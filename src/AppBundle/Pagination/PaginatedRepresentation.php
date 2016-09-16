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
        int $page,
        int $total,
        int $limit,
        array $results
    ) {
        $this->page = $page;
        $this->pages = ceil($total / $limit);
        $this->total = $total;
        $this->limit = $limit;
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
