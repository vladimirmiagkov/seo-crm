<?php
declare(strict_types=1);

namespace SiteAnalyzerBundle\Site;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Predis\Client as RedisClient;
use SiteAnalyzerBundle\Site\ElasticSearch;
use DownloaderBundle\Service\RemoteBrowserDownloaderService;
use SiteAnalyzerBundle\Site\PageDownloader;
use SiteAnalyzerBundle\Resource\HtmlPage;
use SiteAnalyzerBundle\Site\DTO\SiteDownloadOptionsDTO;
use GuzzleHttp\Psr7\Uri;

class SiteDownloader
{
    /**
     * @var ValidatorInterface
     */
    protected $validator;
    /**
     * @var RedisClient
     */
    protected $redis;
    /**
     * @var ElasticSearch
     */
    protected $elasticSearch;
    /**
     * @var RemoteBrowserDownloaderService
     */
    protected $remoteBrowser;
    /**
     * @var PageDownloader
     */
    protected $pageDownloader;

    public function __construct(
        ValidatorInterface $validator,
        RedisClient $redis,
        ElasticSearch $elasticSearch,
        RemoteBrowserDownloaderService $remoteBrowser,
        PageDownloader $pageDownloader
    )
    {
        $this->validator = $validator;
        $this->redis = $redis;
        $this->elasticSearch = $elasticSearch;
        $this->remoteBrowser = $remoteBrowser;
        $this->pageDownloader = $pageDownloader;
    }

    /**
     * Download whole site from root.
     * We can't store whole website in memory because memory exceeded fast.
     * So, we dump every single page to ElasticSearch database.
     *
     * @param SiteDownloadOptionsDTO $options
     * @return bool
     * @throws \Exception
     */
    public function downloadSite(SiteDownloadOptionsDTO $options): bool
    {
        $this->validateSiteDownloadOptionsDTO($options);
        $elasticSearchSiteId = $options->siteId . '_' . $options->siteStampName;

        try {
            $this->elasticSearch->deleteSiteIndex($elasticSearchSiteId);
        } catch (\Exception $e) {
        }
        $this->elasticSearch->createSiteIndex($elasticSearchSiteId);

        echo 'SITE DOWNLOADING START<br>';
        $urisDone = []; // Array of already downloaded uri's.
        $siteDepthLevel = 0;
        $urisScheduled = [$options->siteHost => ['parent' => null, 'depthLevel' => $siteDepthLevel]]; // Start from site root.
        while (!empty($urisScheduled)) {
            echo '---- site depth level: ' . $siteDepthLevel . ' -------------------------------------------- ' . 'memory_get_peak_usage=' . round(memory_get_peak_usage(true) / 1024 / 1024, 1) . 'mb' . '<br>';
            foreach ($urisScheduled as $uri => $uriParams) {
                echo date('Y-m-d H:i:s') . ' downloading uri="' . $uri . '"' . '<br>';
                $urisDone[$uri] = $uriParams; // Mark uri as downloaded.
                unset($urisScheduled[$uri]); // Remove uri from download schedule.

                $htmlPage = $this->downloadUriForCrawler($uri, $options);
                if (null === $htmlPage) { // Can't download page for some reasons.
                    continue; // Bad url.
                }

                $response = $this->elasticSearch->addPageToSite($elasticSearchSiteId, $htmlPage);
                //echo '<pre>', htmlspecialchars(print_r($response, true)), '</pre>';

                if ($htmlPage->getResponseCode() < 200 || $htmlPage->getResponseCode() >= 400) { // Bad response code.
                    continue; // Bad url.
                }

                $crawlableUrisFromPage = self::getUniqueCrawlableUris($htmlPage, $options);
                // Add uncrawled uri's to schedule.
                foreach ($crawlableUrisFromPage as $uriPlanned => $uriParamsPlanned) {
                    if (!isset($urisDone[$uriPlanned])) {
                        $urisScheduled[$uriPlanned] = ['parent' => $uri, 'depthLevel' => $siteDepthLevel];
                    }
                }
            }
            $siteDepthLevel++;
            sleep($options->intervalBetweenPageDownload);
        };
        echo 'SITE DOWNLOADING DONE ' . 'memory_get_peak_usage=' . round(memory_get_peak_usage(true) / 1024 / 1024, 1) . 'mb' . '<br>';


        self::buildSiteTreeByDepthLevel($urisDone);
        echo 'buildSiteTreeByDepthLevel... ' . 'memory_get_peak_usage=' . round(memory_get_peak_usage(true) / 1024 / 1024, 1) . 'mb' . '<br>';

        //todo: save $urisDone to es

        return true;
    }

