<?php
declare(strict_types=1);

namespace AppBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serialization;

trait ModifiedAtTrait
{
    /**
     * Modified at datetime
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=false)
     * @Serialization\Groups({"list"})
     */
    protected $modifiedAt;

    /**
     * @return \DateTime
     */
    public function getModifiedAt()
    {
        return $this->modifiedAt;
    }

    /**
     * @param \DateTime $modifiedAt
     * @return $this
     */
    public function setModifiedAt(\DateTime $modifiedAt)
    {
        $this->modifiedAt = $modifiedAt;
        return $this;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreFlush
     */
    public function setModifiedAtChange()
    {
        $this->setModifiedAt(new \DateTime('now'));
    }
}