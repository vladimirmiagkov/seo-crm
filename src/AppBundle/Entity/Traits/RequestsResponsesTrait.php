<?php
declare(strict_types=1);

namespace AppBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serialization;

trait RequestsResponsesTrait
{
    /**
     * Array of source html "Request".
     *
     * @var null|\string[]
     *
     * @ORM\Column(type="array", nullable=true)
     * @Serialization\Groups({"list"})
     */
    protected $requests;

    /**
     * Array of source html "Response".
     *
     * @var null|\string[]
     *
     * @ORM\Column(type="array", nullable=true)
     * @Serialization\Groups({"list"})
     */
    protected $responses;

    /**
     * @return null|\string
     */
    public function getRequests()
    {
        return $this->requests;
    }

    /**
     * @param null|\string $requests
     * @return $this
     */
    public function setRequests($requests)
    {
        $this->requests = $requests;
        return $this;
    }

    /**
     * @param null|\string $request
     * @return $this
     */
    public function addRequest(string $request)
    {
        $this->requests[] = $request;
        return $this;
    }

    /**
     * @return null|\string
     */
    public function getResponses()
    {
        return $this->responses;
    }

    /**
     * @param null|\string $responses
     * @return $this
     */
    public function setResponses($responses)
    {
        $this->responses = $responses;
        return $this;
    }

    /**
     * @param null|\string $response
     * @return $this
     */
    public function addResponse(string $response)
    {
        $this->responses[] = $response;
        return $this;
    }
}