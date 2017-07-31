<?php
declare(strict_types=1);

namespace AppBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serialization;
use Symfony\Component\Validator\Constraints as Assert;

trait ActiveTrait
{
    /**
     * Is element active?
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=false)
     * @Serialization\Groups({"list"})
     */
    protected $active = true;

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param $active
     * @return $this
     */
    public function setActive($active)
    {
        $this->active = filter_var($active, FILTER_VALIDATE_BOOLEAN); // convert to
        return $this;
    }
}