<?php
declare(strict_types=1);

namespace AppBundle\Service;

use AppBundle\Entity\Site;
use AppBundle\Entity\User;
use AppBundle\Entity\Page;
use AppBundle\Entity\Keyword;
use AppBundle\Helper\Filter\SiteDataBlockFilter;
use AppBundle\Repository\KeywordPositionRepository;
use AppBundle\Helper\DateTimeRange;
use AppBundle\Helper\Pager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\Query;

class SiteDataBlockService
{
    /**
     * Identifier key for entity. Like: is this "keyword"? or "page"?
     * For frontend.
     */
    const ENTITY_TYPE_IDENTIFIER = '_entityType';

    /**
     * @var ValidatorInterface
     */
    protected $validator;
    /**
     * @var EntityManager
     */
    protected $em;
    /**
     * @var KeywordPositionRepository
     */
    protected $keywordPositionRepository;


    public function __construct(
        ValidatorInterface $validator,
        EntityManager $em,
        KeywordPositionRepository $keywordPositionRepository
    )
    {
        $this->validator = $validator;
        $this->em = $em;
        $this->keywordPositionRepository = $keywordPositionRepository;
    }

    /**
     * Building process: keywords <-> pages tree, with all additional data.
     * We build specific data structure, just because for good frontend iteration through it.
     *
     * @param Site                $site   Goal site
     * @param Pager               $pager
     * @param DateTimeRange       $dateTimeRange
     * @param SiteDataBlockFilter $filter DataBlock filter
     * @return array|null
     * @throws \Exception
     */
    public function getDataBlock(
        Site $site,
        Pager $pager,
        DateTimeRange $dateTimeRange,
        SiteDataBlockFilter $filter
    )
    {
        $result = null;
        $paginator = null;
        $generatedHeaderCells = null;

        $qb = $this->em->createQueryBuilder()
            ->setMaxResults($pager->getLimit())
            ->setFirstResult($pager->getOffset());

        switch ($site->getSeoStrategyKeywordPage()) {
            case Site::SEO_STRATEGY_KEYWORDS_LINKED_TO_PAGES:
                // Get pages with pager ------------------------------------------------------------
                $qb->addSelect('page')
                    ->from('AppBundle\Entity\Page', 'page')
                    ->andWhere('page' . '.deleted = false')
                    ->andWhere('page' . '.site = :site')
                    ->setParameter('site', $site->getId())
                    // External table
                    ->addSelect('searchEngine')
                    ->leftJoin('page' . '.searchEngines', 'searchEngine', Join::WITH, $qb->expr()->eq('searchEngine.active', true));
                $qb = $filter->applyFilterToQueryBuilder($qb, 'page');

                $query = $qb->getQuery();
                $query->setHint(Query::HINT_INCLUDE_META_COLUMNS, true);
                $paginator = new Paginator($query, true);
                if (!$pages = $query->getArrayResult()) {
                    break;
                }
                // HACK: If 'searchEngines' empty - add empty array (making it easier for frontend iteration).
                foreach ($pages as $pageKey => $page) {
                    if (empty($pages['searchEngines'])) {
                        $pages[$pageKey]['searchEngines'][0] = [];
                    }
                }

                if (!$pagesIds = self::getAllIdsFromItem($pages)) {
                    break;
                }

                // Get linked keywords from many-to-many by pagesIds ------------------------------
                $keywordsQb = $this->em->createQueryBuilder()
                    ->addSelect('keyword')
                    ->from('AppBundle\Entity\Keyword', 'keyword')
                    ->andWhere('keyword' . '.deleted = false')
                    // Many to many join: keywords to pages
                    ->addSelect('page.id AS pid')
                    ->innerJoin('keyword' . '.pages', 'page')
                    ->andWhere($qb->expr()->in('page.id', $pagesIds))
                    // External table
                    ->addSelect('searchEngine')
                    ->leftJoin('keyword' . '.searchEngines', 'searchEngine', Join::WITH, $qb->expr()->eq('searchEngine.active', true));
                $keywordsQb = $filter->applyFilterToQueryBuilder($keywordsQb, 'keyword');

                $keywordsQuery = $keywordsQb->getQuery();
                $keywordsQuery->setHint(Query::HINT_INCLUDE_META_COLUMNS, true);
                $keywords = $keywordsQuery->getArrayResult();
                // HACK: If 'searchEngines' empty - add empty array (making it easier for frontend iteration).
                foreach ($keywords as $keywordKey => $keyword) {
                    if (empty($keyword[0]['searchEngines'])) {
                        $keywords[$keywordKey][0]['searchEngines'][0] = [];
                    }
                }

                // Recompose keywords to pages, flat tree (making it easier for frontend iteration).
                //    page1
                //    keyword1
                //    keyword2
                //    page2
                //    keyword3
                foreach ($pages as $pageKey => $page) {
                    $page[self::ENTITY_TYPE_IDENTIFIER] = Page::ENTITY_TYPE; // Add identifier for frontend
                    $result[] = $page;
                    foreach ($keywords as $keywordKey => $keyword) {
                        if ($keyword['pid'] === $page['id']) {
                            $keyword[0][self::ENTITY_TYPE_IDENTIFIER] = Keyword::ENTITY_TYPE; // Add identifier for frontend
                            $result[] = $keyword[0];
                            unset($keywords[$keywordKey]);
                        }
                    }
                }

                break;

            case Site::SEO_STRATEGY_PAGES_LINKED_TO_KEYWORD:
                throw new \Exception('SEO_STRATEGY_PAGES_LINKED_TO_KEYWORD Not implemented yet.');
                break;

            default:
                throw new \InvalidArgumentException('Unavailable site seo strategy.');
        }

        $generatedHeaderCells = self::generateHeaderCells($dateTimeRange);

        if (null !== $result) {
            $this->addKeywordsPositionsToData($result, $generatedHeaderCells, $dateTimeRange->getStart(), $dateTimeRange->getEnd());
        }

        return [
            'totalRecords'               => count($paginator),
            'siteSeoStrategyKeywordPage' => $site->getSeoStrategyKeywordPage(),
            'result'                     => $result,
            'header'                     => $generatedHeaderCells,
        ];
    }

