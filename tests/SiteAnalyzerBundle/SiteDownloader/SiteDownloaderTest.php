<?php

namespace Tests\SiteAnalyzerBundle\SiteDownloader;

use PHPUnit\Framework\TestCase;
use SiteAnalyzerBundle\Site\SiteDownloader;

class SiteDownloaderTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testBuildSiteTreeByDepthLevel()
    {
        // Real url was replaced with "url number", for better clarity of test.
        // Site structure:
        //  url number      Info: depth level       Evaluated: siteTreeOrder
        //  (as downloaded)
        //  0               depth level: 0          siteTreeOrder: 0
        //  -/1             depth level: 1          siteTreeOrder: 1
        //  -|-/3           depth level: 2          siteTreeOrder: 2
        //  -|-/4           depth level: 2          siteTreeOrder: 3
        //  -|-|-/6         depth level: 3          siteTreeOrder: 4
        //  -|-|-|-/7       depth level: 4          siteTreeOrder: 5
        //  -/2             depth level: 1          siteTreeOrder: 6
        //  -|-/5           depth level: 2          siteTreeOrder: 7

        $originalArray = [
            // depth level: 0
            '0' => [
                'parent' => null,
            ],
            // depth level: 1
            '1' => [
                'parent' => '0',
            ],
            '2' => [
                'parent' => '0',
            ],
            // depth level: 2
            '3' => [
                'parent' => '1',
            ],
            '4' => [
                'parent' => '1',
            ],
            '5' => [
                'parent' => '2',
            ],
            // depth level: 3
            '6' => [
                'parent' => '4',
            ],
            // depth level: 4
            '7' => [
                'parent' => '6',
            ],
        ];
        $resultArray = [
            // depth level: 0
            '0' => [
                'parent'        => null,
                'siteTreeOrder' => 0,
            ],
            // depth level: 1
            '1' => [
                'parent'        => '0',
                'siteTreeOrder' => 1,
            ],
            '2' => [
                'parent'        => '0',
                'siteTreeOrder' => 6,
            ],
            // depth level: 2
            '3' => [
                'parent'        => '1',
                'siteTreeOrder' => 2,
            ],
            '4' => [
                'parent'        => '1',
                'siteTreeOrder' => 3,
            ],
            '5' => [
                'parent'        => '2',
                'siteTreeOrder' => 7,
            ],
            // depth level: 3
            '6' => [
                'parent'        => '4',
                'siteTreeOrder' => 4,
            ],
            // depth level: 4
            '7' => [
                'parent'        => '6',
                'siteTreeOrder' => 5,
            ],
        ];
        SiteDownloader::buildSiteTreeByDepthLevel($originalArray);
        $this->assertSame($resultArray, $originalArray);
    }

    /**
     * @dataProvider getUnprocessedUrls
     */
    public function testGetCrawlableUri($siteHost, $unprocessedUrl, $processedUrl, $siteHeadBase)
    {
        $this->assertSame($processedUrl, SiteDownloader::getCrawlableUri($siteHost, $unprocessedUrl, $siteHeadBase));
    }

    public function getUnprocessedUrls()
    {
        // www != non www  different domains!
        // http != https   different domains!
        $hostwww = 'http://www.a.com';
        $host = 'http://a.com';

        //Format:  site host,  unprocessed url,  processed url | null(bad url),  head base tag
        return [
            // Empty url
            __LINE__ => [$hostwww, '', null, null], //Bad url. Empty.
            __LINE__ => [$host, '', null, null], //Bad url. Empty.
            __LINE__ => [$hostwww, 'http://', null, null], //Bad url. Empty.

            // Same url
            __LINE__ => [$hostwww, $hostwww, $hostwww, null], //Good url. Do not convert.
            __LINE__ => [$host, $host, $host, null], //Good url. Do not convert.
            __LINE__ => [$hostwww, $hostwww . '/', $hostwww . '/', null], //Good url. Do not convert.
            __LINE__ => ['https://a.com', 'https://a.com', 'https://a.com', null], //Good url. Do not convert.

            // Sub url
            __LINE__ => [$hostwww, $hostwww . '/en', $hostwww . '/en', null], //Good url. Do not convert.
            __LINE__ => [$hostwww, $hostwww . '/en/', $hostwww . '/en/', null], //Good url. Do not convert.
            __LINE__ => [$hostwww, $hostwww . '/en/blog?id=1', $hostwww . '/en/blog?id=1', null], //Good url. Do not convert.
            __LINE__ => [$hostwww, $hostwww . '/map.html', $hostwww . '/map.html', null], //Good url. Do not convert.
            __LINE__ => [$hostwww, '?id=1', $hostwww . '/?id=1', null], //Good url. Convert: add host.

            // We need to crawl urls with fragments as a differing urls. See https://webmasters.googleblog.com/2015/10/deprecating-our-ajax-crawling-scheme.html
            __LINE__ => [$hostwww, $hostwww . '/#quote-carousel', $hostwww . '/#quote-carousel', null], //Good url. Do not convert.
            __LINE__ => [$hostwww, $hostwww . '/?id=1#quote-carousel', $hostwww . '/?id=1#quote-carousel', null], //Good url. Do not convert.
            __LINE__ => [$hostwww, '#quote-carousel', $hostwww . '/#quote-carousel', null], //Good url. Convert: add host.
            __LINE__ => [$hostwww, '/1/#/123/map.html', $hostwww . '/1/#/123/map.html', null], //Good url. Do not convert.
            __LINE__ => [$hostwww, $hostwww . '#', $hostwww . '', null], //Good url. Convert: remove last '#'.
            __LINE__ => [$hostwww, $hostwww . '/#', $hostwww . '/', null], //Good url. Convert: remove last '#'.

            // Add host
            __LINE__ => [$hostwww, '/en/blog2', $hostwww . '/en/blog2', null], //Good url. Convert: add host.
            __LINE__ => [$hostwww, '/en/blog2', $hostwww . '/en/blog2', ($hostwww . '/123')], //Good url. Convert: add host. DONT add <BASE>

            // Add <BASE>
            __LINE__ => [$hostwww, 'en/blog3', $hostwww . '/en/blog3', null], //Good url. Convert: add host.
            __LINE__ => [$hostwww, 'en/blog3', $hostwww . '/en/blog3', ''], //Good url. Convert: add host.
            __LINE__ => [$hostwww, 'en/blog3', $hostwww . '/en/blog3', '/'], //Good url. Convert: add host.
            __LINE__ => [$hostwww, 'en/blog3', $hostwww . '/123/en/blog3', $hostwww . '/123'], //Good url. Convert: add <BASE>.
            __LINE__ => [$hostwww, 'en/blog3', $hostwww . '/123/en/blog3', $hostwww . '/123/'], //Good url. Convert: add <BASE>.

            // Convert scheme
            __LINE__ => [$hostwww, '//www.a.com', $hostwww, null], //Good url. Convert: replace // with host scheme.

            // Little replacements
            __LINE__ => [$hostwww, '   ' . $hostwww . '/en ', $hostwww . '/en', null], //Good url. Convert: trim.
            __LINE__ => [$hostwww, mb_strtoupper($hostwww) . '/En', $hostwww . '/en', null], //Good url. Convert: to lower case.
            __LINE__ => [$hostwww, $hostwww . '/en?&amp;id=1', $hostwww . '/en?&id=1', null], //Good url. Convert: replace. //hm...

            // Not crawlable url
            __LINE__ => [$hostwww, $hostwww . '/app_dev.php/init.jpg', null, null], //Bad url. Not crawlable picture.
            __LINE__ => [$hostwww, $hostwww . '/app_dev.php/init.jpg?123', null, null], //Bad url. Not crawlable picture.
            __LINE__ => [$hostwww, $hostwww . '/app_dev.php/init.jpg#123', null, null], //Bad url. Not crawlable picture.
            __LINE__ => [$hostwww, 'https://en.wikipedia.org/wiki/Scrum', null, null], //Bad url. We don't need external url.
            __LINE__ => [$hostwww, 'mailto:info@a.com', null, null], //Bad url. Not http | https
            __LINE__ => [$hostwww, 'javascript:123', null, null], //Bad url. Not http | https

            // Multibyte path
            __LINE__ => ['http://xn--123.xn--123', 'http://xn--123.xn--123/%D0%BA', 'http://xn--123.xn--123/%d0%ba', null], //Good url. Do not convert.

            // Https
            __LINE__ => [$hostwww, 'https://a.com', null, null], //Bad url. Not http
        ];
    }
}