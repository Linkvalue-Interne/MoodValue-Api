<?php

namespace AppBundle\Pagination;

class PaginatedRepresentation
{
    private $page;
    private $pages;
    private $total;
    private $limit;
    private $results;

    public function __construct(
        ResourceCriteria $resourceCriteria,
        int $total,
        array $results
    ) {
        $this->page = $resourceCriteria->getPage();
        $this->pages = max(ceil($total / $resourceCriteria->getLimit()), 1);
        $this->total = $total;
        $this->limit = $resourceCriteria->getLimit();
        $this->results = $results;
    }

    public function toArray() : array
    {
        return [
            'page' => $this->page,
            'pages' => $this->pages,
            'total' => $this->total,
            'limit' => $this->limit,
            'results' => $this->results
        ];
    }
}
