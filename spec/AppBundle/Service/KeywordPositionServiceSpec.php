<?php

namespace spec\AppBundle\Service;

use AppBundle\Entity\Keyword;
use AppBundle\Entity\SearchEngine;
use AppBundle\Repository\KeywordPositionRepository;
use AppBundle\Repository\KeywordRepository;
use AppBundle\SearchEngine\KeywordPosition\YandexXml;
use AppBundle\SearchEngine\SerpResult;
use AppBundle\Service\KeywordCompetitorService;
use AppBundle\Service\KeywordPositionLogService;
use AppBundle\Service\KeywordPositionService;
use AppBundle\Service\KeywordService;
use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class KeywordPositionServiceSpec extends ObjectBehavior
{
    function let(
        EntityManager $em,
        KeywordService $keywordService,
        KeywordRepository $keywordRepository,
        KeywordPositionRepository $keywordPositionRepository,
        KeywordPositionLogService $keywordPositionLogService,
        KeywordCompetitorService $keywordCompetitorService,
        YandexXml $searchEngineYandexXml
    )
    {
        $this->beConstructedWith(
            $em,
            $keywordService,
            $keywordRepository,
            $keywordPositionRepository,
            $keywordPositionLogService,
            $keywordCompetitorService,
            $searchEngineYandexXml
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(KeywordPositionService::class);
    }

    function it_should_grab_keyword_position_from_search_engines(KeywordRepository $keywordRepository, YandexXml $searchEngineYandexXml)
    {
        $searchEngine = (new SearchEngine())
            ->setName('Yandex')
            ->setType(SearchEngine::YANDEX_TYPE)
            ->setShortName('Y');
        $keyword = (new Keyword())->addSearchEngine($searchEngine);
        $serpResult = new SerpResult();

        $keywordRepository->findKeywordForPositionCheck()->willReturn($keyword)->shouldBeCalled();
        $this->doWeNeedToCheckKeywordPositionInSearchEngine(null, $searchEngine->getCheckKeywordPositionPeriodicity())->shouldReturn(true);
        $searchEngineYandexXml->grabSerp($keyword, $searchEngine, 0)->willReturn($serpResult)->shouldBeCalled();

        $this->grabKeywordPositionFromSearchEngines(false)->shouldReturn([$serpResult]);
    }
}
