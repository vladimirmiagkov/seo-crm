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
use AppBundle\Entity\Keyword;

/**
 * Promoted page on the site.
 *
 * @ORM\Table(name="page")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PageRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Page
{
    /**
     * Identifier for frontend.
     */
    const ENTITY_TYPE = 'page';

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
     *
     * @var Site
     *
     * @ORM\ManyToOne(targetEntity="Site")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
     * @Serialization\Groups({"list"})
     */
    protected $site;

    /**
     * Linked keywords. Doctrine inverse side.
     *
     * @var Keyword|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Keyword", mappedBy="pages")
     * @Serialization\Groups({"list"})
     * @Serialization\MaxDepth(4) // --4 - for dataBlock
     * //@Serialization\SerializedName("subobjs")
     */
    protected $keywords;


    public function __construct()
    {
        $this->keywords = new ArrayCollection();
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
     * @return Keyword|ArrayCollection
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * @param Keyword|ArrayCollection $keyword
     * @return $this
     */
    public function addKeyword(Keyword $keyword)
    {
        $this->keywords[] = $keyword;
        return $this;
    }
}