    /**
     * Build site tree by "site depth level" (find order for each uri).
     * We modify array by reference: because memory limit. Goal: minimum memory overhead.
     * Input array format:
     *   uri =>
     *     'parent' => uri
     *
     * Modify to:
     *   uri =>
     *     'parent' => uri
     *     'siteTreeOrder' => 0, 1, 2, ...
     *
     * @param array $a Input flat array of downloaded uri's with params.
     */
    public static function buildSiteTreeByDepthLevel(array &$a)
    {
        $siteTreeOrder = 0;
        $parentsTmpList = [];
        foreach ($a as $uri => &$uriParams) {
            if ($uriParams['parent'] === null) { // Site root
                $siteTreeOrder = 0;
                $uriParams['siteTreeOrder'] = $siteTreeOrder;
                $parentsTmpList[] = (string)$uri;
                continue;
            }
            foreach ($a as $uri2 => &$uriParams2) {
                if (!isset($uriParams2['siteTreeOrder'])) {
                    if ($parentsTmpList[count($parentsTmpList) - 1] === $uriParams2['parent']) {
                        $siteTreeOrder++;
                        $uriParams2['siteTreeOrder'] = $siteTreeOrder;
                        $parentsTmpList[] = (string)$uri2;
                    }
                }
            }
            array_pop($parentsTmpList);
        }
    }

    /**
     * Download and parse one uri.
     *
     * @param string                 $uri
     * @param SiteDownloadOptionsDTO $options
     * @return null|HtmlPage
     */
    protected function downloadUriForCrawler(string $uri, SiteDownloadOptionsDTO $options)
    {
        try {
            $htmlPage = $this->pageDownloader->downloadPage($uri, $options);
        } catch (\Exception $e) {
            return null;
        }

        return $htmlPage;
    }

    /**
     * Get array of unique crawlable uri's from page.
     *
     * @param HtmlPage               $htmlPage
     * @param SiteDownloadOptionsDTO $options
     * @return array
     */
    public static function getUniqueCrawlableUris(HtmlPage $htmlPage, SiteDownloadOptionsDTO $options): array
    {
        $result = [];
        foreach ($htmlPage->getLinks() as $link) {
            if ($link->isNoFollow() && $options->followNoFollowLinks) {
                continue;
            }

            if (null !== $crawlableLink = self::getCrawlableUri($options->siteHost, $link->getUrl(), $htmlPage->getBaseUri())) {
                $result[$crawlableLink] = true;
            }
        }

        return $result;
    }

