<?php
declare(strict_types=1);

namespace AppBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serialization;
use Symfony\Component\Validator\Constraints as Assert;

trait IdTrait
{
    ///**
    // * @var string
    // *
    // * @ORM\Id
    // * @ORM\Column(type="guid", nullable=false)
    // * @ORM\GeneratedValue(strategy="UUID")
    // * @Serialization\Groups({"list"})
    // */
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serialization\Groups({"list"})
     */
    protected $id;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
}