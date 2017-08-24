<?php
declare(strict_types=1);

namespace AppBundle\Helper;

/**
 * Simple pager.
 */
class Pager
{
    /** @var int */
    private $limit;
    /** @var int */
    private $offset;

    /**
     * @param null|int|string $limit
     * @param null|int|string $offset
     * @param null|int|string $limitDefault  Must be > 0
     * @param null|int|string $offsetDefault Must be >= 0
     */
    public function __construct(
        $limit = null,
        $offset = null,
        $limitDefault = null,
        $offsetDefault = null
    )
    {
        // Validate / convert arguments.
        if (\is_numeric($limit)) {
            $limit = (int)$limit;
        }
        if (\is_numeric($offset)) {
            $offset = (int)$offset;
        }
        if (\is_numeric($limitDefault)) {
            $limitDefault = (int)$limitDefault;
        }
        if (\is_numeric($offsetDefault)) {
            $offsetDefault = (int)$offsetDefault;
        }

        if (null === $limitDefault || !\is_numeric($limitDefault)) {
            throw new \InvalidArgumentException('$limitDefault must be int');
        }
        if (null === $offsetDefault || !\is_numeric($offsetDefault)) {
            throw new \InvalidArgumentException('$limitDefault must be int');
        }

        // Set values from "default" arguments.
        if (!\is_numeric($limit)) {
            $limit = $limitDefault;
        }
        if (!\is_numeric($offset)) {
            $offset = $offsetDefault;
        }

        // Business logic
        if ($limit <= 0) {
            throw new \InvalidArgumentException('limit must be more that 0');
        }
        if ($offset < 0) {
            throw new \InvalidArgumentException('offset must be more or equal that 0');
        }

        $this->limit = $limit;
        $this->offset = $offset;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }
}