<?php

namespace spec\AppBundle\Helper\Filter;

use AppBundle\Helper\Filter\FilterItem;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FilterItemSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('page', 'name', 'text');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FilterItem::class);
    }

    function it_should_has_correct_entity()
    {
        $this->getEntity()->shouldBe('page');
    }

    function it_should_has_correct_name()
    {
        $this->getName()->shouldBe('name');
    }

    function it_should_throw_invalidargumentexception_during_instantiation_with_incorrect_input_values()
    {
        $this->beConstructedWith('page', 'name', 'text111111');
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }
}