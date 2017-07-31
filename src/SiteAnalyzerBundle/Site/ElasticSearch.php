<?php
declare(strict_types=1);

namespace SiteAnalyzerBundle\Site;

use AppBundle\Service\ElasticSearchService;
use SiteAnalyzerBundle\Resource\HtmlPage;

class ElasticSearch
{
    const SITE_DWN_PREFIX = 'sitedwn_';

    /**
     * @var \Elasticsearch\Client
     */
    protected $client;

    public function __construct(ElasticSearchService $elasticSearchService)
    {
        $this->client = $elasticSearchService->client;
    }

    public function createSiteIndex(string $siteStampName)
    {
        // todo: do we need "local_mapping_version": "0.0.1" ??
        $params = [
            'index' => self::SITE_DWN_PREFIX . $siteStampName,
            'body'  => [
                'settings' => [
                    'index.mapping.total_fields.limit'  => 1000, // The maximum number of fields in an index. The default value is 1000.
                    'index.mapping.depth.limit'         => 20, // The maximum depth for a field, which is measured as the number of inner objects. The default is 20. 
                    'index.mapping.nested_fields.limit' => 10000, // The maximum number of nested fields in an index, defaults to 50 ('type' => 'nested').
                    'number_of_shards'                  => 1,
                    'number_of_replicas'                => 0,
                    'analysis'                          => [
                        'filter'   => [
                            'english_stop'               => [
                                'type'      => 'stop',
                                'stopwords' => '_english_',
                            ],
                            //'english_keywords' => [
                            //    'type' => 'keyword_marker',
                            //    'keywords' => []
                            //],
                            'english_stemmer'            => [
                                'type'     => 'stemmer',
                                'language' => 'english',
                            ],
                            'english_possessive_stemmer' => [
                                'type'     => 'stemmer',
                                'language' => 'possessive_english',
                            ],
                            'russian_stop'               => [
                                'type'      => 'stop',
                                'stopwords' => '_russian_',
                            ],
                            //'russian_keywords' => [
                            //    'type' => 'keyword_marker',
                            //    'keywords' => []
                            //],
                            'russian_stemmer'            => [
                                'type'     => 'stemmer',
                                'language' => 'russian',
                            ],
                        ],
                        //'char_filter' => [
                        //    'pre_negs' => [
                        //        'type' => 'pattern_replace',
                        //        'pattern' => '(\\w+)\\s+((?i:never|no|nothing|nowhere|noone|none|not|havent|hasnt|hadnt|cant|couldnt|shouldnt|wont|wouldnt|dont|doesnt|didnt|isnt|arent|aint))\\b',
                        //        'replacement' => '~$1 $2'
                        //    ],
                        //    'post_negs' => [
                        //        'type' => 'pattern_replace',
                        //        'pattern' => '\\b((?i:never|no|nothing|nowhere|noone|none|not|havent|hasnt|hadnt|cant|couldnt|shouldnt|wont|wouldnt|dont|doesnt|didnt|isnt|arent|aint))\\s+(\\w+)',
                        //        'replacement' => '$1 ~$2'
                        //    ]
                        //],
                        'analyzer' => [
                            'ru_en' => [
                                'tokenizer' => 'standard',
                                'filter'    => [
                                    'lowercase',
                                    'russian_stop',
                                    //'russian_keywords',
                                    'russian_stemmer',
                                    'english_possessive_stemmer',
                                    'english_stop',
                                    //'english_keywords',
                                    'english_stemmer',
                                ],
                            ],
                        ],
                    ],
                ],
                'mappings' => [
                    'page' => [
                        'properties' => [
                            'url'         => [
                                'type' => 'keyword',
                            ],
                            'charset'     => [
                                'type' => 'keyword',
                            ],
                            'htmlLang'    => [
                                'type' => 'keyword',
                            ],
                            'baseUri'     => [
                                'type' => 'keyword',
                            ],
                            'title'       => [
                                'type'     => 'text',
                                'analyzer' => 'ru_en',
                                //'fielddata' => true,
                                //'term_vector' => 'yes', //info about indexed values
                                //'copy_to' => 'combined',
                                //'index' => 'not_analyzed', // Should the field be searchable? Accepts true (default) or false.
                                'fields'   => [ //additional fields
                                    'raw' => [ //additionally save as not modified value
                                        'type' => 'keyword',
                                    ],
                                ],
                            ],
                            'titleForSeo' => [
                                'type'     => 'text',
                                'analyzer' => 'ru_en',
                            ],
                            'keywords'    => [
                                'type'     => 'text',
                                'analyzer' => 'ru_en',
                            ],
                            'description' => [
                                'type'     => 'text',
                                'analyzer' => 'ru_en',
                            ],
                            'canonical'   => [
                                'type' => 'keyword',
                            ],
                            'favicon'     => [
                                'type' => 'keyword',
                            ],
                            'shortlink'   => [
                                'type' => 'keyword',
                            ],

                            'bodyText'     => [
                                'type'     => 'text',
                                'analyzer' => 'ru_en',
                            ],
                            'bodyChecksum' => [
                                'type'  => 'keyword',
                                'index' => 'not_analyzed',
                            ],

                            'links' => [
                                'type'       => 'nested', // sub arrays
                                'properties' => [
                                    'sortOrder'              => [ // links sort order on page
                                        'type' => 'integer',
                                    ],
                                    'url'                    => [
                                        'type' => 'keyword',
                                    ],
                                    'hrefOriginal'           => [
                                        'type' => 'keyword',
                                    ],
                                    'text'                   => [
                                        'type'     => 'text',
                                        'analyzer' => 'ru_en',
                                    ],
                                    'title'                  => [
                                        'type'     => 'text',
                                        'analyzer' => 'ru_en',
                                    ],
                                    'type'                   => [
                                        'type' => 'integer',
                                    ],
                                    'noFollow'               => [
                                        'type' => 'boolean',
                                    ],
                                    'urlExcludedByRobotsTxt' => [
                                        'type' => 'boolean',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $response = $this->client->indices()->create($params);
        return $response;
    }

    public function deleteSiteIndex(string $siteStampName)
    {
        $deleteParams = [
            'index' => self::SITE_DWN_PREFIX . $siteStampName,
        ];
        $response = $this->client->indices()->delete($deleteParams);
        return $response;
    }

    public function addPageToSite(string $siteStampName, HtmlPage $htmlPage)
    {
        $params = [
            'index' => self::SITE_DWN_PREFIX . $siteStampName,
            'type'  => 'page',
            'id'    => $htmlPage->getUrl(),
            'body'  => [
                'url'         => $htmlPage->getUrl(),
                'charset'     => $htmlPage->getCharset(),
                'htmlLang'    => $htmlPage->getHtmlLang(),
                'baseUri'     => $htmlPage->getBaseUri(),
                'title'       => $htmlPage->getTitle(),
                'titleForSeo' => $htmlPage->getTitleForSeo(),
                'keywords'    => $htmlPage->getKeywords(),
                'description' => $htmlPage->getDescription(),
                'canonical'   => $htmlPage->getCanonical(),
                'favicon'     => $htmlPage->getFavicon(),
                'shortlink'   => $htmlPage->getShortlink(),

                'bodyText'     => $htmlPage->getBodyText(),
                'bodyChecksum' => $htmlPage->getBodyChecksum(),
            ],
        ];

        // Links
        foreach ($htmlPage->getLinks() as $linkSortOrder => $link) {
            $tmp = [];
            $tmp['sortOrder'] = $linkSortOrder;
            $tmp['url'] = $link->getUrl();
            $tmp['hrefOriginal'] = $link->getHrefOriginal();
            $tmp['text'] = $link->getText();
            $tmp['title'] = $link->getTitle();
            $tmp['type'] = $link->getType();
            $tmp['noFollow'] = $link->isNoFollow();
            $tmp['urlExcludedByRobotsTxt'] = $link->isUrlExcludedByRobotsTxt();
            $params['body']['links'][] = $tmp;
        }

        $response = $this->client->index($params);

        return $response;
    }

    /**
     * Search for identical elements. Like identical 'title' in all pages.
     *
     * @param string $siteStampName
     * @param string $element
     * @return array
     */
    public function findPagesByIdenticalElement(string $siteStampName, string $element)
    {
        $params = [
            'index' => self::SITE_DWN_PREFIX . $siteStampName,
            'type'  => 'page',
            'body'  => [
                'size' => 0,
                'aggs' => [
                    'identical_elements' => [
                        'terms' => [
                            'field'         => $element . '.raw',
                            'min_doc_count' => 2,
                        ],
                        'aggs'  => [
                            'duplicateDocuments' => [
                                'top_hits' => [
                                    '_source' => [
                                        'includes' => [
                                            $element,
                                        ],
                                    ],
                                    'size'    => 1000, // Limit per aggregated group
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $response = $this->client->search($params);

        return $response;
    }

    //public function debugShowAllRecords()
    //{
    //$params = [
    //    'index' => 'my_index',//db
    //    'type' => 'my_type',//table
    //    'id' => 'my_id',
    //    //'body' => ['testField' => 'abc'],
    //    //'client' => [ 'ignore' => [400, 404] ],
    //    //'client' => [
    //    //    'verbose' => true
    //    //],
    //];
    ////$response = $this->client->index($params);
    //$response = $this->client->get($params);
    //echo '<pre>', htmlspecialchars(print_r($response, true)), '</pre>';

    //sleep(3);
    //$params = [
    //    'index' => 'site_dwn_' . $siteId,
    //    'type' => 'page',
    //    'body' => [
    //        'query' => [
    //            'match' => [
    //                'title' => 'стол'
    //            ]
    //        ]
    //    ]
    //];
    //$response = $this->client->search($params);
    //}
}