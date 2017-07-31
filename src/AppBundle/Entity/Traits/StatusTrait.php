<?php
declare(strict_types=1);

namespace AppBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serialization;

trait StatusTrait
{
    /**
     * Some status...
     *
     * @var \int
     *
     * @ORM\Column(type="integer", nullable=false)
     * @Serialization\Groups({"list"})
     */
    protected $status;

    /**
     * @return \int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param \int $status
     * @return $this
     */
    public function setStatus(int $status)
    {
        $this->status = $status;
        return $this;
    }
}