<?php
declare(strict_types=1);

namespace AppBundle\Entity;

use AppBundle\Entity\Traits\ActiveTrait;
use AppBundle\Entity\Traits\CreatedByTrait;
use AppBundle\Entity\Traits\IdTrait;
use AppBundle\Entity\Traits\CreatedAtTrait;
use AppBundle\Entity\Traits\ModifiedAtTrait;
use AppBundle\Entity\Traits\ModifiedByTrait;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serialization;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Entity\Site;
use AppBundle\Entity\SiteStamp;

/**
 * SiteSchedule.
 *
 * @ORM\Table(name="site_schedule")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SiteScheduleRepository")
 * @ORM\HasLifecycleCallbacks
 */
class SiteSchedule
{
    const USE_DEFAULT = 'use_default';
    const USE_ROBOTSTXT = 'use_robots.txt';
    const DONT_USE_ROBOTSTXT = 'dont_use_robots.txt';
    const USE_ALL_DISALLOWS = 'use_all_disallows';

    use IdTrait;
    use ActiveTrait;
    use CreatedByTrait;
    use ModifiedByTrait;
    use CreatedAtTrait;
    use ModifiedAtTrait;

    /**
     * Linked site.
     *
     * @var Site
     *
     * @ORM\OneToOne(targetEntity="Site")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
     * @Serialization\Groups({"list"})
     * @Serialization\MaxDepth(1)
     */
    protected $site;


    // Specific schedule fields \/ ////////////////////////////////////////////////////////////////////////////////////

    /**
     * Interval between site download, seconds.
     *
     * @var string
     * @ORM\Column(type="string", nullable=false)
     * @Serialization\Groups({"list"})
     * @Assert\NotBlank()
     */
    protected $intervalBetweenSiteDownload = self::USE_DEFAULT;

    /**
     * Interval between page download, seconds.
     *
     * @var string
     * @ORM\Column(type="string", nullable=false)
     * @Serialization\Groups({"list"})
     * @Assert\NotBlank()
     */
    protected $intervalBetweenPageDownload = self::USE_ROBOTSTXT;

    /**
     * Max time limit for site download, seconds.
     *
     * @var string
     * @ORM\Column(type="string", nullable=false)
     * @Serialization\Groups({"list"})
     * @Assert\NotBlank()
     */
    protected $maxTimeLimitForSiteDownload = self::USE_DEFAULT;

    /**
     * Max depth level limit for site download (>=0). Level 0 = site root.
     * Tip: commonly believed that the levels from 0 to 7 - most important for SEO.
     *
     * @var string
     * @ORM\Column(type="string", nullable=false)
     * @Serialization\Groups({"list"})
     * @Assert\NotBlank()
     */
    protected $maxDepthLevelLimitForSiteDownload = self::USE_DEFAULT;

    /**
     * Do we need to use robots.txt disallow directives?
     *
     * @var string
     * @ORM\Column(type="string", nullable=false)
     * @Serialization\Groups({"list"})
     * @Assert\NotBlank()
     */
    protected $useUserAgentFromRobotsTxt = self::USE_ALL_DISALLOWS; // DONT_USE_ROBOTSTXT, USE_ALL_DISALLOWS, "Google", ...

    /**
     * When crawling site: follow "nofollow" links?
     *
     * @var bool
     * @ORM\Column(type="boolean", nullable=false)
     * @Serialization\Groups({"list"})
     * @Assert\Type(type="bool", message="The value {{ value }} is not a valid {{ type }}.")
     */
    protected $followNoFollowLinks = false;

    /**
     * Check external links for 404? While download and parse pages, we can try to check(download) all external links.
     * Why? Try to find broken outgoing-external links.
     *
     * @var bool
     * @ORM\Column(type="boolean", nullable=false)
     * @Serialization\Groups({"list"})
     * @Assert\Type(type="bool", message="The value {{ value }} is not a valid {{ type }}.")
     */
    protected $checkExternalLinksFor404 = false;

