<?php
declare(strict_types=1);

namespace AppBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as Serialization;
use AppBundle\Entity\User;

trait ModifiedByTrait
{
    /**
     * Modified by user
     *
     * @var null|User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true, onDelete="SET NULL")
     * @Serialization\Groups({"list"})
     * @Serialization\MaxDepth(1)
     */
    protected $modifiedBy;

    /**
     * @return null|User
     */
    public function getModifiedBy()
    {
        return $this->modifiedBy;
    }

    /**
     * @param User $modifiedBy
     * @return $this
     */
    public function setModifiedBy(User $modifiedBy)
    {
        $this->modifiedBy = $modifiedBy;
        return $this;
    }
}