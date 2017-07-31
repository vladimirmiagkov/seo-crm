<?php
declare(strict_types=1);

namespace AppBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serialization;
use Symfony\Component\Validator\Constraints as Assert;

trait NameTrait
{
    /**
     * Name for element
     *
     * @var string
     *
     * @ORM\Column(type="string", length=4096, nullable=false)
     * @Serialization\Groups({"list"})
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = (string)$name; // convert to
        return $this;
    }
}