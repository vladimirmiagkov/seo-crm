<?php
declare(strict_types=1);

namespace SiteAnalyzerBundle\Site\DTO;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Options for site download.
 */
class SiteDownloadOptionsDTO
{
    /**
     * Some unique id, for site identification. Usually you db id.
     *
     * @var integer
     * @Assert\NotBlank()
     */
    public $siteId;

    /**
     * Some unique site stamp(site copy name), for stamp identification.
     *
     * @var string
     * @Assert\NotBlank()
     */
    public $siteStampName;

    /**
     * Fully qualified site uri, like "http://www.somesite.com"
     *
     * @var string
     * @Assert\NotBlank()
     * @Assert\Url(
     *    protocols = {"http", "https"}
     * )
     */
    public $siteHost;

    /**
     * Use remote browser with full javascript processing?
     * If true: we load site in remote browser, and wait for all resources fully loaded, then grab source html and
     * links.
     *
     * @var bool
     * @Assert\Type(type="bool", message="The value {{ value }} is not a valid {{ type }}.")
     */
    public $useRemoteBrowserForCrawling = false;

    /**
     * Interval between page download, seconds.
     *
     * @var integer
     * @Assert\NotBlank()
     * @Assert\Range(
     *     min = 0,
     *     max = 3600,
     *     minMessage = "Min interval between page download, seconds: {{ limit }}",
     *     maxMessage = "Max interval between page download, seconds: {{ limit }}"
     * )
     */
    public $intervalBetweenPageDownload = 0;

    /**
     * Max time limit for site download, seconds. Default = 432000 (5 days)
     *
     * @var integer
     * @Assert\NotBlank()
     * @Assert\Range(
     *     min = 1,
     *     max = 2678400,
     *     minMessage = "Min time limit for site download, seconds: {{ limit }}",
     *     maxMessage = "Max time limit for site download, seconds: {{ limit }}"
     * )
     */
    public $maxTimeLimitForSiteDownload = 432000;

    /**
     * CURL: The number of seconds to wait while trying to connect. Use 0 to wait indefinitely.
     *
     * @var int
     * @Assert\NotBlank()
     * @Assert\Range(
     *     min = 0,
     *     max = 86400,
     *     minMessage = "Min connection timeout, seconds: {{ limit }}",
     *     maxMessage = "Max connection timeout, seconds: {{ limit }}"
     * )
     */
    public $curlConnectTimeout = 60;

    /**
     * CURL: The maximum number of seconds to allow cURL functions to execute.
     *
     * @var int
     * @Assert\NotBlank()
     * @Assert\Range(
     *     min = 0,
     *     max = 86400,
     *     minMessage = "Min connection timeout, seconds: {{ limit }}",
     *     maxMessage = "Max connection timeout, seconds: {{ limit }}"
     * )
     */
    public $curlTimeout = 60;

    /**
     * Max depth level limit for site download (>=0). Level 0 = site root.
     * Tip: commonly believed that the levels from 0 to 7 - most important for SEO.
     *
     * @var integer
     * @Assert\NotBlank()
     * @Assert\Range(
     *     min = 0,
     *     max = 255,
     *     minMessage = "Min depth level limit for site download: {{ limit }}",
     *     maxMessage = "Max depth level limit for site download: {{ limit }}"
     * )
     */
    public $maxDepthLevelLimitForSiteDownload = 100;

    /**
     * What path we need to exlude from crawling site?
     * Like ['/articles/*', '/admin']
     *
     * @var array
     * @Assert\Type(type="array", message="The value {{ value }} is not a valid {{ type }}.")
     */
    public $excludePathsFromCrawling; //todo: implement

    /**
     * When crawling site: follow "nofollow" links?
     *
     * @var bool
     * @Assert\Type(type="bool", message="The value {{ value }} is not a valid {{ type }}.")
     */
    public $followNoFollowLinks = false;

    /**
     * Check external links for 404? While download and parse pages, we can try to check(download) all external links.
     * Why? Try to find broken outgoing-external links.
     *
     * @var bool
     * @Assert\Type(type="bool", message="The value {{ value }} is not a valid {{ type }}.")
     */
    public $checkExternalLinksFor404 = false; //todo: implement
}