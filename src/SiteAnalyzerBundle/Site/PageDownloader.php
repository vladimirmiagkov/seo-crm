<?php
declare(strict_types=1);

namespace SiteAnalyzerBundle\Site;

use SiteAnalyzerBundle\Page\PageAnalyzer;
use SiteAnalyzerBundle\Resource\HtmlPage;
use SiteAnalyzerBundle\Site\DTO\SiteDownloadOptionsDTO;
use DownloaderBundle\Service\RemoteBrowserDownloaderService;
use Goutte\Client as GoutteClient;
use GuzzleHttp\Client as GuzzleHttpClient;
use Predis\Client as RedisClient;

class PageDownloader
{
    const MAXIMUM_ALLOWED_REDIRECTS = 10;

    /**
     * @var RemoteBrowserDownloaderService
     */
    protected $remoteBrowser;
    /**
     * @var PageAnalyzer
     */
    protected $pageAnalyzer;
    /**
     * @var RedisClient
     */
    protected $redis;

    public function __construct(RemoteBrowserDownloaderService $remoteBrowser, PageAnalyzer $pageAnalyzer, RedisClient $redis)
    {
        $this->remoteBrowser = $remoteBrowser;
        $this->pageAnalyzer = $pageAnalyzer;
        $this->redis = $redis;
    }

    /**
     * Download page.
     * We need to download same uri 2 times(if configured):
     *   1) through Guzzle (grab headers and HTTP status)
     *   2) through remoteBrowser (there we CAN'T receive headers and HTTP status;
     *      but we can grab javascript-processed outgoing links)
     *
     * @param string                 $uri
     * @param SiteDownloadOptionsDTO $options
     * @return HtmlPage
     * @throws \ErrorException
     */
    public function downloadPage(string $uri, SiteDownloadOptionsDTO $options): HtmlPage
    {
        $goutteResponse = unserialize((string)$this->redis->get('rsSiteDwnTmp:' . $uri));//todo: remove DEBUG
        if (empty($goutteResponse)) {//todo: remove DEBUG
            $goutteClient = (new GoutteClient())->setClient($this->initGuzzle($options));
            $goutteClient->request('GET', $uri);
            $goutteResponse = $goutteClient->getInternalResponse();
            $this->redis->set('rsSiteDwnTmp:' . $uri, serialize($goutteResponse));//todo: remove DEBUG
        }//todo: remove DEBUG

        $htmlPage = (new HtmlPage)
            ->setAndParseHtml((string)$goutteResponse->getContent(), $uri)
            ->setResponseCode($goutteResponse->getStatus())
            ->setHeaders((array)$goutteResponse->getHeaders());

        if (
            $goutteResponse->getStatus() >= 200 && $goutteResponse->getStatus() < 400
            && $options->useRemoteBrowserForCrawling // Download page through remoteBrowser if needed
        ) {
            $this->remoteBrowser->loadUri($uri);
            if (!empty($links = $this->remoteBrowser->getPageLinks())) { //TODO: replace with ->setAndParseHtml, for speed optimization 
                $htmlPage->clearLinks();
                foreach ($links as $k => $link) {
                    $htmlPage->addLink($htmlPage->createLinkFromArray($link, $uri));
                }
            }
        }

        return $htmlPage;
    }

    /**
     * @param SiteDownloadOptionsDTO $options
     * @return GuzzleHttpClient
     */
    protected function initGuzzle(SiteDownloadOptionsDTO $options): GuzzleHttpClient
    {
        $guzzleClient = new GuzzleHttpClient([
            'timeout'         => $options->curlTimeout,
            'connect_timeout' => $options->curlConnectTimeout,
            'allow_redirects' => [ //http://docs.guzzlephp.org/en/latest/request-options.html#allow-redirects
                'max'             => self::MAXIMUM_ALLOWED_REDIRECTS, //max: (int, default=5) maximum number of allowed redirects.
                'protocols'       => ['http', 'https'], //Specified which protocols are allowed for redirect requests.
                'strict'          => false, // Set to true to use strict redirects. Strict RFC compliant redirects mean that POST redirect requests are sent as POST requests vs. doing what most browsers do which is redirect POST requests with GET requests.
                'referer'         => false, // referer: (bool, default=false) Set to true to enable adding the Referer header when redirecting.
                'track_redirects' => true, // When set to true, each redirected URI encountered will be tracked in the X-Guzzle-Redirect-History header in the order in which the redirects were encountered.
                //'on_redirect' => function(RequestInterface $request,ResponseInterface $response,UriInterface $uri) {echo 'Redirecting! ' . $request->getUri() . ' to ' . $uri . "\n";}; //on_redirect: (callable) PHP callable that is invoked when a redirect is encountered. The callable is invoked with the original request and the redirect response that was received. Any return value from the on_redirect function is ignored.
            ],
            //'decode_content' => false,//Specify whether or not Content-Encoding responses (gzip, deflate, etc.) are automatically decoded. //http://docs.guzzlephp.org/en/latest/request-options.html#decode-content
            'headers'         => [
                'User-Agent' => 'User-Agent: Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',// todo: move to settings
            ],
            'http_errors'     => false,//Set to false to disable throwing exceptions on an HTTP protocol errors (i.e., 4xx and 5xx responses). Exceptions are thrown by default when HTTP protocol errors are encountered.
            //'verify' => false, //Describes the SSL certificate verification behavior of a request. //http://docs.guzzlephp.org/en/latest/request-options.html#verify
        ]);
        return $guzzleClient;
    }
}