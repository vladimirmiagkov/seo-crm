<?php
declare(strict_types=1);

namespace AppBundle\Service;

use AppBundle\Entity\Keyword;
use AppBundle\Entity\KeywordPosition;
use AppBundle\Entity\KeywordPositionLog;
use AppBundle\Entity\SearchEngine;
use AppBundle\Repository\KeywordPositionRepository;
use AppBundle\Repository\KeywordRepository;
use AppBundle\SearchEngine\KeywordPosition\YandexXml;
use AppBundle\SearchEngine\SerpResult;
use Doctrine\ORM\EntityManager;

class KeywordPositionService
{
    /**
     * @var EntityManager
     */
    private $em;
    /**
     * @var KeywordRepository
     */
    private $keywordRepository;
    /**
     * @var KeywordService
     */
    private $keywordService;
    /**
     * @var KeywordPositionRepository
     */
    private $keywordPositionRepository;
    /**
     * @var KeywordPositionLogService
     */
    private $keywordPositionLogService;
    /**
     * @var KeywordCompetitorService
     */
    private $keywordCompetitorService;
    /**
     * @var YandexXml
     */
    private $searchEngineYandexXml;

    public function __construct(
        EntityManager $em,
        KeywordService $keywordService,
        KeywordRepository $keywordRepository,
        KeywordPositionRepository $keywordPositionRepository,
        KeywordPositionLogService $keywordPositionLogService,
        KeywordCompetitorService $keywordCompetitorService,
        YandexXml $searchEngineYandexXml
    )
    {
        $this->em = $em;
        $this->keywordService = $keywordService;
        $this->keywordRepository = $keywordRepository;
        $this->keywordPositionRepository = $keywordPositionRepository;
        $this->keywordPositionLogService = $keywordPositionLogService;
        $this->keywordCompetitorService = $keywordCompetitorService;
        $this->searchEngineYandexXml = $searchEngineYandexXml;
    }

    /**
     * We check one keyword in ALL linked (to this keyword) search engines.
     *
     * @param bool $debugUpdateKeywordPositionLastCheck
     * @return array|null
     */
    public function grabKeywordPositionFromSearchEngines($debugUpdateKeywordPositionLastCheck = true)
    {
        $result = [];

        $keyword = $this->keywordRepository->findKeywordForPositionCheck();
        if (!$keyword) { // No keyword to check - exit.
            return null;
        }

        // For each search engine: check keyword position.
        /** @var SearchEngine $searchEngine */
        foreach ($keyword->getSearchEngines() as $searchEngine) {
            if (!$this->doWeNeedToCheckKeywordPositionInSearchEngine($keyword->getPositionLastCheck(), $searchEngine->getCheckKeywordPositionPeriodicity())) {
                continue;
            }

            if ($debugUpdateKeywordPositionLastCheck) {
                $this->keywordService->lock($keyword); // We "lock" keyword for each search engine.
            }

            $serpResult = null;
            switch ($searchEngine->getType()) {
                case SearchEngine::YANDEX_TYPE:
                    $serpResult = $this->searchEngineYandexXml->grabSerp(
                        $keyword,
                        $searchEngine,
                        $this->getStartRequestingFromPage($keyword, $searchEngine)
                    );
                    break;
                case SearchEngine::GOOGLE_TYPE:
                    // TODO: Parsing GOOGLE not implemented yet.
                    break;
                default:
            }

            null !== $serpResult ? $result[] = $serpResult : null;
        }

        if ($debugUpdateKeywordPositionLastCheck) {
            $this->keywordService->updatePositionLastCheck($keyword);
        }

        return $result;
    }

    /**
     * Save search engines results (SERPs) to db.
     * TODO: move to own module ? ...
     *
     * @param SerpResult[] $serps
     * @return array|null
     */
    public function saveSerpsToDb(array $serps)
    {
        if (empty($serps)) {
            return null;
        }

        //$debugOutput = [];
        foreach ($serps as $serpResult) {
            // Save keyword position.
            $keywordPosition = null;
            if ($serpResult->getKeywordPosition()) {
                $keywordPosition = $this->saveKeywordPositionToDb($serpResult->getKeywordPosition());
            }

            // Save competitors.
            if ($serpResult->didWeFoundSomeSitesInSerp()) {
                $this->keywordCompetitorService->saveCompetitorsToDb($serpResult->getSites());
            }

            // Save logging results.
            $keywordPositionLog = $this->keywordPositionLogService->addNewKeywordPositionLogToDb(
                $serpResult->getRequests(),
                $serpResult->getResponses(),
                $serpResult->getErrors(),
                $serpResult->getStatus(),
                $keywordPosition
            );

            //$debugOutput[$searchEngine->getName()] = [
            //    //'keywordLastCheckPosition' => $this->keywordPositionRepository->findLastCheckPosition($keyword, $searchEngine),
            //    'keywordPosition'          => $keywordPositionLog->getKeywordPosition(),
            //    'error'                    => $keywordPositionLog->getErrors(),
            //];
        }

        return [
            //'keyword'      => $keyword,
            //'searchEngine' => $debugOutput,
        ];
    }

    /**
     * @param null|\DateTime $lastCheck   Last check was at datetime|null ...
     * @param int            $periodicity Keyword position checking periodicity
     * @param string         $now         DataTime point
     * @return bool
     */
    public static function doWeNeedToCheckKeywordPositionInSearchEngine($lastCheck, int $periodicity, string $now = 'now'): bool
    {
        if (null === $lastCheck) {
            // Last position NOT exists - we need to check keyword position.
            return true;
        }

        $currentDateTime = new \DateTime($now);
        $keywordLastCheck = $lastCheck;
        $checkKeywordAfter = (new \DateTime())->setTimestamp($keywordLastCheck->getTimestamp() + $periodicity);

        if ($checkKeywordAfter > $currentDateTime) {
            return false;
        }

        return true;
    }

    /**
     * Get page number, for search engine, from which we start requesting.
     * This is optimization by requests count to search engine.
     *
     * @param Keyword      $keyword
     * @param SearchEngine $searchEngine
     * @return int
     */
    public function getStartRequestingFromPage(Keyword $keyword, SearchEngine $searchEngine): int
    {
        $startFromPage = 0;
        $keywordLastCheckPosition = $this->keywordPositionRepository->findLastCheckPosition($keyword, $searchEngine);
        if ($keywordLastCheckPosition) {
            $startFromPage = (int)\floor(
                $keywordLastCheckPosition->getPosition() / $searchEngine->getCheckKeywordPositionRequestSitesPerPage()
            );
            if ($startFromPage < 1) { // If position was "-1" // TODO: is this possible?
                $startFromPage = 0;
            }
            // If, for example, the previous position(exist) was 393, and the new limit = 300, then we start search from 0 page
            if ($startFromPage >= (int)\floor($keyword->getSearchEngineRequestLimit() / $searchEngine->getCheckKeywordPositionRequestSitesPerPage())) {
                $startFromPage = 0;
            }
        }
        return $startFromPage;
    }

    /**
     * @param KeywordPosition $keywordPosition
     * @return KeywordPosition
     */
    public function saveKeywordPositionToDb(KeywordPosition $keywordPosition): KeywordPosition
    {
        $this->em->persist($keywordPosition);
        $this->em->flush();
        return $keywordPosition;
    }
}