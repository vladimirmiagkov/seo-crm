<?php
declare(strict_types=1);

namespace AppBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serialization;
use Symfony\Component\Validator\Constraints as Assert;

trait DeletedTrait
{
    /**
     * Is element marked as "deleted"
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=false)
     * @Serialization\Groups({"list"})
     */
    protected $deleted = false;

    /**
     * @return bool
     */
    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    /**
     * @param $deleted
     * @return $this
     */
    public function setDeleted($deleted)
    {
        $this->deleted = filter_var($deleted, FILTER_VALIDATE_BOOLEAN); // convert to
        return $this;
    }
}