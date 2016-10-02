<?php

namespace MoodValue\Projection\Event;

use Doctrine\DBAL\Connection;
use MoodValue\Infrastructure\Repository\CollectionResult;

class EventFinder
{
    const TABLE_USER = 'event';

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->connection->setFetchMode(\PDO::FETCH_ASSOC);
    }

    public function findAll(int $start = 0, int $limit = 10) : CollectionResult
    {
        $results = $this->connection->fetchAll(
            sprintf('SELECT SQL_CALC_FOUND_ROWS * FROM %s LIMIT %d, %d', self::TABLE_USER, $start, $limit)
        );

        $total = $this->connection->fetchColumn('SELECT FOUND_ROWS()');

        return new CollectionResult($results, $total);
    }
}
