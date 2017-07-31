<?php
declare(strict_types=1);

namespace SiteAnalyzerBundle\Resource\Dom;


class Link
{
    const TYPE_OUTGOING_INTERNAL = 0; // Link to our internal site pages.
    const TYPE_OUTGOING_EXTERNAL = 1; // Link to external sites.
    //const TYPE_INCOMING_INTERNAL = 2; // link can't be that type??
    //const TYPE_INCOMING_EXTERNAL = 3; // link can't be that type??

    /**
     * Fully qualified link uri, like "http://www.example.com/123.html"
     *
     * @var string
     */
    protected $url;
    /**
     * Original href for link, like "/123.html"
     *
     * @var string
     */
    protected $hrefOriginal;
    /**
     * Text on link. Like: <a href="...">Text</a>
     *
     * @var string
     */
    protected $text;
    /**
     * Title attribute in link. Like: <a href="..." title="Title">...</a>
     *
     * @var string
     */
    protected $title;
    /**
     * Link type: external or internal.
     *
     * @var integer
     */
    protected $type;
    /**
     * Link flag nofollow.
     * <a href="..." rel="nofollow">...</a>
     *
     * @see http://www.w3schools.com/tags/att_a_rel.asp
     *
     * @var bool
     */
    protected $noFollow = false;
    /**
     * Is this url excluded by site robots.txt?
     *
     * @var bool
     */
    protected $urlExcludedByRobotsTxt = false;//todo: implement


    /**
     * Get full uri, like "http://www.example.com/123.html"
     *
     * @return null|string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set full uri, like "http://www.example.com/123.html"
     *
     * @param string $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = mb_strtolower(trim((string)$url));
        return $this;
    }

    /**
     * Get original href for link, like "/123.HTML".
     * Case sensitive.
     *
     * @return null|string
     */
    public function getHrefOriginal()
    {
        return $this->hrefOriginal;
    }

    /**
     * Set original href for link, like "/123.HTML".
     * Case sensitive.
     *
     * @param string $hrefOriginal
     * @return $this
     */
    public function setHrefOriginal($hrefOriginal)
    {
        $this->hrefOriginal = (string)$hrefOriginal;
        return $this;
    }

    /**
     * Get text on link. Like: <a href="...">Text</a>
     *
     * @return null|string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set text on link. Like: <a href="...">Text</a>
     *
     * @param string $text
     * @return $this
     */
    public function setText($text)
    {
        $this->text = (string)$text;
        return $this;
    }

    /**
     * Get title attribute in link. Like: <a href="..." title="Title">...</a>
     *
     * @return null|string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set title attribute in link. Like: <a href="..." title="Title">...</a>
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = (string)$title;
        return $this;
    }

    /**
     * Get link type: external or internal.
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Calculate type for this link: external or internal.
     *
     * @param string $linkUri    Uri from link,  like "/1/123.html" or "http://www.example.com/1/123.html" or
     *                           "http://www.twitter.com"
     * @param string $currentUri Full site uri + path, like "http://www.example.com/1/"
     * @return $this
     */
    public function setType($linkUri, $currentUri)
    {
        $linkUri = trim((string)$linkUri);
        $linkUriScheme = parse_url($linkUri, PHP_URL_SCHEME);
        $linkUriHost = parse_url($linkUri, PHP_URL_HOST);

        $currentUri = trim((string)$currentUri);
        $currentUriScheme = parse_url($currentUri, PHP_URL_HOST);

        if (empty($linkUriScheme)
            || $linkUriHost === $currentUriScheme
        ) {
            $this->type = self::TYPE_OUTGOING_INTERNAL;
        } else {
            $this->type = self::TYPE_OUTGOING_EXTERNAL;
        }

        return $this;
    }

    /**
     * Is this link flagged 'nofollow'?
     * <a href="..." rel="nofollow">...</a>
     *
     * @return bool
     */
    public function isNoFollow(): bool
    {
        return $this->noFollow;
    }

    /**
     * Calculate "nofollow" flag for current link.
     * Search for rel 'nofollow' in: rel="nofollow"
     * <a href="..." rel="nofollow">...</a>
     *
     * @param string $rel
     * @return $this
     */
    public function setNoFollow($rel)
    {
        if (!empty($rel) && false !== mb_strpos($rel, 'nofollow')) {
            $this->noFollow = true;
        } else {
            $this->noFollow = false;
        }

        return $this;
    }

    /**
     * @return boolean
     */
    public function isUrlExcludedByRobotsTxt(): bool
    {
        return $this->urlExcludedByRobotsTxt;
    }

    /**
     * @param boolean $urlExcludedByRobotsTxt
     * @return $this
     */
    public function setUrlExcludedByRobotsTxt($urlExcludedByRobotsTxt)
    {
        $this->urlExcludedByRobotsTxt = (bool)$urlExcludedByRobotsTxt;
        return $this;
    }
}