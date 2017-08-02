<?php
declare(strict_types=1);

namespace AppBundle\SearchEngine\KeywordPosition;

use AppBundle\Entity\KeywordCompetitor;
use AppBundle\SearchEngine\SerpResult;
use SiteAnalyzerBundle\Utils\UriUtil;

/**
 * Get keywords positions from search engine: Yandex.
 */
class YandexXml
{
    /**
     * @var YandexXmlDownload
     */
    protected $yandexXmlDownload;
    /**
     * Search engine credentials: User key, passwords...
     *
     * @var string
     */
    protected $searchEngineCredentials;

    public function __construct(
        YandexXmlDownload $yandexXmlDownload,
        string $searchEngineCredentials
    )
    {
        $this->yandexXmlDownload = $yandexXmlDownload;
        $this->searchEngineCredentials = $searchEngineCredentials;
    }

    /**
     * Get SERP from search engine.
     * Looking for our site position in search engine results.
     * More info: https://tech.yandex.ru/xml/doc/dg/reference/faq-docpage/
     *            https://tech.yandex.ru/xml/doc/dg/concepts/response_response-el-docpage/
     *            https://yandex.ru/support/search/query-language/qlanguage.html
     *
     * @param string $keyword                Like "buy stuff"
     * @param string $goalSiteDomain         Like "https://www.example.com"
     * @param string $fromPlace              Region (or city) from which to search.
     *                                       http://search.yaca.yandex.ru/geo.c2n //&lr=47
     * @param int    $maxRequestSites        Maximum (total) search sites from the search engine.
     * @param int    $requestSitesPerPage    Instruction for search engine. How many sites per page we requesting.
     * @param int    $timeoutBetweenRequests Timeout between requesting page from search engine.
     *                                       WARNING: If no timeout - search engine may disconnect with error: 503
     *                                       (bruteforce).
     * @param int    $startFromPage          From which page we need to start requesting search engine.
     *                                       This optimization is done to reduce the number of requests to search
     *                                       engine. We can assume this number from the last keyword position.
     *
     * @return SerpResult
     */
    public function grabSerp(
        string $keyword,
        string $goalSiteDomain,
        string $fromPlace = '',
        int $maxRequestSites = 100,
        int $requestSitesPerPage = 100,
        int $timeoutBetweenRequests = 3,
        int $startFromPage = 0
    ): SerpResult
    {
        $serpResult = new SerpResult();

        $searchEngineRequestParameters = $this->searchEngineCredentials;
        if (empty($searchEngineRequestParameters)) {
            $serpResult->setStatus(SerpResult::STATUS_SEARCH_ENGINE_INVALID_ARGUMENT);
            $serpResult->addError('search_engine_credentials_yandex_xml from global params can not be empty.');
            return $serpResult;
        }

        if (empty($keyword)) {
            $serpResult->setStatus(SerpResult::STATUS_SEARCH_ENGINE_INVALID_ARGUMENT);
            $serpResult->addError('$keyword can not be empty.');
            return $serpResult;
        }

        if (empty($goalSiteDomain)) {
            $serpResult->setStatus(SerpResult::STATUS_SEARCH_ENGINE_INVALID_ARGUMENT);
            $serpResult->addError('$goalSiteDomain can not be empty.');
            return $serpResult;
        }
        $goalSiteDomainWithoutWww = UriUtil::getHostFromUriWithoutWww($goalSiteDomain);
        if (empty($goalSiteDomainWithoutWww)) {
            $serpResult->setStatus(SerpResult::STATUS_SEARCH_ENGINE_INVALID_ARGUMENT);
            $serpResult->addError('$goalSiteDomain can not parse host.');
            return $serpResult;
        }

        if ($fromPlace !== '') {
            $searchEngineRequestParameters .= '&lr=' . $fromPlace;
        }

        // Find max requested page in search engine.
        $maxRequestedPage = (int)\floor($maxRequestSites / $requestSitesPerPage);
        if ($maxRequestedPage < 1) {
            $maxRequestedPage = 1;
        }

        $totalSitesCounter = $startFromPage * $requestSitesPerPage;
        $stopRequestingSearchEngineFlag = false;
        for ($currentRequestPage = $startFromPage; $currentRequestPage < $maxRequestedPage; $currentRequestPage++) {
            if ($currentRequestPage > 0) {
                \sleep($timeoutBetweenRequests);
            }

            // Download search engine url.
            list($request, $response) = $this->yandexXmlDownload->download(
                $searchEngineRequestParameters,
                $keyword,
                $currentRequestPage,
                $requestSitesPerPage
            );

            $serpResult->addRequest($request);
            $serpResult->addResponse($response);
            if (!$response) {
                $serpResult->setStatus(SerpResult::STATUS_SEARCH_ENGINE_NOT_AVAILABLE);
                $serpResult->addError('Search engine Yandex Xml unavailable.');
                return $serpResult;
            }

            // Process search engine results. 
            $responseXmlDoc = new \SimpleXMLElement($response);
            $responseXmlError = $responseXmlDoc->response->error;
            if ($responseXmlError[0]) {
                $serpResult->setStatus(SerpResult::STATUS_SEARCH_ENGINE_ERROR);
                $serpResult->addError((string)$responseXmlError[0]); // May be: "Error in the original query"
                return $serpResult;
            }

            // Parse search engine results.
            $sites = $responseXmlDoc->xpath('response/results/grouping/group');
            foreach ($sites as $site) {
                $keywordCompetitor = $this->parseSiteFromSerp($site, $totalSitesCounter);
                $serpResult->setSiteByIndex($totalSitesCounter, $keywordCompetitor);

                // Try to find our goal site in SERP.
                if ($goalSiteDomainWithoutWww === UriUtil::getHostFromUriWithoutWww((string)$keywordCompetitor->getUrl())) {
                    // Goal site was found in SERP.
                    $stopRequestingSearchEngineFlag = true;

                    // We save only first encountered site position.
                    if (null === $serpResult->getGoalSiteIndex()) {
                        $serpResult->setGoalSiteIndex($totalSitesCounter);
                    }
                }

                $totalSitesCounter++;
            }

            if ($stopRequestingSearchEngineFlag) {
                // We found goal site in SERP and no need to collect sites furthermore. 
                break;
            } elseif ($startFromPage > 0) {
                // If a request was made to grab sites from a particular page (for example 3),
                //   and there no our goal site, then we start to search again, but from the 0 page.
                // TODO: investigate situation.
                // TODO: do not request already requested pages from search engine. 
                $serpResult = $this->grabSerp(
                    $keyword,
                    $goalSiteDomain,
                    $fromPlace,
                    $maxRequestSites,
                    $requestSitesPerPage,
                    $timeoutBetweenRequests,
                    0
                );
                break;
            }
        }

        return $serpResult;
    }

