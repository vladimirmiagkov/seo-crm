<?php
declare(strict_types=1);

namespace AppBundle\Helper\Filter;

class FilterItem
{
    const ITEM_TYPE_MULTISELECT = 'multiSelect';
    const ITEM_TYPE_TEXT = 'text';
    const ITEM_TYPE_RANGE = 'range';
    const LIST_OF_AVAILABLE_FILTER_TYPES = [
        self::ITEM_TYPE_MULTISELECT,
        self::ITEM_TYPE_TEXT,
        self::ITEM_TYPE_RANGE,
    ];

    const SORT_DIRECTION_NONE = '';
    const SORT_DIRECTION_ASC = 'ASC';
    const SORT_DIRECTION_DESC = 'DESC';

    /**
     * Filter identifier. Like "page"
     *
     * @var string
     */
    private $entity;
    /**
     * Filter identifier. Like "name"
     *
     * @var string
     */
    private $name;
    /**
     * Filter identifier. Like "multiSelect"
     *
     * @var string
     */
    private $type;
    /**
     * List of available options for select.
     * Like: [ {"label":"Google","value":"1"}, {"label":"Yandex","value":"2"} ]
     *
     * @var null|array
     */
    private $valuesAvailable = null; // TODO: do we need this?
    /**
     * Main filter value(s). Like: null | string | array.
     *
     * @var null|string|array
     */
    private $values = null;
    /**
     * Sort order. Like: '' or 'ASC' or 'DESC'.
     *
     * @var null|string
     */
    private $sortDirection = null;

    /**
     * Min value. Used for "range" type.
     *
     * @var null|mixed
     */
    private $valueMin = null;
    /**
     * Max value. Used for "range" type.
     *
     * @var null|mixed
     */
    private $valueMax = null;

    public function __construct(
        string $entity,
        string $name,
        string $type,
        $valuesAvailable = null,
        $values = null,
        $sortDirection = null,
        $valueMin = null,
        $valueMax = null
    )
    {
        $this->entity = $entity;
        $this->name = $name;

        if (!\in_array($type, self::LIST_OF_AVAILABLE_FILTER_TYPES)) {
            throw new \InvalidArgumentException('You trying to use unavailable filter type:' . \htmlspecialchars($type));
        }
        $this->type = $type;


        if (!self::isValueEmpty($valuesAvailable)) {
            $this->valuesAvailable = $valuesAvailable;
        }

        if (!self::isValueEmpty($values)) {
            $this->values = $values;
        }

        if (!empty($sortDirection)) {
            $sortDirection = \mb_strtoupper(trim($sortDirection));
            if ($sortDirection === self::SORT_DIRECTION_ASC || $sortDirection === self::SORT_DIRECTION_DESC) {
                $this->sortDirection = $sortDirection;
            }
        }

        if (!self::isValueEmpty($valueMin)) {
            $this->valueMin = (int)$valueMin;
        }

        if (!self::isValueEmpty($valueMax)) {
            $this->valueMax = (int)$valueMax;
        }
    }

    // Getters / setters -----------------------------------------------------------------------------------------------

    /**
     * @return string
     */
    public function getEntity(): string
    {
        return $this->entity;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return null|array
     */
    public function getValuesAvailable()
    {
        return $this->valuesAvailable;
    }

    /**
     * @return array|null|string
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @return null|string
     */
    public function getSortDirection()
    {
        return $this->sortDirection;
    }

    /**
     * @return mixed|null
     */
    public function getValueMin()
    {
        return $this->valueMin;
    }

    /**
     * @return mixed|null
     */
    public function getValueMax()
    {
        return $this->valueMax;
    }

    // Helper methods --------------------------------------------------------------------------------------------------
    private function isValueEmpty($value)
    {
        if (
            null === $value
            || '' === $value
            || (\is_array($value) && empty($value))
        ) {
            return true;
        } else {
            return false;
        }
    }
}