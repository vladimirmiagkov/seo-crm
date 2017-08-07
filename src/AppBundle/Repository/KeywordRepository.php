<?php
declare(strict_types=1);

namespace AppBundle\Repository;

use AppBundle\Entity\Keyword;
use AppBundle\Entity\SearchEngine;
use Doctrine\ORM\EntityRepository;

class KeywordRepository extends EntityRepository
{
    const ALIAS = 'keyword';

    /**
     * Find keyword for which the position will be checked.
     *
     * @return null|Keyword
     */
    public function findKeywordForPositionCheck()
    {
        $qb = $this->createQueryBuilder(self::ALIAS)
            ->setMaxResults(1)
            ->andWhere(self::ALIAS . '.deleted = false')
            ->andWhere(self::ALIAS . '.active = true');

        // There are linked search engines
        $qb->andWhere($qb->expr()->gt('size(' . self::ALIAS . '.searchEngines)', 0));

        // Since last LOCK passed more than n seconds
        $lockTime = (new \DateTime())->modify('-' . (int)SearchEngine::CHECK_KEYWORD_POSITION_LOCK_TIMEOUT . ' seconds')->format('Y-m-d H:i:s');
        $qb->andWhere(
            $qb->expr()->orX(
                $qb->expr()->isNull(self::ALIAS . '.positionLockedAt'),
                $qb->expr()->lt(self::ALIAS . '.positionLockedAt', ':lockTime')
            )
        );
        $qb->setParameter('lockTime', $lockTime);

        //
        $positionLastCheck = (new \DateTime())->setTime(0, 0, 0)->format('Y-m-d H:i:s');
        $qb->andWhere(
            $qb->expr()->orX(
                $qb->expr()->isNull(self::ALIAS . '.positionLastCheck'),
                $qb->expr()->lt(self::ALIAS . '.positionLastCheck', ':positionLastCheck')
            )
        );
        $qb->setParameter('positionLastCheck', $positionLastCheck);

        // Take the 'oldest' keyword
        $qb->addOrderBy(self::ALIAS . '.' . 'positionLastCheck', 'ASC');

        $query = $qb->getQuery();
        //$result = $query->getResult();
        $result = $query->getOneOrNullResult();

        return $result;
    }

    ///**
    // * @param int $keywordId
    // * @return Keyword
    // * @throws \Exception
    // */
    //public function getById(int $keywordId)
    //{
    //    /** @var Keyword $result */
    //    $result = $this->find($keywordId);
    //    if (!$result) {
    //        throw new \Exception('Cant find keyword by id="' . $keywordId . '"');
    //    }
    //
    //    return $result;
    //}
}