    /**
     * Add keywords positions to data array.
     *
     * @param array     $data
     * @param array     $generatedHeaderCells
     * @param \DateTime $dateFrom "higher" like '2018-01-01'
     * @param \DateTime $dateTo   "lower"  like '2017-01-01'
     */
    protected function addKeywordsPositionsToData(&$data, $generatedHeaderCells, \DateTime $dateFrom, \DateTime $dateTo)
    {
        $monitoredKeywordsIds = self::getMonitoredKeywordsIdsFromData($data);
        if (!$monitoredKeywordsIds) {
            return;
        }
        $keywordsPositions = $this->keywordPositionRepository->findRangeByKeywordsIds($monitoredKeywordsIds, $dateFrom, $dateTo);
        foreach ($data as &$item) {
            if (
                $item[self::ENTITY_TYPE_IDENTIFIER] === Keyword::ENTITY_TYPE // It's a "keyword".
                && isset($item['searchEngines'][0]['id'])                    // We have linked "SearchEngine" to this keyword.
            ) {
                foreach ($item['searchEngines'] as &$searchEngine) {
                    // Add generatedHeaderCells to searchEngine, even if NO keywordsPositions found!
                    $searchEngine['_cell'] = $generatedHeaderCells;

                    // Add actual keyword position per day (if keyword position exists for target day)
                    foreach ($searchEngine['_cell'] as &$cell) { // cell - target date
                        if (!empty($keywordsPositions[$item['id']][$searchEngine['id']][$cell['fulldate']][0])) {
                            // Data for Keyword and SearchEngine exists
                            $cell['pos'] = $keywordsPositions[$item['id']][$searchEngine['id']][$cell['fulldate']][0];
                        }
                    }
                }
            }
        }
    }

    /**
     * Generate dataBlock dates cells for frontend table header.
     *
     * @param DateTimeRange $dateTimeRange
     * @return array
     */
    protected static function generateHeaderCells(DateTimeRange $dateTimeRange)
    {
        $result = [];
        $range = (clone($dateTimeRange))
            ->makeRangePositive()
            ->expandRangeToFullDay();

        $generatedSequence = $range->generateSequence('P1D');
        // Make range "reversed": from "higher" to "lower". We need it for frontend table.
        $generatedSequence = \array_reverse($generatedSequence);

        /** @var \DateTime $date */
        foreach ($generatedSequence as $date) {
            $result[] = [
                'shortdate' => $date->format('d'),
                'fulldate'  => $date->format('Y-m-d'),
            ];
        }

        return $result;
    }

    /**
     * @param $item
     * @return array
     */
    protected static function getAllIdsFromItem($item)
    {
        return array_map(function ($item) {
            return $item['id'];
        }, $item);
    }

    /**
     * Get monitored keywords (keyword has linked searchEngines) ids from data array.
     *
     * @param array $data
     * @return array|null
     */
    protected static function getMonitoredKeywordsIdsFromData($data)
    {
        $result = null;
        foreach ($data as $item) {
            if (
                $item[self::ENTITY_TYPE_IDENTIFIER] === Keyword::ENTITY_TYPE // It's a "keyword".
                && !empty($item['searchEngines'])                            // We have linked "SearchEngine" to this keyword.
            ) {
                $result[] = $item['id'];
            }
        }
        if (null !== $result) {
            $result = \array_unique($result);
        }
        return $result;
    }
}