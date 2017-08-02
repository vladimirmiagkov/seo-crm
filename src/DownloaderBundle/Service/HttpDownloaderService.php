<?php
declare(strict_types=1);

namespace DownloaderBundle\Service;

use GuzzleHttp\Psr7;
use GuzzleHttp\Client as GuzzleHttpClient;

/**
 * Just a wrapper for Guzzle, with custom config.
 */
class HttpDownloaderService implements DownloaderInterface
{
    /**
     * Max allowed http redirects when download resource.
     */
    const MAXIMUM_ALLOWED_REDIRECTS = 10;

    /** @var array */
    private $config = [];

    /** @var GuzzleHttpClient */
    private $client;

    public function __construct(array $config = [])
    {
        $this->config = $this->configureDefaults($config);
        $this->client = new GuzzleHttpClient($config);
    }

    /**
     * @param string $url
     * @param string $method
     * @param array  $options
     *
     * @return array
     */
    public function request(string $url, string $method = 'GET', array $options = []): array
    {
        $response = $this->client->request($method, $url, $options);

        // TODO: replace with some `response` object...
        return [
            'body'    => (string)$response->getContent(),
            'status'  => (int)$response->getStatus(),
            'headers' => (array)$response->getHeaders(),
        ];
    }

    /**
     * @param array $config
     * @return array
     */
    private function configureDefaults(array $config): array
    {
        $defaults = [
            'timeout'         => 60,
            'connect_timeout' => 60,
            'decode_content'  => true, //Specify whether or not Content-Encoding responses (gzip, deflate, etc.) are automatically decoded. //http://docs.guzzlephp.org/en/latest/request-options.html#decode-content
            //'verify'          => true, //Describes the SSL certificate verification behavior of a request. //http://docs.guzzlephp.org/en/latest/request-options.html#verify
            'cookies'         => false,
            'headers'         => [
                'User-Agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
            ],
            'allow_redirects' => [ //http://docs.guzzlephp.org/en/latest/request-options.html#allow-redirects
                'max'             => self::MAXIMUM_ALLOWED_REDIRECTS, //max: (int, default=5) maximum number of allowed redirects.
                'protocols'       => ['http', 'https'], //Specified which protocols are allowed for redirect requests.
                'strict'          => false, // Set to true to use strict redirects. Strict RFC compliant redirects mean that POST redirect requests are sent as POST requests vs. doing what most browsers do which is redirect POST requests with GET requests.
                'referer'         => false, // referer: (bool, default=false) Set to true to enable adding the Referer header when redirecting.
                'track_redirects' => true, // When set to true, each redirected URI encountered will be tracked in the X-Guzzle-Redirect-History header in the order in which the redirects were encountered.
                //'on_redirect' => function(RequestInterface $request,ResponseInterface $response,UriInterface $uri) {echo 'Redirecting! ' . $request->getUri() . ' to ' . $uri . "\n";}; //on_redirect: (callable) PHP callable that is invoked when a redirect is encountered. The callable is invoked with the original request and the redirect response that was received. Any return value from the on_redirect function is ignored.
            ],
            'http_errors'     => false,//Set to false to disable throwing exceptions on an HTTP protocol errors (i.e., 4xx and 5xx responses). Exceptions are thrown by default when HTTP protocol errors are encountered.
        ];

        // Use the standard Linux HTTP_PROXY and HTTPS_PROXY if set.
        // We can only trust the HTTP_PROXY environment variable in a CLI
        // process due to the fact that PHP has no reliable mechanism to
        // get environment variables that start with "HTTP_".
        if (php_sapi_name() == 'cli' && getenv('HTTP_PROXY')) {
            $defaults['proxy']['http'] = getenv('HTTP_PROXY');
        }
        if ($proxy = getenv('HTTPS_PROXY')) {
            $defaults['proxy']['https'] = $proxy;
        }
        if ($noProxy = getenv('NO_PROXY')) {
            $cleanedNoProxy = str_replace(' ', '', $noProxy);
            $defaults['proxy']['no'] = explode(',', $cleanedNoProxy);
        }

        $this->config = $config + $defaults;

        //if (!empty($config['cookies']) && $config['cookies'] === true) {
        //    $this->config['cookies'] = new CookieJar();
        //}

        return $config;
    }
}