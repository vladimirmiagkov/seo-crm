<?php
declare(strict_types=1);

namespace spec\AppBundle\SearchEngine\KeywordPosition;

use AppBundle\Entity\Keyword;
use AppBundle\Entity\SearchEngine;
use AppBundle\Entity\Site;
use AppBundle\SearchEngine\KeywordPosition\YandexXml;
use AppBundle\SearchEngine\KeywordPosition\YandexXmlDownload;
use AppBundle\SearchEngine\SerpResult;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class YandexXmlSpec extends ObjectBehavior
{
    const GOOD_SEARCH_ENGINE_RESPONSE_XML = __DIR__ . '/good_search_engine_response.xml';

    function let(YandexXmlDownload $yandexXmlDownload)
    {
        $yandexXmlDownload->download(
            'fake_credentials',
            'fake_keyword',
            0,
            100)->willReturn(['', \file_get_contents(self::GOOD_SEARCH_ENGINE_RESPONSE_XML)]);

        $this->beConstructedWith($yandexXmlDownload, 'fake_credentials');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(YandexXml::class);
    }

    function it_should_found_keyword_position_in_serp()
    {
        $site = (new Site())->setName('http://www.elecmet52.ru');
        $keyword = (new Keyword())->setSite($site)->setName('fake_keyword');
        $searchEngine = new SearchEngine();

        $serpResult = $this->grabSerp($keyword, $searchEngine);
        $serpResult->getResponses()->shouldBe([\file_get_contents(self::GOOD_SEARCH_ENGINE_RESPONSE_XML)]);
        $serpResult->getStatus()->shouldBe(SerpResult::STATUS_ALL_GOOD);
        $serpResult->getKeywordPosition()->getPosition()->shouldBe(6);
    }

    function it_should_not_found_keyword_position_in_serp()
    {
        $site = (new Site())->setName('http://www.notexistssite.com');
        $keyword = (new Keyword())->setSite($site)->setName('fake_keyword');
        $searchEngine = new SearchEngine();

        $serpResult = $this->grabSerp($keyword, $searchEngine);
        $serpResult->getStatus()->shouldBe(SerpResult::STATUS_ALL_GOOD);
        $serpResult->getKeywordPosition()->shouldBeNull(); // Our goal site position NOT FOUND in serp.
    }

    function it_has_bad_status_when_search_engine_not_available(YandexXmlDownload $yandexXmlDownload)
    {
        $yandexXmlDownload->download(
            'fake_credentials',
            'fake_keyword',
            0,
            100)->willReturn(['', '']);

        $site = (new Site())->setName('http://www.notexistssite.com');
        $keyword = (new Keyword())->setSite($site)->setName('fake_keyword');
        $searchEngine = new SearchEngine();

        $serpResult = $this->grabSerp($keyword, $searchEngine);
        $serpResult->getStatus()->shouldBe(SerpResult::STATUS_SEARCH_ENGINE_NOT_AVAILABLE);
    }
}
