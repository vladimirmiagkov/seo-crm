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

    //function it_should_request_url()
    //{
    //    $result = $this->request('http://www.site1.us');
    //    
    //}
}
