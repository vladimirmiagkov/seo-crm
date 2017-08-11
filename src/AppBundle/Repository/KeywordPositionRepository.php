<?php
declare(strict_types=1);

namespace AppBundle\Repository;

use AppBundle\Entity\Keyword;
use AppBundle\Entity\KeywordPosition;
use AppBundle\Entity\SearchEngine;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Parameter;

class KeywordPositionRepository extends EntityRepository
{
    const ALIAS = 'kp';

    /**
     * Find keywords positions by time range.
     * WARNING: Optimization by speed and low memory consumption!
     *
     * @param array          $keywordsIds
     * @param \DateTime|null $dateFrom
     * @param \DateTime|null $dateTo
     * @return null
     */
    public function findRangeByKeywordsIds(array $keywordsIds, \DateTime $dateFrom = null, \DateTime $dateTo = null)
    {
        $result = null;

        //$qb = $this->createQueryBuilder(self::ALIAS);
        //$qb->andWhere($qb->expr()->in(self::ALIAS . '.keyword', $keywordsIds));
        //$qb->andWhere($qb->expr()->between(self::ALIAS . '.createdAt', ':from', ':to'))
        //    ->setParameters(new ArrayCollection([
        //        new Parameter('from', $dateTo),
        //        new Parameter('to', $dateFrom)
        //    ]));
        //
        //$query = $qb->getQuery();
        ////$result = $query->getResult();
        //$query->setHint(Query::HINT_INCLUDE_META_COLUMNS, true);
        //$result = $query->getArrayResult();

        $conn = $this->getEntityManager()->getConnection();
        $sql = '
            SELECT
              kp.keyword_id,
              kp.search_engine_id,
              kp.position,
              kp.url,
              kp.created_at,
              DATE_FORMAT(kp.created_at, \'%Y-%m-%d\') as fulldate
            FROM keyword_position kp
            WHERE
              kp.created_at BETWEEN :to AND :from
              AND kp.keyword_id IN (' . implode(',', array_map('intval', $keywordsIds)) . ')
            ORDER BY kp.created_at DESC
        ';
        $stmt = $conn->prepare($sql);
        $stmt->bindValue('to', $dateTo->format('Y-m-d H:i:s'));
        $stmt->bindValue('from', $dateFrom->format('Y-m-d H:i:s'));
        $stmt->execute();
        $fetch = $stmt->fetchAll();

        // Reformat result array to:
        // [keywordId]
        //      [searchEngineId]
        //          [fulldate]  // - target day
        //              []data...
        foreach ($fetch as $keywordPosition) {
            $result[$keywordPosition['keyword_id']][$keywordPosition['search_engine_id']][$keywordPosition['fulldate']][] = $keywordPosition;
        }

        return $result;
    }

    /**
     * Get last position for keyword, if exists.
     *
     * @param Keyword      $keyword
     * @param SearchEngine $searchEngine
     * @return null|KeywordPosition
     */
    public function findLastCheckPosition(Keyword $keyword, SearchEngine $searchEngine)
    {
        /** @var KeywordPosition $result */
        $result = $this->findOneBy([
            'keyword'      => $keyword,
            'searchEngine' => $searchEngine,
        ], ['createdAt' => 'DESC']);

        return $result;
    }
}