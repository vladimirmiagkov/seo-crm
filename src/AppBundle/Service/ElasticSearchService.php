<?php
declare(strict_types=1);

namespace AppBundle\Service;

use Elasticsearch\ClientBuilder;

class ElasticSearchService
{
    public $client;

    public function __construct($hosts)
    {
        //$hosts = [
        //    // This is effectively equal to: "https://username:password!#$?*abc@foo.com:9200/"
        //    [
        //        'host' => '192.168.99.100',
        //        'port' => '9200',
        //        'scheme' => 'https',
        //        'user' => 'username',
        //        'pass' => 'password!#$?*abc'
        //    ],
        //    // This is equal to "http://localhost:9200/"
        //    [
        //        'host' => 'localhost',    // Only host is required
        //    ]
        //];
        $this->client = ClientBuilder::create()
            ->setHosts($hosts)
            ->build();
    }
}