    /**
     * If url is crawlable: return same or modified url(with apply constraints to crawlable uri).
     * If url is NOT crawlable: return null.
     *
     * @param string      $siteHost     Like: "http://www.example.com"
     * @param string      $siteUri      Like: "http://www.example.com/123/" or "/123/" or "?id=1"
     * @param null|string $siteHeadBase Base tag in page head(if present). Like: <base href="http://www.example.com/" />
     * @return null|string
     */
    public static function getCrawlableUri(string $siteHost, string $siteUri, $siteHeadBase = null)
    {
        $parsedSiteHost = new Uri($siteHost);

        if (!empty($siteHeadBase)) {
            $siteHeadBase = trim($siteHeadBase);
            try {
                $parsedSiteHeadBase = new Uri($siteHeadBase);
            } catch (\Exception $e) {
            }
        }

        $resultUri = trim($siteUri);
        // Little replacements
        if ($resultUri === '') {
            return null;
        }
        $resultUri = mb_strtolower($resultUri, 'UTF-8');
        //$resultUri = urldecode($resultUri);
        $resultUri = str_replace('&amp;', '&', $resultUri); // "/catalog/index.php?SECTION_ID=221&amp;PAGEN_2=2" // Do we need that?
        if (substr($resultUri, 0, 2) == '//') {
            $resultUri = $parsedSiteHost->getScheme() . ':' . $resultUri;
        }
        try {
            /** @var $parsedResult \GuzzleHttp\Psr7\Uri */
            $parsedResult = new Uri($resultUri);
        } catch (\Exception $e) {
            return null;
        }

        //
        if (empty($parsedResult->getHost())) {// Uri is relative
            if (!empty($parsedResult->getScheme())) {
                if ($parsedResult->getScheme() != 'http' && $parsedResult->getScheme() != 'https') {// We dont whant to download links like "mailto:"
                    return null;
                }
            }
            if (substr($parsedResult->getPath(), 0, 1) === '/') { // Path from root
                $parsedResult = Uri::resolve($parsedSiteHost, $parsedResult);
            } else {// Path NOT from root
                if (!empty($siteHeadBase) && ($parsedSiteHeadBase instanceof Uri) && !empty($parsedSiteHeadBase->getHost())) {// Apply <BASE>
                    $parsedResult = $parsedResult->withPath($parsedSiteHeadBase->getPath() . '/' . $parsedResult->getPath());
                    $parsedResult = Uri::resolve($parsedSiteHeadBase, $parsedResult);
                } else {// No <BASE> found
                    $parsedResult = Uri::resolve($parsedSiteHost, $parsedResult);
                }
            }
        }

        // Only internal links allowed
        if ($parsedSiteHost->getHost() != $parsedResult->getHost()) {
            return null;
        }

        // Reduce multiple slashes "//" in path // this happend when we add glue '/' slashes.
        if (!empty($parsedResult->getPath())) {
            $parsedResult = $parsedResult->withPath(preg_replace('#/+#', '/', $parsedResult->getPath()));
        }

        // Do not crawl links to files
        $pathinfo = pathinfo($parsedResult->getPath());
        if (!empty($pathinfo['extension'])
            && in_array($pathinfo['extension'], [
                'tiff', 'tif', 'jpeg', 'jpg', 'png', 'bmp', 'gif',
                'ico', 'css', 'js',
                'swf', 'flv', 'mpeg', 'mpg', 'mp4', 'avi',
                'xlsx', 'docx', 'doc', 'xls', 'pdf', 'zip', 'rar', 'tar', 'exe', 'gz',
            ])
        ) {
            return null;
        }

        // Special replacement // Do we need this ?? // Its from remoteBrowser: remoteBrowser replace "a.com?id=1" -> "a.com/?id=1" and "a.com#quote-carousel" -> "a.com/#quote-carousel"
        if (empty($parsedResult->getPath())) {
            if (!empty($parsedResult->getQuery())) { // "a.com?id=1" -> "a.com/?id=1"
                $parsedResult = $parsedResult->withPath('/');
            } elseif (!empty($parsedResult->getFragment())) { // "a.com#quote-carousel" -> "a.com/#quote-carousel"
                $parsedResult = $parsedResult->withPath('/');
            }
        }

        return (string)$parsedResult;
    }

    /**
     * @param SiteDownloadOptionsDTO $options
     * @throws \InvalidArgumentException
     */
    protected function validateSiteDownloadOptionsDTO(SiteDownloadOptionsDTO $options)
    {
        $errors = $this->validator->validate($options);
        if (count($errors) > 0) {
            throw new \InvalidArgumentException((string)$errors);
        }
    }
}