    // Specific schedule fields /\ ////////////////////////////////////////////////////////////////////////////////////


    public function __construct()
    {

    }

    /**
     * @return Site
     */
    public function getSite(): Site
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
     * @return int
     */
    public function getIntervalBetweenSiteDownload(): int
    {
        if ($this->intervalBetweenSiteDownload === self::USE_DEFAULT) {
            return 3600 * 24 * 7; //TODO
        }

        return (int)$this->intervalBetweenSiteDownload;
    }

    /**
     * @param string $intervalBetweenSiteDownload
     * @return $this
     */
    public function setIntervalBetweenSiteDownload($intervalBetweenSiteDownload)
    {
        $this->intervalBetweenSiteDownload = $intervalBetweenSiteDownload;
        return $this;
    }

    /**
     * @return int
     */
    public function getIntervalBetweenPageDownload(): int
    {
        if ($this->intervalBetweenPageDownload === self::USE_DEFAULT) {
            return 2; //TODO
        } elseif ($this->intervalBetweenPageDownload === self::USE_ROBOTSTXT) {
            return 4; //TODO
        }

        return (int)$this->intervalBetweenPageDownload;
    }

    /**
     * @param string $intervalBetweenPageDownload
     * @return $this
     */
    public function setIntervalBetweenPageDownload($intervalBetweenPageDownload)
    {
        $this->intervalBetweenPageDownload = $intervalBetweenPageDownload;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxTimeLimitForSiteDownload(): int
    {
        if ($this->maxTimeLimitForSiteDownload === self::USE_DEFAULT) {
            return 3600 * 24 * 7; //TODO
        }

        return (int)$this->maxTimeLimitForSiteDownload;
    }

    /**
     * @param string $maxTimeLimitForSiteDownload
     * @return $this
     */
    public function setMaxTimeLimitForSiteDownload($maxTimeLimitForSiteDownload)
    {
        $this->maxTimeLimitForSiteDownload = $maxTimeLimitForSiteDownload;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxDepthLevelLimitForSiteDownload(): int
    {
        if ($this->maxDepthLevelLimitForSiteDownload === self::USE_DEFAULT) {
            return 100; //TODO
        }

        return (int)$this->maxDepthLevelLimitForSiteDownload;
    }

    /**
     * @param string $maxDepthLevelLimitForSiteDownload
     * @return $this
     */
    public function setMaxDepthLevelLimitForSiteDownload($maxDepthLevelLimitForSiteDownload)
    {
        $this->maxDepthLevelLimitForSiteDownload = $maxDepthLevelLimitForSiteDownload;
        return $this;
    }

    /**
     * @return string
     */
    public function getUseUserAgentFromRobotsTxt(): string
    {
        return $this->useUserAgentFromRobotsTxt;
    }

    /**
     * @param string $useUserAgentFromRobotsTxt
     * @return $this
     */
    public function setUseUserAgentFromRobotsTxt($useUserAgentFromRobotsTxt)
    {
        $this->useUserAgentFromRobotsTxt = $useUserAgentFromRobotsTxt;
        return $this;
    }

    /**
     * @return bool
     */
    public function isFollowNoFollowLinks(): bool
    {
        return $this->followNoFollowLinks;
    }

    /**
     * @param bool $followNoFollowLinks
     * @return $this
     */
    public function setFollowNoFollowLinks($followNoFollowLinks)
    {
        $this->followNoFollowLinks = filter_var($followNoFollowLinks, FILTER_VALIDATE_BOOLEAN);
        return $this;
    }

    /**
     * @return bool
     */
    public function isCheckExternalLinksFor404(): bool
    {
        return $this->checkExternalLinksFor404;
    }

    /**
     * @param bool $checkExternalLinksFor404
     * @return $this
     */
    public function setCheckExternalLinksFor404($checkExternalLinksFor404)
    {
        $this->checkExternalLinksFor404 = filter_var($checkExternalLinksFor404, FILTER_VALIDATE_BOOLEAN);
        return $this;
    }
}