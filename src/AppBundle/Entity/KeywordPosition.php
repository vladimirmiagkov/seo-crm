<?php
declare(strict_types=1);

namespace AppBundle\Entity;

use AppBundle\Entity\Keyword;
use AppBundle\Entity\SearchEngine;
use AppBundle\Entity\Traits\IdTrait;
use AppBundle\Entity\Traits\CreatedAtTrait;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serialization;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * SEO position (in SERP) for 1 keyword in 1 search engine for concrete date.
 * From 1 (top position in search engine) to 999 (scan limit: lowest position).
 *
 * @ORM\Table(name="keyword_position")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\KeywordPositionRepository")
 * @ORM\HasLifecycleCallbacks
 */
class KeywordPosition
{
    use IdTrait;
    use CreatedAtTrait;

    /**
     * Linked keyword.
     *
     * @var Keyword
     *
     * @ORM\ManyToOne(targetEntity="Keyword")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
     * //@Serialization\Groups({"list"})
     * //@Serialization\MaxDepth(1)
     */
    protected $keyword;

    /**
     * Linked search engine.
     *
     * @var SearchEngine
     *
     * @ORM\ManyToOne(targetEntity="SearchEngine")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
     * //@Serialization\Groups({"list"})
     * //@Serialization\MaxDepth(1)
     */
    protected $searchEngine;

    /**
     * SEO position for keyword in search engine.
     *  value = 0   position not found in SERP
     *              Add an record to the database every time(check),
     *              even if there was a connection error (prevent DDOS search engine)
     *  value = int found position in SERP (1 - 999)
     *
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false)
     * //@Serialization\Groups({"list"})
     */
    protected $position;

    /**
     * Site url in SERP.
     * Like: "https://www.example.com/123.html?shownews=1#top" | null
     *
     * @var string
     *
     * @ORM\Column(type="string", length=4096, nullable=true)
     * //@Serialization\Groups({"list"})
     * //@Assert\NotBlank()
     */
    protected $url;


    public function __construct()
    {
    }


    /**
     * @return Keyword
     */
    public function getKeyword(): Keyword
    {
        return $this->keyword;
    }

    /**
     * @param Keyword $keyword
     * @return $this
     */
    public function setKeyword(Keyword $keyword)
    {
        $this->keyword = $keyword;
        return $this;
    }

    /**
     * @return SearchEngine
     */
    public function getSearchEngine(): SearchEngine
    {
        return $this->searchEngine;
    }

    /**
     * @param SearchEngine $searchEngine
     * @return $this
     */
    public function setSearchEngine(SearchEngine $searchEngine)
    {
        $this->searchEngine = $searchEngine;
        return $this;
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @param int $position
     * @return $this
     */
    public function setPosition(int $position)
    {
        $this->position = $position;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl(string $url)
    {
        $this->url = trim($url);
        return $this;
    }
}