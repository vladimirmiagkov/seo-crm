<?php
declare(strict_types=1);

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class SiteScheduleRepository extends EntityRepository
{
    const ALIAS = 'ss';
}