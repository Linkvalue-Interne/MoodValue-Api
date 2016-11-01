<?php

namespace MoodValue\Projection\Event;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use MoodValue\Infrastructure\Repository\CollectionResult;
use MoodValue\Projection\User\UserFinder;

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

    public function findAllForUser(string $userId, int $start = 0, int $limit = 10) : CollectionResult
    {
        $results = $this->connection->fetchAll(
            sprintf('SELECT SQL_CALC_FOUND_ROWS *
                FROM %s mood_event
                INNER JOIN %s mood_user_has_event ON mood_user_has_event.event_id = mood_event.id
                INNER JOIN %s mood_user ON mood_user_has_event.user_id = mood_user.id
                WHERE mood_user.id = :user_id
                LIMIT %d, %d',
                self::TABLE_EVENT, self::TABLE_USER_HAS_EVENT, UserFinder::TABLE_USER,
                $start, $limit),
            [
                'user_id' => $userId
            ],
            [
                'user_id' => Type::STRING
            ]
        );

        $total = $this->connection->fetchColumn('SELECT FOUND_ROWS()');

        return new CollectionResult($results, $total);
    }
}
