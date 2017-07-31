<?php

namespace Tests\SiteAnalyzerBundle\Utils;

use PHPUnit\Framework\TestCase;
use SiteAnalyzerBundle\Utils\UriUtil;

class UriUtilTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testGetHostFromUriWithoutWww()
    {
        $this->assertSame('example.com', UriUtil::getHostFromUriWithoutWww('https://www.example.com'));
        $this->assertSame('example.com', UriUtil::getHostFromUriWithoutWww('www.example.com'));
    }

    /**
     * @dataProvider getUnprocessedUrls
     */
    public function testGetRelativePathFromUri($unprocessedUrl, $processedUrl)
    {
        $this->assertSame($processedUrl, UriUtil::getRelativePathFromUri($unprocessedUrl));
    }

    public function getUnprocessedUrls()
    {
        $hostwww = 'http://www.example.com';

        //Format:    unprocessed url | null,    processed url | null
        return [
            // Root url
            __LINE__ => [$hostwww, '/'],

            // Same url
            __LINE__ => [$hostwww . '/', '/'],

            // Sub url
            __LINE__ => [$hostwww . '/en', '/en'],
            __LINE__ => [$hostwww . '/en/', '/en/'],
            __LINE__ => [$hostwww . '/en/blog?id=1', '/en/blog?id=1'],
            __LINE__ => [$hostwww . '/map.html', '/map.html'],
            __LINE__ => [$hostwww . '?id=1', '/?id=1'],
            __LINE__ => [$hostwww . '/app_dev.php/init.jpg', '/app_dev.php/init.jpg'],
            __LINE__ => [$hostwww . '/#quote-carousel', '/#quote-carousel'],
            __LINE__ => [$hostwww . '#quote-carousel', '/#quote-carousel'],
            __LINE__ => [$hostwww . '/?id=1#quote-carousel', '/?id=1#quote-carousel'],
            __LINE__ => [$hostwww . '#', '/'],
            __LINE__ => [$hostwww . '/#', '/'],
        ];
    }
}