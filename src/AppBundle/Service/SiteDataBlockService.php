<?php
declare(strict_types=1);

namespace AppBundle\Service;

use AppBundle\Entity\Site;
use AppBundle\Entity\User;
use AppBundle\Entity\Page;
use AppBundle\Entity\Keyword;
use AppBundle\Repository\KeywordPositionRepository;
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
        EntityManager $em
    )
    {
        $this->validator = $validator;
        $this->em = $em;

        $this->keywordPositionRepository = $em->getRepository('AppBundle:KeywordPosition');
    }

    /**
     * Build keywords <-> pages tree, with all data.
     * TODO: Move this method to repository?
     *
     * @param Site     $site     Goal site
     * @param null|int $limit    Pager limit
     * @param null|int $offset   Pager offset
     * @param string   $dateFrom Limits for data (bigger)
     * @param string   $dateTo   Limits for data (smaller)
     * @param string   $filter   DataBlock filter
     * @return array|null
     * @throws \Exception
     */
    public function getDataBlock(
        Site $site,
        $limit = null,
        $offset = null,
        string $dateFrom = '',
        string $dateTo = '',
        $filter = null)
    {
        $result = null;
        $paginator = null;
        $generatedRangeOfDates = null;

        /** @var $dateFrom \DateTime */
        /** @var $dateTo \DateTime */
        list($dateFrom, $dateTo) = self::evaluateDateRange($dateFrom, $dateTo);

        $qb = $this->em->createQueryBuilder()
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        switch ($site->getSeoStrategyKeywordPage()) {
            case Site::SEO_STRATEGY_KEYWORDS_LINKED_TO_PAGES:
                // Get pages with pager ------------------------------------------------------------
                $alias = 'page';
                $qb->addSelect($alias)
                    ->from('AppBundle\Entity\Page', $alias)
                    ->andWhere($alias . '.deleted = false')
                    ->andWhere($alias . '.site = :site')
                    ->setParameter('site', $site->getId());
                $qb = self::addFilterToQb($qb, $alias, $filter);//TODO: move to own module
                $query = $qb->getQuery(); //$a = $query->getSQL();
                $query->setHint(Query::HINT_INCLUDE_META_COLUMNS, true);
                $paginator = new Paginator($query, true); //$a = $query->getSQL();
                if (!$pages = $query->getArrayResult()) {
                    break;
                }
                // HACK: If 'searchEngine' empty - add empty array(making it easier for frontend iteration)
                foreach ($pages as $pageKey => $page) {
                    if (empty($pages['searchEngine'])) {
                        $pages[$pageKey]['searchEngine'][0] = [];
                    }
                }

                if (!$pagesIds = self::getAllIds($pages)) {
                    break;
                }

                // Get linked keywords from many-to-many by pagesIds ------------------------------
                $alias = 'keyword';
                $keywordsQb = $this->em->createQueryBuilder()
                    ->addSelect($alias)
                    ->from('AppBundle\Entity\Keyword', $alias)
                    ->andWhere($alias . '.deleted = false')
                    //   Many to many join: keywords to pages
                    ->addSelect('p.id AS pid')// Add page id to keyword
                    ->innerJoin($alias . '.pages', 'p')
                    ->andWhere($qb->expr()->in('p.id', $pagesIds))
                    //   Many to many join: SearchEngine
                    ->addSelect('se')// Add se
                    ->leftJoin($alias . '.searchEngine', 'se', Join::WITH, $qb->expr()->eq('se.active', true));
                $keywordsQb = self::addFilterToQb($keywordsQb, $alias, $filter);//TODO: move to own module
                $keywordsQuery = $keywordsQb->getQuery(); //$a = $keywordsQuery->getSQL();
                $keywordsQuery->setHint(Query::HINT_INCLUDE_META_COLUMNS, true);
                $keywords = $keywordsQuery->getArrayResult();
                // HACK: If 'searchEngine' empty - add empty array(making it easier for frontend iteration)
                foreach ($keywords as $keywordKey => $keyword) {
                    if (empty($keyword[0]['searchEngine'])) {
                        $keywords[$keywordKey][0]['searchEngine'][0] = [];
                    }
                }

                // Merge keywords to pages, flat tree ----------------------------------------------
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

        // Generate dataBlock dates cells in table header
        $generatedRangeOfDates = self::generateRangeOfDates($dateFrom, $dateTo);

        if (null !== $result) {
            $this->addKeywordsPositions($result, $generatedRangeOfDates, $dateFrom, $dateTo);
        }

        return [
            'totalRecords'               => count($paginator),
            'siteSeoStrategyKeywordPage' => $site->getSeoStrategyKeywordPage(),
            'result'                     => $result,
            'header'                     => $generatedRangeOfDates,
        ];
    }

    /**
     * Add keywords positions to result array.
     *
     * @param           $data
     * @param           $generatedRangeOfDates
     * @param \DateTime $dateFrom (bigger)
     * @param \DateTime $dateTo   (smaller)
     */
    protected function addKeywordsPositions(&$data, $generatedRangeOfDates, \DateTime $dateFrom, \DateTime $dateTo)
    {
        $monitoredKeywordsIds = self::getMonitoredKeywordsIds($data);
        $keywordsPositions = $this->keywordPositionRepository->findRangeByKeywordsIds($monitoredKeywordsIds, $dateFrom, $dateTo);
        foreach ($data as &$item) {
            if ($item[self::ENTITY_TYPE_IDENTIFIER] === Keyword::ENTITY_TYPE && isset($item['searchEngine'][0]['id'])) {
                foreach ($item['searchEngine'] as &$searchEngine) {
                    // Add generatedRangeOfDates to searchEngine, even if NO keywordsPositions found!
                    $searchEngine['_cell'] = $generatedRangeOfDates;

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
     * Generate dataBlock dates cells in table header.
     *
     * @param \DateTime $dateFrom (bigger)
     * @param \DateTime $dateTo   (smaller)
     * @return array
     */
    protected static function generateRangeOfDates(\DateTime $dateFrom, \DateTime $dateTo)
    {
        $result = [];

        $rangeFrom = (clone $dateFrom)->modify('+1 day')->setTime(0, 0, 0);
        $rangeTo = (clone $dateTo)->setTime(0, 0, 0);

        $intervalDays = $rangeFrom->diff($rangeTo)->days;
        for ($i = 1; $i <= $intervalDays; $i++) {
            $date = (clone $rangeFrom)->modify('-' . $i . ' day');
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
    protected static function getAllIds($item)
    {
        return array_map(function ($item) {
            return $item['id'];
        }, $item);
    }

    /**
     * @param $items
     * @return array|null
     */
    protected static function getMonitoredKeywordsIds($items)
    {
        $result = null;
        foreach ($items as $item) {
            if ($item[self::ENTITY_TYPE_IDENTIFIER] === Keyword::ENTITY_TYPE && !empty($item['searchEngine'])) {
                $result[] = $item['id'];
            }
        }
        if (null !== $result) {
            $result = \array_unique($result);
        }
        return $result;
    }

    /**
     * Set default time range, if needed.
     * Also convert unix timestamp to datetime, if needed.
     *
     * @param string $dateFrom (bigger)
     * @param string $dateTo   (smaller)
     * @return array
     */
    protected static function evaluateDateRange(string $dateFrom, string $dateTo)
    {
        if (empty($dateFrom)) {
            $dateFrom = (new \DateTime('now'));
        } elseif (is_numeric($dateFrom)) { // timestamp
            $dateFrom = (new \DateTime())->setTimestamp((int)$dateFrom);
        } else {
            throw new \InvalidArgumentException('Unsupported datetime format.');
        }
        $dateFrom->setTime(23, 59, 59); // Expand time limit: start from today night

        if (empty($dateTo)) {
            $dateTo = (new \DateTime('now -1 month'));
        } elseif (is_numeric($dateTo)) { // timestamp
            $dateTo = (new \DateTime())->setTimestamp((int)$dateTo);
        } else {
            throw new \InvalidArgumentException('Unsupported datetime format.');
        }
        $dateTo->setTime(0, 0, 0);

        return [$dateFrom, $dateTo];
    }

    /**
     * TODO: move to own module
     *
     * @param $name
     * @param $param
     * @param $filter
     * @return null|string
     */
    protected static function getFilterParam($name, $param, $filter)
    {
        $result = null;
        $filterKey = \array_search($name, \array_column($filter['filters'], 'name'));
        if (false !== $filterKey) {
            $result = $filter['filters'][$filterKey][$param];
            if ($param == 'sortDirection') {
                $result = \strtoupper($result);
                if (!empty($result)) {
                    $result = $result == 'ASC' ? $result : 'DESC';
                }
            }
        }
        return $result;
    }

    /**
     * TODO: move to own module
     *
     * @param QueryBuilder $qb
     * @param string       $entity
     * @param              $filter
     * @return QueryBuilder
     */
    protected static function addFilterToQb(QueryBuilder $qb, string $entity, $filter)
    {
        // Add filtering
        if (!empty($filterBy = self::getFilterParam($entity . 'Name', 'values', $filter))) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like($entity . '.name', ':' . $entity . 'name')
            ));
            $qb->setParameter($entity . 'name', '%' . $filterBy . '%');
        }
        // Add sorting
        if (!empty($sortBy = self::getFilterParam($entity . 'Name', 'sortDirection', $filter))) {
            $qb->addOrderBy($entity . '.' . 'name', $sortBy);
        }

        return $qb;
    }
}