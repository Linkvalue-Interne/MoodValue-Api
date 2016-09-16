<?php

namespace MoodValue\Infrastructure\Repository;

class CollectionResult
{
    /**
     * @var array
     */
    private $results;

    /**
     * @var int
     */
    private $total;

    public function __construct(array $results, int $total)
    {
        $this->results = $results;
        $this->total = $total;
    }

    /**
     * @return array
     */
    public function getResults() : array
    {
        return $this->results;
    }

    /**
     * @return int
     */
    public function getTotal() : int
    {
        return $this->total;
    }
}