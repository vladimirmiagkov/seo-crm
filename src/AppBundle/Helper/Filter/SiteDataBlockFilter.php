<?php
declare(strict_types=1);

namespace AppBundle\Helper\Filter;

use Doctrine\ORM\QueryBuilder;

/**
 * Filter for SiteDataBlock. + Sort logic.
 * Operate with Doctrine query builder.
 */
class SiteDataBlockFilter
{
    /**
     * Available filters for this filter. Security stuff.
     */
    const LIST_OF_AVAILABLE_FILTER_NAMES = [
        'page.searchEngines',
        'page.name',
        'page.tags',
        'keyword.searchEngines',
        'keyword.name',
        'keyword.fromPlace',
        'keyword.searchEngineRequestLimit',
    ];
    /**
     * Available entities for this filter. Security stuff.
     */
    const LIST_OF_AVAILABLE_ENTITIES = [
        'page',
        'keyword',
    ];

    /**
     * List of filter items. Index = filter order.
     *
     * @var null|FilterItem[]
     */
    private $filterItems = null;

    /**
     * Procedure: fill up filterItems from incomming array (from frontend).
     *
     * @param null|array $inputFilterArray
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setFilterItemsFromArray($inputFilterArray = null)
    {
        $filtersArrayToProcees = $inputFilterArray;
        if (isset($inputFilterArray['filters'])) { // !!! Sub array.
            $filtersArrayToProcees = $inputFilterArray['filters'];
        }
        if (empty($filtersArrayToProcees)) {
            $this->filterItems = null;
            return $this;
        }

        foreach ($filtersArrayToProcees as $filterOrder => $filterData) {
            if (!\in_array($filterData['name'], self::LIST_OF_AVAILABLE_FILTER_NAMES)) {
                throw new \InvalidArgumentException('You trying to use unavailable filter:' . \htmlspecialchars($filterData['name']));
            }

            list($filterEntity, $filterName) = \explode('.', $filterData['name']);
            if (!\in_array($filterEntity, self::LIST_OF_AVAILABLE_ENTITIES)) {
                throw new \InvalidArgumentException('You trying to use unavailable entity:' . \htmlspecialchars($filterEntity));
            }

            $filterItem = new FilterItem(
                $filterEntity,
                $filterName,
                $filterData['type'],
                ($filterData['valuesAvailable'] ?? null),
                ($filterData['values'] ?? null),
                ($filterData['sortDirection'] ?? null),
                ($filterData['valueMin'] ?? null),
                ($filterData['valueMax'] ?? null)
            );
            $this->filterItems[$filterOrder] = $filterItem;
        }

        return $this;
    }

    /**
     * @param QueryBuilder $qb
     * @param string       $goalEntityName Like: 'page'
     * @return QueryBuilder
     */
    public function applyFilterToQueryBuilder(QueryBuilder $qb, string $goalEntityName): QueryBuilder
    {
        if (null === $this->filterItems) {
            return $qb;
        }

        // Apply filters.
        foreach ($this->getFiltersForRestrictionsByEntityName($goalEntityName) as $filter) {
            $entityCollumn = $filter->getEntity() . '.' . $filter->getName(); // Like: 'page.name'
            $placeholder = $filter->getEntity() . $filter->getName(); // Like: 'pagename'

            switch ($filter->getType()) {
                case FilterItem::ITEM_TYPE_TEXT:
                    $qb->andWhere($qb->expr()->like($entityCollumn, ':' . $placeholder));
                    $qb->setParameter($placeholder, '%' . $filter->getValues() . '%');
                    break;
                case FilterItem::ITEM_TYPE_MULTISELECT:
                    switch ($filter->getName()) {
                        case 'searchEngines':
                            // Join external table.
                            $qb->andWhere($qb->expr()->in('searchEngine.id', ':' . $placeholder));
                            $qb->setParameter($placeholder, $filter->getValues());
                            break;
                        default:
                            $qb->andWhere($qb->expr()->in($entityCollumn, ':' . $placeholder));
                            $qb->setParameter($placeholder, $filter->getValues());
                    }
                    break;
                case FilterItem::ITEM_TYPE_RANGE:
                    if (null !== $filter->getValueMin()) {
                        $qb->andWhere($qb->expr()->gte($entityCollumn, ':' . $placeholder . 'min'));
                        $qb->setParameter($placeholder . 'min', $filter->getValueMin());
                    }
                    if (null !== $filter->getValueMax()) {
                        $qb->andWhere($qb->expr()->lte($entityCollumn, ':' . $placeholder . 'max'));
                        $qb->setParameter($placeholder . 'max', $filter->getValueMax());
                    }
                    break;
                default:
                    throw new \InvalidArgumentException('You trying to use unavailable filter type:' . $filter->getType());
            }

        }

        // Apply sorting.
        foreach ($this->getFiltersForSortByEntityName($goalEntityName) as $filter) {
            $entityCollumn = $filter->getEntity() . '.' . $filter->getName(); // Like: 'page.name'

            switch ($filter->getType()) {
                case FilterItem::ITEM_TYPE_MULTISELECT:
                    switch ($filter->getName()) {
                        case 'searchEngines':
                            // External table.
                            $qb->addOrderBy('searchEngine.name', $filter->getSortDirection());
                            break;
                        default:
                            $qb->addOrderBy($entityCollumn, $filter->getSortDirection());
                    }
                    break;
                default:
                    $qb->addOrderBy($entityCollumn, $filter->getSortDirection());
            }
        }

        return $qb;
    }

    /**
     * Get all "restriction" filters by entity name.
     *
     * @param string $goalEntityName
     * @return FilterItem[]
     */
    protected function getFiltersForRestrictionsByEntityName(string $goalEntityName): array
    {
        $result = [];
        foreach ($this->filterItems as $filterKey => $filter) {
            // We need filters only for goal entity.
            if ($filter->getEntity() !== $goalEntityName) {
                continue;
            }

            // values not empty?
            switch ($filter->getType()) {
                case FilterItem::ITEM_TYPE_RANGE:
                    if (null === $filter->getValueMin() && null === $filter->getValueMax()) {
                        continue(2);
                    }
                    break;
                default:
                    if (null === $filter->getValues()) {
                        continue(2);
                    }
            }

            $result[$filterKey] = $filter;
        }

        return $result;
    }

    /**
     * Get all filters available for sort by entity name.
     *
     * @param string $goalEntityName
     * @return FilterItem[]
     */
    protected function getFiltersForSortByEntityName(string $goalEntityName): array
    {
        $result = [];
        foreach ($this->filterItems as $filterKey => $filter) {
            // We need filters only for goal entity.
            if ($filter->getEntity() !== $goalEntityName) {
                continue;
            }

            // values not empty?
            if (null === $filter->getSortDirection()) {
                continue;
            }

            $result[$filterKey] = $filter;
        }

        return $result;
    }
}