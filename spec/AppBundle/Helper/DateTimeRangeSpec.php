<?php

namespace spec\AppBundle\Helper;

use AppBundle\Helper\DateTimeRange;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DateTimeRangeSpec extends ObjectBehavior
{
    // Init ------------------------------------------------------------------------------------------------------------

    function let()
    {
        $this->beConstructedWith(null, null, 'now -1 month', 'now');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DateTimeRange::class);
    }

    function it_should_has_correct_state_values_after_initialization()
    {
        $this->getStart()->shouldReturnAnInstanceOf('DateTime');
        $this->getEnd()->shouldReturnAnInstanceOf('DateTime');
    }

    function it_should_correctly_initialized_from_unixtimestamp()
    {
        $this->beConstructedWith('1503377630', '1503377639');
        $this->shouldHaveType(DateTimeRange::class);
    }

    function it_should_correctly_initialized_from_string()
    {
        $this->beConstructedWith('now -1 month', 'now');
        $this->shouldHaveType(DateTimeRange::class);
    }

    function it_should_correctly_initialized_from_datetime_php_object()
    {
        $this->beConstructedWith((new \DateTime()), (new \DateTime()));
        $this->shouldHaveType(DateTimeRange::class);
    }

    function it_should_throw_exception_during_instantiation_with_incorrect_input_values()
    {
        $this->beConstructedWith('hm...', 'hm...');
        $this->shouldThrow('\Exception')->duringInstantiation();
    }

    function it_should_throw_exception_during_instantiation_with_null_default_values()
    {
        $this->beConstructedWith(null, null, null, null);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    // Job -------------------------------------------------------------------------------------------------------------

    function it_should_correctly_expand_range_to_full_day()
    {
        $expandedRange = $this->expandRangeToFullDay();
        $expandedRange->shouldReturnAnInstanceOf(DateTimeRange::class);
        $expandedRange->getStart()->format('H:i:s')->shouldBe('00:00:00');
        $expandedRange->getEnd()->format('H:i:s')->shouldBe('23:59:59');
    }

    // Range positive / negative ---------------------------------------------------------------------------------------

    function it_should_has_positive_range()
    {
        $this->beConstructedWith('now -1 month', 'now');
        $this->isRangePositive()->shouldBe(true);
    }

    function it_should_has_positive_range_on_equal_start_and_end_points()
    {
        $this->beConstructedWith('now', 'now');
        $this->isRangePositive()->shouldBe(true);
    }

    function it_should_has_negative_range()
    {
        $this->beConstructedWith('now', 'now -1 month');
        $this->isRangePositive()->shouldBe(false);
    }

    function it_should_be_able_to_be_transformed_to_negative_range()
    {
        $this->beConstructedWith('now -1 month', 'now');
        $transfomedRange = $this->makeRangeNegative();
        $transfomedRange->isRangePositive()->shouldBe(false);
    }

    function it_should_be_able_to_be_transformed_to_positive_range()
    {
        $this->beConstructedWith('now', 'now -1 month');
        $transfomedRange = $this->makeRangePositive();
        $transfomedRange->isRangePositive()->shouldBe(true);
    }

    // Generate sequence -----------------------------------------------------------------------------------------------

    function it_should_be_able_to_generate_datetime_sequence__day_by_day()
    {
        $this->beConstructedWith('now -1 month', 'now');
        $this->generateSequence('P1D')->shouldBeArray();
    }
}