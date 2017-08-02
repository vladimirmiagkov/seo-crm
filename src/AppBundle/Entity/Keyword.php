<?php
declare(strict_types=1);

namespace AppBundle\Entity;

use AppBundle\Entity\Traits\ActiveTrait;
use AppBundle\Entity\Traits\CreatedByTrait;
use AppBundle\Entity\Traits\DeletedTrait;
use AppBundle\Entity\Traits\IdTrait;
use AppBundle\Entity\Traits\ModifiedAtTrait;
use AppBundle\Entity\Traits\ModifiedByTrait;
use AppBundle\Entity\Traits\NameTrait;
use AppBundle\Entity\Traits\CreatedAtTrait;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serialization;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Entity\Site;
use AppBundle\Entity\Page;
use AppBundle\Entity\SearchEngine;

/**
 * Promoted keyword for site.
 * SEO term. https://en.wikipedia.org/wiki/Keyword
 *
 * @ORM\Table(name="keyword")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\KeywordRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Keyword
{
    const ENTITY_TYPE = 'keyword'; // Identifier for frontend

    use IdTrait;
    use NameTrait;
    use ActiveTrait;
    use DeletedTrait;
    use CreatedByTrait;
    use ModifiedByTrait;
    use CreatedAtTrait;
    use ModifiedAtTrait;

    ///**
    // * Auto generated id
    // *
    // * @var int
    // *
    // * @ORM\Id
    // * @ORM\Column(type="integer", nullable=false)
    // * @ORM\GeneratedValue(strategy="AUTO")
    // * @Serialization\Groups({"list", "datablock"})
    // */
    //protected $id;
    //
    ///**
    // * Name for element
    // *
    // * @var string
    // *
    // * @ORM\Column(type="string", length=4096, nullable=false)
    // * @Serialization\Groups({"list", "datablock"})
    // * @Assert\NotBlank()
    // */
    //protected $name;
    //
    ///**
    // * Is element active?
    // *
    // * @var bool
    // *
    // * @ORM\Column(type="boolean", nullable=false)
    // * @Serialization\Groups({"list", "datablock"})
    // */
    //protected $active = true;

    /**
     * Linked site.
     * This "keyword" linked to "site".
     *
     * @var Site
     *
     * @ORM\ManyToOne(targetEntity="Site")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
     * @Serialization\Groups({"list"})
     */
    protected $site;

    /**
     * Linked "pages".
     * Doctrine owning side.
     *
     * @var Page|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Page", inversedBy="keywords")
     * @Serialization\Groups({"list"})
     * @Serialization\MaxDepth(4) // --4 - for dataBlock
     * //@Serialization\SerializedName("subobjs")
     */
    protected $pages;

    /**
     * Available (processed) "search engines" for this "keyword".
     * Like: Check positions in Google, Yandex...
     *
     * @var SearchEngine|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="SearchEngine")
     * @Serialization\Groups({"list"})
     * @Serialization\MaxDepth(3) // 3 = pages->keywords->searchengine
     */
    protected $searchEngines;

    /**
     * Our system search this keyword "from place".
     * Like: i search keyword "bestseller" from place "Denver"
     * Search engine term.
     *
     * @var string
     *
     * @ORM\Column(type="string", length=4096, nullable=true)
     * @Serialization\Groups({"list"})
     * //@Assert\NotBlank()
     */
    protected $fromPlace;

    /**
     * Max position to check, when we request search engine.
     * This is for limiting max requests.
     *
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=false)
     * @Serialization\Groups({"list"})
     * @Assert\NotBlank()
     */
    protected $searchEngineRequestLimit = 100;

    /**
     * Datetime of last check (get position) of the keyword position in SERP.
     * We check positions in all searchEngine's at one run.
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     * //@Serialization\Groups({"list"})
     */
    protected $positionLastCheck;

    /**
     * We 'lock' keyword at starting point of "getting positions" in search engine,
     * to prevent multiprocess "getting position" for same keyword at once.
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     * //@Serialization\Groups({"list"})
     */
    protected $positionLockedAt;


    public function __construct()
    {
        $this->pages = new ArrayCollection();
        $this->searchEngines = new ArrayCollection();
    }


    /**
     * @return Site
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * @param Site $site
     * @return $this
     */
    public function setSite(Site $site)
    {
        $this->site = $site;
        return $this;
    }

    /**
     * @return Page|ArrayCollection
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * @param Page $page
     * @return $this
     */
    public function addPage($page)
    {
        $page->addKeyword($this); // Synchronously updating inverse side.
        $this->pages[] = $page;
        return $this;
    }

    /**
     * @return SearchEngine|ArrayCollection
     */
    public function getSearchEngines()
    {
        return $this->searchEngines;
    }

    /**
     * @param SearchEngine|ArrayCollection $searchEngine
     * @return $this
     */
    public function addSearchEngine($searchEngine)
    {
        $this->searchEngines[] = $searchEngine;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getFromPlace()
    {
        return $this->fromPlace;
    }

    /**
     * @param string $fromPlace
     * @return $this
     */
    public function setFromPlace(string $fromPlace)
    {
        $this->fromPlace = $fromPlace;
        return $this;
    }

    /**
     * @return int
     */
    public function getSearchEngineRequestLimit(): int
    {
        return $this->searchEngineRequestLimit;
    }

    /**
     * @param int $searchEngineRequestLimit
     * @return $this
     */
    public function setSearchEngineRequestLimit(int $searchEngineRequestLimit)
    {
        $this->searchEngineRequestLimit = $searchEngineRequestLimit;
        return $this;
    }

    /**
     * @return null|\DateTime
     */
    public function getPositionLastCheck()
    {
        return $this->positionLastCheck;
    }

    /**
     * @param \DateTime $positionLastCheck
     * @return $this
     */
    public function setPositionLastCheck(\DateTime $positionLastCheck)
    {
        $this->positionLastCheck = $positionLastCheck;
        return $this;
    }

    /**
     * @return null|\DateTime
     */
    public function getPositionLockedAt()
    {
        return $this->positionLockedAt;
    }

    /**
     * @param \DateTime $positionLockedAt
     * @return $this
     */
    public function setPositionLockedAt(\DateTime $positionLockedAt)
    {
        $this->positionLockedAt = $positionLockedAt;
        return $this;
    }
}