<?php

namespace AppBundle\Pagination;

/**
 * Allows to separate and filter url parameters for resource filtering
 */
class ResourceCriteria
{
    const PAGE_PARAMETER_NAME = 'page';
    const LIMIT_PARAMETER_NAME = 'limit';
    const MAX_LIMIT_PARAMETER_NAME = 'max_limit';
    const SORT_PARAMETER_NAME = 'sort';

    /**
     * @var int
     */
    private $limit;

    /**
     * @var int
     */
    private $maxLimit = 100;

    /**
     * @var int
     */
    private $start;

    /**
     * @var int
     */
    private $page;

    /**
     * @var array
     */
    private $criteria;

    /**
     * @var array
     */
    private $fieldsCriteria;

    /**
     * @var array
     */
    private $sortCriteria;

    /**
     * @param array $criteria
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(array $criteria)
    {
        $this->criteria = $criteria;

        $this->criteria[self::PAGE_PARAMETER_NAME] = $this->criteria[self::PAGE_PARAMETER_NAME] ?? 1;
        $this->criteria[self::LIMIT_PARAMETER_NAME] = $this->criteria[self::LIMIT_PARAMETER_NAME] ?? 10;
        $this->criteria[self::SORT_PARAMETER_NAME] = $this->criteria[self::SORT_PARAMETER_NAME] ?? '';
//        if (isset($this->criteria[self::PAGE_PARAMETER_NAME]) && !is_int($this->criteria[self::PAGE_PARAMETER_NAME])) {
//            throw new \InvalidArgumentException('Invalid page number');
//        }
//
//        if (isset($this->criteria[self::LIMIT_PARAMETER_NAME]) && !is_int($this->criteria[self::LIMIT_PARAMETER_NAME])) {
//            throw new \InvalidArgumentException('Invalid limit');
//        }
//
//        if (isset($this->criteria[self::SORT_PARAMETER_NAME]) && !is_array($this->criteria[self::SORT_PARAMETER_NAME])) {
//            throw new \InvalidArgumentException('Invalid sort criteria');
//        }

        $this->splitCriteria();
        $this->calculateStartAndLimitFromCriteria();
    }

    /**
     * Filter all get params by removing the ones used for pagination
     *
     * @throws \InvalidArgumentException
     *
     * @return ResourceCriteria
     */
    private function splitCriteria()
    {
        $this->fieldsCriteria = array_diff_key(
            $this->criteria,
            array(
                self::PAGE_PARAMETER_NAME  => null,
                self::LIMIT_PARAMETER_NAME => null,
                self::SORT_PARAMETER_NAME  => null
            )
        );

        $this->sortCriteria = explode(',', $this->criteria[self::SORT_PARAMETER_NAME]);

        return $this;
    }

    /**
     * Get start and limit from page and perPage
     *
     * @param int $page    Page number
     * @param int $perPage Results per page
     *
     * @return ResourceCriteria
     */
    private function calculateStartAndLimit($page, $perPage = 10)
    {
        $this->page = max($page, 1);
        $this->limit = max(min($perPage, $this->maxLimit), 1);
        $this->start = ($this->page - 1) * $this->limit;

        return $this;
    }

    /**
     * Calculate start and limit from criteria
     *
     * @throws \InvalidArgumentException
     *
     * @return ResourceCriteria
     */
    private function calculateStartAndLimitFromCriteria()
    {
        return $this->calculateStartAndLimit(
            $this->criteria[self::PAGE_PARAMETER_NAME],
            $this->criteria[self::LIMIT_PARAMETER_NAME]
        );
    }

    /**
     * @return array
     */
    public function getFieldsCriteria()
    {
        return $this->fieldsCriteria;
    }

    /**
     * @return array
     */
    public function getSortCriteria()
    {
        return $this->sortCriteria;
    }

    /**
     * @return array
     */
    public function getSortFieldsWithDirections()
    {
        $sortFieldsWithDirections = array();

        foreach ($this->sortCriteria as $field) {
            if (empty($field)) {
                continue;
            }

            $desc = '-' === $field[0];
            $fieldName = $desc ? substr($field, 1) : $field;

            $sortFieldsWithDirections[] = array($fieldName, $desc ? 'DESC' : 'ASC');
        }

        return $sortFieldsWithDirections;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @return int
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @return array
     */
    public function getCriteria()
    {
        return $this->criteria;
    }
}
