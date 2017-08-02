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
use Doctrine\ORM\EntityManager;

class KeywordPositionService
{
    /**
     * @var EntityManager
     */
    protected $em;
    /**
     * @var KeywordRepository
     */
    protected $keywordRepository;
    /**
     * @var KeywordService
     */
    protected $keywordService;
    /**
     * @var KeywordPositionRepository
     */
    protected $keywordPositionRepository;
    /**
     * @var KeywordPositionLogService
     */
    protected $keywordPositionLogService;
    /**
     * @var KeywordCompetitorService
     */
    private $keywordCompetitorService;
    /**
     * @var YandexXml
     */
    protected $searchEngineYandexXml;

    public function __construct(
        EntityManager $em,
        KeywordService $keywordService,
        KeywordPositionLogService $keywordPositionLogService,
        KeywordCompetitorService $keywordCompetitorService,
        YandexXml $searchEngineYandexXml
    )
    {
        $this->em = $em;
        $this->keywordService = $keywordService;
        $this->keywordPositionLogService = $keywordPositionLogService;
        $this->keywordCompetitorService = $keywordCompetitorService;
        $this->searchEngineYandexXml = $searchEngineYandexXml;

        $this->keywordRepository = $em->getRepository('AppBundle:Keyword');
        $this->keywordPositionRepository = $em->getRepository('AppBundle:KeywordPosition');
    }

    /**
     * We check one keyword in ALL linked (to this keyword) search engines.
     * Cron task.
     *
     * @param bool $updateKeywordPositionLastCheck
     * @return array|null array = Debug info
     */
    public function grabKeywordPositionFromSearchEngines($updateKeywordPositionLastCheck = true)
    {
        $keyword = $this->keywordRepository->findKeywordForPositionCheck();
        if (!$keyword) {
            // No keyword to check - exit.
            return null;
        }

        $searchEngineDebugOutput = [];

        // For each search engine: check keyword position.
        /** @var SearchEngine $searchEngine */
        foreach ($keyword->getSearchEngines() as $searchEngine) {
            if (!$this->doWeNeedToCheckKeywordPositionInSearchEngine($keyword->getPositionLastCheck(), $searchEngine->getCheckKeywordPositionPeriodicity())) {
                continue;
            }

            // We lock keyword for each search engine.
            if ($updateKeywordPositionLastCheck) {
                $this->keywordService->lock($keyword);
            }

            $serpResult = null;
            $keywordPositionLog = $this->keywordPositionLogService->createNewLogger();

            // Request data from search engine.
            switch ($searchEngine->getType()) {
                case SearchEngine::YANDEX_TYPE:
                    // Get last position for keyword, if exists. This is optimization by requests count to search engine.
                    $startFromPage = 0;
                    $keywordLastCheckPosition = $this->keywordPositionRepository->findLastCheckPosition($keyword, $searchEngine);
                    if ($keywordLastCheckPosition) {
                        $startFromPage = (int)\floor($keywordLastCheckPosition->getPosition() / $searchEngine->getCheckKeywordPositionRequestSitesPerPage());
                        if ($startFromPage < 1) { // If position was "-1" // TODO: is this possible?
                            $startFromPage = 0;
                        }
                        // If, for example, the previous position(exist) was 393, and the new limit = 300, then we start search from 0 page
                        if ($startFromPage >= (int)\floor($keyword->getSearchEngineRequestLimit() / $searchEngine->getCheckKeywordPositionRequestSitesPerPage())) {
                            $startFromPage = 0;
                        }
                    }

                    $serpResult = $this->searchEngineYandexXml->grabSerp(
                        $keyword->getName(),
                        $keyword->getSite()->getNamePuny(),
                        (string)$keyword->getFromPlace(),
                        $keyword->getSearchEngineRequestLimit(),
                        $searchEngine->getCheckKeywordPositionRequestSitesPerPage(),
                        $searchEngine->getCheckKeywordPositionTimeoutBetweenRequests(),
                        (int)$startFromPage
                    );

                    break;

                case SearchEngine::GOOGLE_TYPE:
                    // TODO: Parsing GOOGLE not implemented yet.
                    break;

                default:
            }

            // Processing search engine results.
            if (null !== $serpResult) {
                $keywordPositionLog->setErrors($serpResult->getErrors());
                $keywordPositionLog->setStatus($serpResult->getStatus());
                $keywordPositionLog->setRequests($serpResult->getRequests());
                $keywordPositionLog->setResponses($serpResult->getResponses());

                if ($serpResult->didWeFoundSomeSitesInSerp()) {
                    if ($serpResult->findGoalSite()) {
                        // Save keyword position.
                        $keywordPosition = $this->saveNewKeywordPosition(
                            $keyword,
                            $searchEngine,
                            ($serpResult->getGoalSiteIndex() + 1),
                            $serpResult->findGoalSite()->getUrl()
                        );
                        $keywordPositionLog->setKeywordPosition($keywordPosition);
                    }

                    // Evaluate data for competitors.
                    foreach ($serpResult->getSites() as $competitorIndex => $competitor) {
                        $competitor->setKeyword($keyword);
                        $competitor->setSearchEngine($searchEngine);
                        $serpResult->setSiteByIndex($competitorIndex, $competitor);
                    }
                    // Save competitors.
                    $this->keywordCompetitorService->saveNewCompetitors($serpResult->getSites());
                }

                // Save keyword position log.
                $this->keywordPositionLogService->save($keywordPositionLog);
            }

            $searchEngineDebugOutput[$searchEngine->getName()] = [
                //'serpResult' => $serpResult ?? null,
                'keywordLastCheckPosition' => $keywordLastCheckPosition ?? null,
                'keywordPosition'          => $keywordPosition ?? null,
                'error'                    => $keywordPositionLog->getErrors(),
            ];
        }

        if ($updateKeywordPositionLastCheck) {
            $this->keywordService->updatePositionLastCheck($keyword);
        }

        return [
            'keyword'      => $keyword,
            'searchEngine' => $searchEngineDebugOutput,
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
     * @param Keyword      $keyword
     * @param SearchEngine $searchEngine
     * @param int          $position
     * @param string       $url
     * @return KeywordPosition
     */
    public function saveNewKeywordPosition(
        Keyword $keyword,
        SearchEngine $searchEngine,
        int $position,
        string $url
    ): KeywordPosition
    {
        $keywordPosition = new KeywordPosition();
        $keywordPosition
            ->setKeyword($keyword)
            ->setSearchEngine($searchEngine)
            ->setPosition($position)
            ->setUrl($url);
        $this->em->persist($keywordPosition);
        $this->em->flush();

        return $keywordPosition;
    }
}