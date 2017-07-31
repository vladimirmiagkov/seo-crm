<?php
declare(strict_types=1);

namespace AppBundle\Repository;

use AppBundle\Entity\SearchEngine;
use Doctrine\ORM\EntityRepository;

class SearchEngineRepository extends EntityRepository
{
    const ALIAS = 'se';

    ///**
    // * @param int $searchEngineType
    // * @return SearchEngine
    // * @throws \Exception
    // */
    //public function getByType(int $searchEngineType)
    //{
    //    /** @var SearchEngine $result */
    //    $result = $this->findOneBy(['type' => $searchEngineType]);
    //    if (!$result) {
    //        throw new \Exception('Cant find search engine by type="' . $searchEngineType . '"');
    //    }
    //
    //    return $result;
    //}
}