<?php

namespace spec\AppBundle\Utils;

use AppBundle\Utils\Pager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PagerSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(10, 0, 10, 0);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Pager::class);
    }

    function it_should_has_correct_state_values_after_initialization()
    {
        $this->getLimit()->shouldBe(10);
        $this->getOffset()->shouldBe(0);
    }

    function it_should_correctly_initialized_from_default_values()
    {
        $this->beConstructedWith(null, null, 10, 10);
        $this->shouldHaveType(Pager::class);
    }

    function it_should_correctly_initialized_from_string()
    {
        $this->beConstructedWith('', '', '10', '0');
        $this->shouldHaveType(Pager::class);
    }

    function it_should_throw_invalid_argument_exception_during_instantiation_with_incorrect_default_values()
    {
        $this->beConstructedWith(null, null, ' ', 'abc');
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    function it_should_throw_invalid_argument_exception_during_instantiation_with_null_default_values()
    {
        $this->beConstructedWith(null, null, null, null);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    function it_should_throw_invalid_argument_exception_during_instantiation_with_limit_zero_value()
    {
        $this->beConstructedWith(0, 0, 10, 0);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    function it_should_throw_invalid_argument_exception_during_instantiation_with_limitdefault_zero_value()
    {
        $this->beConstructedWith(null, null, 0, 0);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    function it_should_throw_invalid_argument_exception_during_instantiation_with_limit_minus_value()
    {
        $this->beConstructedWith(-10, 0, 1, 0);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    function it_should_throw_invalid_argument_exception_during_instantiation_with_limitdefault_minus_value()
    {
        $this->beConstructedWith(null, null, -10, 0);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    function it_should_throw_invalid_argument_exception_during_instantiation_with_offset_minus_value()
    {
        $this->beConstructedWith(1, -10, 1, 0);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    function it_should_throw_invalid_argument_exception_during_instantiation_with_offsetdefault_minus_value()
    {
        $this->beConstructedWith(null, null, 1, -10);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }
}