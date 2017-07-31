<?php
declare(strict_types=1);

namespace AppBundle\SearchEngine;

use AppBundle\Entity\KeywordCompetitor;
use AppBundle\Entity\Traits\ErrorsTrait;
use AppBundle\Entity\Traits\RequestsResponsesTrait;
use AppBundle\Entity\Traits\StatusTrait;

/**
 * Parsed results from search engine (parsed SERP).
 * (List of all founded sites + additional info)
 */
class SerpResult
{
    use ErrorsTrait;
    use StatusTrait;
    use RequestsResponsesTrait;

    const STATUS_ALL_GOOD = 0;
    const STATUS_SEARCH_ENGINE_INVALID_ARGUMENT = 1;
    const STATUS_SEARCH_ENGINE_NOT_AVAILABLE = 2;
    const STATUS_SEARCH_ENGINE_ERROR = 4;

    /**
     * Array of source html "Request".
     *
     * @var null|\string[]
     */
    protected $requests;

    /**
     * Array of source html "Response".
     *
     * @var null|\string[]
     */
    protected $responses;

    /**
     * One status for request + response + errors...
     *
     * @var \int
     */
    protected $status = self::STATUS_ALL_GOOD;

    /**
     * Array of KeywordCompetitor.
     * Index = site position in SERP.
     *
     * @var null|KeywordCompetitor[]
     */
    protected $sites;

    /**
     * Our goal site index (in $sites) in SERP
     *
     * @var null|\int
     */
    protected $goalSiteIndex;

    /**
     * Array of human readable "errors" and maybe "warnings".
     *
     * @var null|\string[]
     */
    protected $errors;


    public function __construct()
    {
    }


    /**
     * Find goal site, if exists.
     *
     * @return null|KeywordCompetitor
     */
    public function findGoalSite()
    {
        if (null !== $this->getGoalSiteIndex()) {
            /** @var KeywordCompetitor $keywordCompetitor */
            $keywordCompetitor = $this->getSites()[$this->getGoalSiteIndex()];
            return $keywordCompetitor;
        }

        return null;
    }

    /**
     * @return bool
     */
    public function didWeFoundSomeSitesInSerp()
    {
        if (null !== $this->getSites()) {
            return true;
        }

        return false;
    }

    // Getters and Setters =============================================================================================

    /**
     * @return KeywordCompetitor[]|null
     */
    public function getSites()
    {
        return $this->sites;
    }

    /**
     * Add / replace site.
     *
     * @param \int              $index
     * @param KeywordCompetitor $site
     */
    public function setSiteByIndex(int $index, KeywordCompetitor $site)
    {
        $this->sites[$index] = $site;
    }

    /**
     * @return int|null
     */
    public function getGoalSiteIndex()
    {
        return $this->goalSiteIndex;
    }

    /**
     * @param int|null $goalSiteIndex
     * @return $this
     */
    public function setGoalSiteIndex(int $goalSiteIndex)
    {
        $this->goalSiteIndex = $goalSiteIndex;
        return $this;
    }
}