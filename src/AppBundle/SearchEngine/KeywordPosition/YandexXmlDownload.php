<?php
declare(strict_types=1);

namespace AppBundle\SearchEngine\KeywordPosition;

use DownloaderBundle\Service\DownloaderInterface;

/**
 * Helper class for download Yandex xml data.
 */
class YandexXmlDownload
{
    const SEARCH_ENGINE_REQUEST_URL = 'https://yandex.ru/search/xml?';

    /**
     * @var DownloaderInterface
     */
    private $downloader;

    public function __construct(DownloaderInterface $downloader)
    {
        $this->downloader = $downloader;
    }

    /**
     * Download url from yandex xml.
     *
     * @param string $searchEngineParameters Parameters for search engine
     * @param string $goalKeyword            Goal requested keyword
     * @param int    $requestPageNumber      Page number in search engine
     * @param int    $requestSitesPerPage    How much sites we request from 1 search engine page
     *
     * @return array
     */
    public function download(string $searchEngineParameters, string $goalKeyword, $requestPageNumber, $requestSitesPerPage)
    {
        $searchEngineRequestUri = self::SEARCH_ENGINE_REQUEST_URL . $searchEngineParameters;

        $requestContent = '<?xml version="1.0" encoding="UTF-8"?>' . "\r\n"
            . '<request>' . "\r\n"
            . '<query>' . \htmlspecialchars($goalKeyword, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</query>' . "\r\n"
            . '<page>' . $requestPageNumber . '</page>' . "\r\n"
            . '<groupings>' . "\r\n"
            . '<groupby attr="d" mode="deep" groups-on-page="' . $requestSitesPerPage . '"  docs-in-group="1" /> ' . "\r\n"
            . '</groupings>' . "\r\n"
            . '</request>' . "\r\n";

        //$requestOptions = [
        //    'http' => [
        //        'method'  => 'POST',
        //        'header'  => 'Content-type: application/xml' . "\r\n" . 'Content-length: ' . \strlen($requestContent),
        //        'content' => $requestContent,
        //    ],
        //];
        //$requestContext = \stream_context_create($requestOptions);

        //$response = \file_get_contents(\dirname(__FILE__) . '/../../../../spec/AppBundle/SearchEngine/KeywordPosition/good_search_engine_response.xml'); // DEBUG
        //$response = \file_get_contents($searchEngineRequestUri, true, $requestContext);

        /** @var \GuzzleHttp\Psr7\Response $response */
        $response = $this->downloader->request($searchEngineRequestUri, 'POST', [
            'body'    => $requestContent,
            'headers' => [
                'Content-type'   => 'application/xml',
                'Content-length' => \strlen($requestContent),
            ],
        ]);

        return [(string)$requestContent, (string)$response->getBody()];
    }
}
