<?php

namespace Tests\AppBundle\Service;

use AppBundle\Service\KeywordPositionService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class KeywordPositionServiceTest extends KernelTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testDoWeNeedToCheckKeywordPositionInSearchEngine()
    {
        $this->assertSame(true, true);

        // last check = null
        $this->assertSame(true, KeywordPositionService::doWeNeedToCheckKeywordPositionInSearchEngine(
            null,
            86400,
            '2020-01-01 00:00:00'
        ));
        // + 1 hour
        $this->assertSame(false, KeywordPositionService::doWeNeedToCheckKeywordPositionInSearchEngine(
            (new \DateTime('2020-01-01 00:00:00')),
            86400,
            '2020-01-01 01:00:00'
        ));
        // + 23:59:59
        $this->assertSame(false, KeywordPositionService::doWeNeedToCheckKeywordPositionInSearchEngine(
            (new \DateTime('2020-01-01 00:00:00')),
            86400,
            '2020-01-01 23:59:59'
        ));
        // + 1 day
        $this->assertSame(true, KeywordPositionService::doWeNeedToCheckKeywordPositionInSearchEngine(
            (new \DateTime('2020-01-01 00:00:00')),
            86400,
            '2020-01-02 00:00:00'
        ));
        // check position once per day: + 10 day
        $this->assertSame(true, KeywordPositionService::doWeNeedToCheckKeywordPositionInSearchEngine(
            (new \DateTime('2020-01-01 00:00:00')),
            86400,
            '2020-01-10 00:00:00'
        ));
        // check position once per 7 days: + 7 days
        $this->assertSame(true, KeywordPositionService::doWeNeedToCheckKeywordPositionInSearchEngine(
            (new \DateTime('2020-01-01 00:00:00')),
            86400 * 7,
            '2020-01-08 00:00:00'
        ));
        // check position once per 7 days: + 6 days
        $this->assertSame(false, KeywordPositionService::doWeNeedToCheckKeywordPositionInSearchEngine(
            (new \DateTime('2020-01-01 00:00:00')),
            86400 * 7,
            '2020-01-07 00:00:00'
        ));
    }
}