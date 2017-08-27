<?php

namespace spec\AppBundle\Helper\Filter;

use AppBundle\Helper\Filter\SiteDataBlockFilter;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SiteDataBlockFilterSpec extends ObjectBehavior
{
    private $filterDataFromFrontendRaw = '
        {
            "name":"page.searchEngines",
            "title":"Search engine (page)",
            "type":"multiSelect",
            "valuesAvailable":[
                {"label":"Google","value":"1"},
                {"label":"Yandex","value":"2"}
            ],
            "values":[],
            "sortDirection":""
        },
        {
            "name":"keyword.searchEngines",
            "title":"Search engine (keyword)",
            "type":"multiSelect",
            "valuesAvailable":[
                {"label":"Google","value":"1"},
                {"label":"Yandex","value":"2"}
            ],
            "values":[],
            "sortDirection":""
        },
        {
            "name":"page.name",
            "title":"Name (page)",
            "type":"text",
            "values":"",
            "sortDirection":"ASC"
        },
        {
            "name":"keyword.name",
            "title":"Name (keyword)",
            "type":"text",
            "values":"",
            "sortDirection":""
        },
        {
            "name":"keyword.fromPlace",
            "title":"From location (keyword)",
            "type":"multiSelect",
            "valuesAvailable":[
                {"label":"NN","value":"47"},
                {"label":"NY","value":"1"}
            ],
            "values":["47"],
            "sortDirection":""
        },
        {
            "name":"keyword.searchEngineRequestLimit",
            "title":"Req (request) (keyword)",
            "type":"range",
            "valueMin":"100",
            "valueMax":"600",
            "sortDirection":""
        }
    ';
    private $filterDataFromFrontend = null;

    function let()
    {
        $this->filterDataFromFrontend = \json_decode($this->filterDataFromFrontendRaw, true);
        $this->beConstructedWith();
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SiteDataBlockFilter::class);
    }

    function it_should_has_correctly_set_filteritems_from_array()
    {
        $this->setFilterItemsFromArray($this->filterDataFromFrontend)->shouldHaveType(SiteDataBlockFilter::class);
    }

    function it_should_has_correctly_reset_filteritems_from_null()
    {
        $this->setFilterItemsFromArray(null)->shouldHaveType(SiteDataBlockFilter::class);
    }

    function it_should_throw_invalidargumentexception_on_set_unavailable_filteritems_name_from_array()
    {
        $inputFilterArray = json_decode('{
                                                "filters":[
                                                    {
                                                        "name":"page.searchEngine1111111",
                                                        "type":"multiSelect"
                                                    }
                                                ]
                                            }', true);
        $this->shouldThrow('\InvalidArgumentException')->during('setFilterItemsFromArray', [$inputFilterArray]);
    }

    function it_should_throw_invalidargumentexception_on_set_unavailable_filteritems_entity_from_array()
    {
        $inputFilterArray = json_decode('{
                                                "filters":[
                                                    {
                                                        "name":"page1111.searchEngine",
                                                        "type":"multiSelect"
                                                    }
                                                ]
                                            }', true);
        $this->shouldThrow('\InvalidArgumentException')->during('setFilterItemsFromArray', [$inputFilterArray]);
    }
}