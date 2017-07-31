<?php
declare(strict_types=1);

namespace AppBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serialization;

trait ErrorsTrait
{
    /**
     * Array of human readable "errors".
     *
     * @var null|\string[]
     *
     * @ORM\Column(type="array", nullable=true)
     * @Serialization\Groups({"list"})
     */
    protected $errors;

    /**
     * @return null|\string[]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param null|\string[] $errors
     * @return $this
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;
        return $this;
    }

    /**
     * @param null|\string $error
     * @return $this
     */
    public function addError(string $error)
    {
        $this->errors[] = $error;
        return $this;
    }
}