    /**
     * Parse one site from SERP.
     *
     * @param \SimpleXMLElement $site
     * @param int               $sitePosition
     *
     * @return KeywordCompetitor
     */
    protected function parseSiteFromSerp(\SimpleXMLElement $site, int $sitePosition): KeywordCompetitor
    {
        $tmp = \json_decode(\json_encode($site), true);
        $keywordCompetitor = new KeywordCompetitor;

        // Set sequential site number in SERP
        $keywordCompetitor->setPosition($sitePosition + 1);

        // YANDEX: Адрес найденного документа.
        $keywordCompetitor->setUrl($tmp['doc']['url'] ?? null);

        // YANDEX: Домен, на котором расположен найденный документ.
        $keywordCompetitor->setDomain($tmp['doc']['domain'] ?? null);

        // YANDEX: Дата и время изменения документа в формате:<год><месяц><день>Т<час><минута><секунда>
        if (isset($tmp['doc']['modtime'])) {
            $keywordCompetitor->setDocumentModificationTime(\strtotime($tmp['doc']['modtime']));
        }

        // YANDEX: Кодировка найденного документа.
        $keywordCompetitor->setDocumentCharset($tmp['doc']['charset'] ?? null);

        // YANDEX: Тип документа в соответствии с RFC2046.
        //$xml->{"mime-type"}
        $keywordCompetitor->setDocumentMimeType($tmp['doc']['mime-type'] ?? null);

        // YANDEX: Заголовок найденного документа.
        if (isset($tmp['doc']['title'])) {
            $keywordCompetitor->setDocumentTitle(\str_replace(
                ['<title>', '</title>', '<hlword>', '</hlword>'],
                ['', '', '<' . KeywordCompetitor::HIGHLIGHT_WORD_TAG . '>', '</' . KeywordCompetitor::HIGHLIGHT_WORD_TAG . '>'],
                (string)$site->doc->title->asXML()));
        }

        // YANDEX: Опциональный. Аннотация документа. Для формирования используется HTML-тег meta, содержащий атрибут name со значением «description»
        if (isset($tmp['doc']['headline'])) {
            $keywordCompetitor->setDocumentHeadline(\str_replace(
                ['<headline>', '</headline>', '<hlword>', '</hlword>'],
                ['', '', '<' . KeywordCompetitor::HIGHLIGHT_WORD_TAG . '>', '</' . KeywordCompetitor::HIGHLIGHT_WORD_TAG . '>'],
                (string)$site->doc->headline->asXML()));
        }

        // YANDEX: Опциональный. Язык документа.
        $keywordCompetitor->setDocumentLang($tmp['doc']['properties']['lang'] ?? null);

        // YANDEX: Тип пассажа. Возможные значения:
        //     «0» — стандартный пассаж (сформирован из текста документа);
        //     «1» — пассаж на основе текста ссылки. Используется, если документ найден по ссылке.
        $keywordCompetitor->setDocumentPassagesType($tmp['doc']['properties']['_PassagesType'] ?? null);

        // YANDEX: Пассаж с аннотацией к документу. Available values: 0 - 5
        if (isset($tmp['doc']['passages'])) {
            foreach ($site->doc->passages as $passages) {
                if (\is_string($passages)) {
                    $keywordCompetitor->addDocumentPassage($passages);
                } else {
                    foreach ($passages as $passage) {
                        $keywordCompetitor->addDocumentPassage(\str_replace(
                            ['<passage>', '</passage>', '<hlword>', '</hlword>'],
                            ['', '', '<' . KeywordCompetitor::HIGHLIGHT_WORD_TAG . '>', '</' . KeywordCompetitor::HIGHLIGHT_WORD_TAG . '>'],
                            (string)$passage->asXML()));
                    }
                }
            }
        }

        // YANDEX: Адрес сохраненной копии документа.
        $keywordCompetitor->setSavedCopyUrl($tmp['doc']['saved-copy-url'] ?? null);

        return $keywordCompetitor;
    }
}
