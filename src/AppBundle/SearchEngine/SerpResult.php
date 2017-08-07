<?php
declare(strict_types=1);

namespace AppBundle\SearchEngine;

use AppBundle\Entity\Keyword;
use AppBundle\Entity\KeywordCompetitor;
use AppBundle\Entity\KeywordPosition;
use AppBundle\Entity\SearchEngine;
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

    /**
     * Default begin status. No search engine requested yet.
     */
    const STATUS_NO_RESULTS_YET = 0;
    /**
     * Search engine requested successfully. No errors.
     */
    const STATUS_ALL_GOOD = 1;
    /**
     * Invalid arguments for search engine.
     */
    const STATUS_SEARCH_ENGINE_INVALID_ARGUMENT = 2;
    /**
     * Search engine: not available; no connection; connection refused; ...
     */
    const STATUS_SEARCH_ENGINE_NOT_AVAILABLE = 4;
    /**
     * Some error from search engine: cannot parse SERP; some other error; ...
     */
    const STATUS_SEARCH_ENGINE_ERROR = 8;

    /**
     * Linked keyword.
     *
     * @var Keyword
     */
    protected $keyword;

    /**
     * Linked search engine.
     *
     * @var SearchEngine
     */
    protected $searchEngine;

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
    protected $status = self::STATUS_NO_RESULTS_YET;

    /**
     * Array of KeywordCompetitor.
     * Index = site position in SERP.
     *
     * @var null|KeywordCompetitor[]
     */
    protected $sites;

    /**
     * If we found our goal site - we set KeywordPosition.
     *
     * @var null|KeywordPosition
     */
    protected $keywordPosition = null;

    /**
     * Array of human readable "errors" and maybe "warnings".
     *
     * @var null|\string[]
     */
    protected $errors;

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
     * Set KeywordPosition to serp.
     *
     * @param int $goalSiteIndex
     * @return $this
     */
    public function setKeywordPosition(int $goalSiteIndex)
    {
        $this->keywordPosition = (new KeywordPosition())
            ->setKeyword($this->getkeyword())
            ->setSearchEngine($this->getSearchEngine())
            ->setPosition(($goalSiteIndex + 1))
            ->setUrl($this->getSites()[$goalSiteIndex]->getUrl());

        return $this;
    }

    /**
     * @return KeywordPosition|null
     */
    public function getKeywordPosition()
    {
        return $this->keywordPosition;
    }
}