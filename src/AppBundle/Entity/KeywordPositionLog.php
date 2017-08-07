<?php
declare(strict_types=1);

namespace AppBundle\Entity;

use AppBundle\Entity\Traits\ErrorsTrait;
use AppBundle\Entity\Traits\IdTrait;
use AppBundle\Entity\Traits\RequestsResponsesTrait;
use AppBundle\Entity\Traits\StatusTrait;
use AppBundle\SearchEngine\SerpResult;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serialization;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Log keyword position check in search engine.
 * Use for debug, and monitoring.
 *
 * @ORM\Table(name="keyword_position_log")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\KeywordPositionLogRepository")
 * @ORM\HasLifecycleCallbacks
 */
class KeywordPositionLog
{
    use IdTrait;
    use ErrorsTrait;
    use StatusTrait;
    use RequestsResponsesTrait;

    /**
     * Linked KeywordPosition.
     *
     * @var KeywordPosition
     *
     * @ORM\OneToOne(targetEntity="KeywordPosition")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
     * @Serialization\Groups({"list"})
     * //@Serialization\MaxDepth(1)
     */
    protected $keywordPosition;

    /**
     * One status for request + response + errors...
     *
     * @var \int
     *
     * @ORM\Column(type="integer", nullable=false)
     * @Serialization\Groups({"list"})
     */
    protected $status = SerpResult::STATUS_NO_RESULTS_YET;


    public function __construct()
    {
    }


    /**
     * @return KeywordPosition
     */
    public function getKeywordPosition(): KeywordPosition
    {
        return $this->keywordPosition;
    }

    /**
     * @param KeywordPosition $keywordPosition
     * @return $this
     */
    public function setKeywordPosition(KeywordPosition $keywordPosition)
    {
        $this->keywordPosition = $keywordPosition;
        return $this;
    }
}