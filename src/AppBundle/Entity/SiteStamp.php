<?php
declare(strict_types=1);

namespace AppBundle\Entity;

use AppBundle\Entity\Traits\IdTrait;
use AppBundle\Entity\Traits\CreatedAtTrait;
use AppBundle\Entity\Traits\ModifiedAtTrait;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serialization;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Entity\SiteSchedule;

/**
 * Site stamp. Crawled (downloaded) copy of site for concrete date.
 * It's like google search engine make full cached version of you site with ALL pages and resources.
 *
 * @ORM\Table(name="site_stamp")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SiteStampRepository")
 * @ORM\HasLifecycleCallbacks
 */
class SiteStamp
{
    use IdTrait;
    use CreatedAtTrait;
    use ModifiedAtTrait;

    /**
     * Linked site schedule.
     *
     * @var SiteSchedule
     *
     * @ORM\ManyToOne(targetEntity="SiteSchedule")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
     * @Serialization\Groups({"list"})
     */
    protected $schedule;

    /**
     * Purpose: Just store there serialized array of initial SiteSchedule options. For debug maybe.
     * (Because main SiteSchedule options(linked to site) can be changed in the future.)
     *
     * @var string
     */
    protected $archiveInitialScheduleOptions;

    /**
     * @return SiteSchedule
     */
    public function getSchedule(): SiteSchedule
    {
        return $this->schedule;
    }

    /**
     * @param SiteSchedule $schedule
     * @return $this
     */
    public function setSchedule(SiteSchedule $schedule)
    {
        $this->schedule = $schedule;
        return $this;
    }
}