<?php
declare(strict_types=1);

namespace AppBundle\Entity;

use AppBundle\Entity\Traits\ActiveTrait;
use AppBundle\Entity\Traits\IdTrait;
use AppBundle\Entity\Traits\NameTrait;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serialization;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Search engine: definition, settings ...
 *
 * @ORM\Table(name="search_engine")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SearchEngineRepository")
 * @ORM\HasLifecycleCallbacks
 */
class SearchEngine
{
    use IdTrait;
    use NameTrait;
    use ActiveTrait;

    /**
     * Hardcoded "type". Do we need this?
     */
    const GOOGLE_TYPE = 0;
    /**
     * Hardcoded "type". Do we need this?
     */
    const YANDEX_TYPE = 1;

    /**
     * Timeout between checking, for one keyword position. Seconds.
     */
    const CHECK_KEYWORD_POSITION_LOCK_TIMEOUT = 86400;

    /**
     * Short name, like "G" (mean "Google")
     * Displayed at frontend.
     *
     * @var string
     *
     * @ORM\Column(type="string", length=4096, nullable=false)
     * @Serialization\Groups({"list"})
     * @Assert\NotBlank()
     */
    protected $shortName;

    /**
     * Type of search engine.
     *
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false)
     * @Serialization\Groups({"list"})
     * @Assert\NotBlank()
     */
    protected $type;

    /**
     * Check keyword position every Periodicity.
     * Seconds.
     *
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false)
     * @Serialization\Groups({"list"})
     * @Assert\Range(
     *     min = 86400,
     *     max = 31536000,
     *     minMessage = "Periodicity must be at min {{ limit }} seconds",
     *     maxMessage = "Periodicity must be at max {{ limit }} seconds"
     * )
     */
    protected $checkKeywordPositionPeriodicity = 86400;

    /**
     * Check keyword position timeout between requests to search engine. (antibruteforce)
     * Seconds.
     *
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false)
     * @Serialization\Groups({"list"})
     * @Assert\Range(
     *     min = 0,
     *     max = 60,
     *     minMessage = "Timeout between requests must be at min {{ limit }} seconds",
     *     maxMessage = "Timeout between requests must be at max {{ limit }} seconds"
     * )
     */
    protected $checkKeywordPositionTimeoutBetweenRequests = 3;

    /**
     * Number of sites per request page.
     *
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false)
     * @Serialization\Groups({"list"})
     * @Assert\Range(
     *     min = 1,
     *     max = 1000,
     *     minMessage = "Request sites per page must be at min {{ limit }}",
     *     maxMessage = "Request sites per page must be at max {{ limit }}"
     * )
     */
    protected $checkKeywordPositionRequestSitesPerPage = 100;


    public function __construct()
    {
    }


    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     * @return $this
     */
    public function setType(int $type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getShortName(): string
    {
        return $this->shortName;
    }

    /**
     * @param string $shortName
     * @return $this
     */
    public function setShortName(string $shortName)
    {
        $this->shortName = $shortName;
        return $this;
    }

    /**
     * @return int
     */
    public function getCheckKeywordPositionPeriodicity(): int
    {
        return $this->checkKeywordPositionPeriodicity;
    }

    /**
     * @param int $checkKeywordPositionPeriodicity
     * @return $this
     */
    public function setCheckKeywordPositionPeriodicity(int $checkKeywordPositionPeriodicity)
    {
        $this->checkKeywordPositionPeriodicity = $checkKeywordPositionPeriodicity;
        return $this;
    }

    /**
     * Timeout between requesting page from search engine. WARNING: If no timeout - search engine may disconnect with
     * error: 503 (bruteforce).
     *
     * @return int
     */
    public function getCheckKeywordPositionTimeoutBetweenRequests(): int
    {
        return $this->checkKeywordPositionTimeoutBetweenRequests;
    }

    /**
     * @param int $checkKeywordPositionTimeoutBetweenRequests
     * @return $this
     */
    public function setCheckKeywordPositionTimeoutBetweenRequests(int $checkKeywordPositionTimeoutBetweenRequests)
    {
        $this->checkKeywordPositionTimeoutBetweenRequests = $checkKeywordPositionTimeoutBetweenRequests;
        return $this;
    }

    /**
     * Instruction for search engine. How many sites per page we requesting.
     *
     * @return int
     */
    public function getCheckKeywordPositionRequestSitesPerPage(): int
    {
        return $this->checkKeywordPositionRequestSitesPerPage;
    }

    /**
     * @param int $checkKeywordPositionRequestSitesPerPage
     * @return $this
     */
    public function setCheckKeywordPositionRequestSitesPerPage(int $checkKeywordPositionRequestSitesPerPage)
    {
        if ($checkKeywordPositionRequestSitesPerPage < 1) {
            throw new \LogicException('$checkKeywordPositionRequestSitesPerPage can not be less than 1');
        }
        $this->checkKeywordPositionRequestSitesPerPage = $checkKeywordPositionRequestSitesPerPage;
        return $this;
    }
}