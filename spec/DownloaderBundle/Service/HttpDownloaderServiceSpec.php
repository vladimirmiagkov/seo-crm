<?php

namespace spec\DownloaderBundle\Service;

use DownloaderBundle\Service\HttpDownloaderService;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class HttpDownloaderServiceSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith();
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(HttpDownloaderService::class);
    }

    //function it_should_download_url()
    //{
    //    $downloadResult = $this->download('http://www.elecmet52.ru');
    //    //$serpResult->getResponses()->shouldBe([\file_get_contents(self::GOOD_SEARCH_ENGINE_RESPONSE_XML)]);
    //}
}
