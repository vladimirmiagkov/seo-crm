<?php
declare(strict_types=1);

namespace AppBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serialization;

trait SortableTrait
{
    /**
     * Sort order
     *
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false)
     * @Serialization\Groups({"list"})
     */
    protected $sort = 0;

    /**
     * @return int
     */
    public function getSort(): int
    {
        return $this->sort;
    }

    /**
     * @param int $sort
     * @return $this
     */
    public function setSort(int $sort)
    {
        $this->sort = $sort;
        return $this;
    }
}