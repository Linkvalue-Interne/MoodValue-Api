<?php

namespace MoodValue\Projection\Event;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use MoodValue\Infrastructure\Repository\CollectionResult;
use MoodValue\Model\User\UserId;

class EventFinder
{
    const TABLE_EVENT = 'event';
    const TABLE_USER_HAS_EVENT = 'user_has_event';

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
            sprintf('SELECT SQL_CALC_FOUND_ROWS * FROM %s LIMIT %d, %d', self::TABLE_EVENT, $start, $limit)
        );

        $total = $this->connection->fetchColumn('SELECT FOUND_ROWS()');

        return new CollectionResult($results, $total);
    }

    public function findAllForUser(UserId $userId, int $start = 0, int $limit = 10) : CollectionResult
    {
        $results = $this->connection->fetchAll(
            sprintf('SELECT SQL_CALC_FOUND_ROWS *
                FROM %s
                JOIN %s ON %s.event_id = event.id
                WHERE %s.user_id = :user_id
                LIMIT %d, %d',
                self::TABLE_EVENT, self::TABLE_USER_HAS_EVENT, self::TABLE_USER_HAS_EVENT, self::TABLE_USER_HAS_EVENT,
                $start, $limit),
            [
                'user_id' => $userId->toString()
            ],
            [
                'user_id' => Type::STRING
            ]
        );

        $total = $this->connection->fetchColumn('SELECT FOUND_ROWS()');

        return new CollectionResult($results, $total);
    }
}
