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

/**
 * Site.
 *
 * @ORM\Table(name="site")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SiteRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Site
{
    use IdTrait;
    use NameTrait;
    use ActiveTrait;
    use DeletedTrait;
    use CreatedByTrait;
    use ModifiedByTrait;
    use CreatedAtTrait;
    use ModifiedAtTrait;

    const SEO_STRATEGY_KEYWORDS_LINKED_TO_PAGES = 0;
    const SEO_STRATEGY_PAGES_LINKED_TO_KEYWORD = 1;

    /**
     * Site URI in puny representation. Example: http://www.xn----123.xn--123
     *
     * @var string
     *
     * @ORM\Column(type="string", length=4096, nullable=false)
     * @Serialization\Groups({"list"})
     * @Assert\NotBlank()
     */
    protected $namePuny;

    /**
     * Strategy for seo linking: keywords against pages.
     * Seo business logic.
     *
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false)
     * @Serialization\Groups({"list"})
     * @Assert\NotBlank()
     */
    protected $seoStrategyKeywordPage = self::SEO_STRATEGY_KEYWORDS_LINKED_TO_PAGES;


    public function __construct()
    {

    }


    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = (string)$name;
        $this->namePuny = (string)$name; //TODO: decode to puny representation
        return $this;
    }

    /**
     * @return string
     */
    public function getNamePuny()
    {
        return $this->namePuny;
    }

    /**
     * @return int
     */
    public function getSeoStrategyKeywordPage(): int
    {
        return $this->seoStrategyKeywordPage;
    }

    /**
     * @param int $seoStrategyKeywordPage
     * @return $this
     */
    public function setSeoStrategyKeywordPage($seoStrategyKeywordPage)
    {
        if (
            $seoStrategyKeywordPage !== self::SEO_STRATEGY_KEYWORDS_LINKED_TO_PAGES
            && $seoStrategyKeywordPage !== self::SEO_STRATEGY_PAGES_LINKED_TO_KEYWORD
        ) {
            throw new \InvalidArgumentException('Unavailable $seoStrategyKeywordPage.');
        } else {
            $this->seoStrategyKeywordPage = $seoStrategyKeywordPage;
        }

        return $this;
    }
}