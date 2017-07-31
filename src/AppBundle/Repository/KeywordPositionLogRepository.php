<?php
declare(strict_types=1);

namespace AppBundle\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Parameter;

class KeywordPositionLogRepository extends EntityRepository
{
    const ALIAS = 'kpl';
}