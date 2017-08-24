<?php
declare(strict_types=1);

namespace AppBundle\Helper;

/**
 * Support class for work with datetime range.
 * Datetime range: from "start" datetime point to "end" datetime point.
 */
class DateTimeRange
{
    /** @var \DateTime */
    private $start;
    /** @var \DateTime */
    private $end;

    /**
     * Create date time range.
     *
     * @param null|string|\DateTime $start
     * @param null|string|\DateTime $end
     * @param string                $startDefault Default value for "start"
     * @param string                $endDefault   Default value for "end"
     */
    public function __construct($start = null, $end = null, $startDefault = 'now -1 month', $endDefault = 'now')
    {
        if (empty($startDefault)) {
            throw new \InvalidArgumentException('$startDefault cannot be empty.');
        }
        if (empty($endDefault)) {
            throw new \InvalidArgumentException('$endDefault cannot be empty.');
        }

        if (empty($start)) {
            $this->setStart(new \DateTime($startDefault));
        } elseif (\is_numeric($start)) { // Unix timestamp representstion of date time.
            $this->setStart((new \DateTime())->setTimestamp((int)$start));
        } elseif (\is_string($start)) { // String representstion of date time.
            $start = trim($start);
            $this->setStart((new \DateTime($start)));
        } elseif ($start instanceof \DateTime) { // DateTime representstion of date time.
            $this->setStart($start);
        } else {
            throw new \InvalidArgumentException('Unsupported $start datetime format.');
        }

        if (empty($end)) {
            $this->setEnd(new \DateTime($endDefault));
        } elseif (\is_numeric($end)) { // Unix timestamp representstion of date time.
            $this->setEnd((new \DateTime())->setTimestamp((int)$end));
        } elseif (\is_string($end)) { // String representstion of date time.
            $end = trim($end);
            $this->setEnd((new \DateTime($end)));
        } elseif ($end instanceof \DateTime) { // DateTime representstion of date time.
            $this->setEnd($end);
        } else {
            throw new \InvalidArgumentException('Unsupported $end datetime format.');
        }
    }

    /**
     * Expand time range:
     *   "start" set time to 00:00:00
     *   "end"   set time to 23:59:59
     *
     * @return $this
     */
    public function expandRangeToFullDay()
    {
        $this->setStart(($this->getStart())->setTime(0, 0, 0));
        $this->setEnd(($this->getEnd())->setTime(23, 59, 59));
        return $this;
    }

    /**
     * Determine: is date time range positive(from lower to higher)?
     *   "lower"  like '2017-01-01'
     *   "higher" like '2018-01-01'
     *
     * @return bool true = from lower to higher (or start == end); false = from higher to lower
     */
    public function isRangePositive()
    {
        return (bool)($this->getStart()->getTimestamp() <= $this->getEnd()->getTimestamp());
    }

    /**
     * Swap start and end points if needed.
     * Negative: from "higher" to "lower"
     *
     * @return $this
     */
    public function makeRangeNegative()
    {
        if ($this->isRangePositive()) {
            $this->swapRange();
        }
        return $this;
    }

    /**
     * Swap start and end points if needed.
     * Positive: from "lower" to "higher"
     *
     * @return $this
     */
    public function makeRangePositive()
    {
        if (!$this->isRangePositive()) {
            $this->swapRange();
        }
        return $this;
    }

    /**
     * Generate sequence like:
     *
     * @param string $interval
     * @return \DateTime[]|null
     */
    public function generateSequence(string $interval)
    {
        if (empty($interval)) {
            throw new \InvalidArgumentException('$interval can not be empty');
        }

        $result = null;

        $start = $this->getStart();
        $end = $this->getEnd();
        $interval = new \DateInterval($interval);

        $dates = new \DatePeriod($start, $interval, $end);
        if (!empty($dates)) {
            foreach ($dates as $date) {
                $result[] = $date;
            }
        }

        return $result;
    }

    // Getters / setters -----------------------------------------------------------------------------------------------

    /**
     * @return \DateTime
     */
    public function getStart(): \DateTime
    {
        return clone($this->start);
    }

    /**
     * @param \DateTime $start
     * @return $this
     */
    private function setStart(\DateTime $start)
    {
        $this->start = clone($start);
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEnd(): \DateTime
    {
        return clone($this->end);
    }

    /**
     * @param \DateTime $end
     * @return $this
     */
    private function setEnd(\DateTime $end)
    {
        $this->end = clone($end);
        return $this;
    }

    // Support methods -------------------------------------------------------------------------------------------------

    private function swapRange()
    {
        $tmp = $this->getEnd();
        $this->setEnd(clone($this->getStart()));
        $this->setStart($tmp);
    }
}