<?php

namespace MoodValue\Infrastructure\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use MoodValue\Model\Event\Event;
use MoodValue\Model\Event\EventId;
use MoodValue\Model\Event\EventRepository;
use MoodValue\Model\User\UserId;

class EventMysqlRepository implements EventRepository
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

    public function add(Event $event) : int
    {
        $isInserted = $this->connection->insert(self::TABLE_EVENT, [
            'id' => $event->getEventId()->toString(),
            'name' => $event->getName(),
            'text' => $event->getText(),
            'start_date' => $event->getStartDate()->format('Y-m-d H:i:s'),
            'end_date' => $event->getEndDate()->format('Y-m-d H:i:s'),
            'day_of_week' => $event->getDayOfWeek(),
            'mobile_splashscreen' => $event->getMobileSplashscreen()
        ]);

        foreach ($event->getUsers() as $userId) {
            $this->connection->insert(self::TABLE_USER_HAS_EVENT, [
                'user_id' => $userId->toString(),
                'event_id' => $event->getEventId()->toString(),
                'joined_at' => (new \DateTimeImmutable('now'))->format('Y-m-d H:i:s')
            ]);
        }

        return $isInserted;
    }

    public function get(EventId $eventId)
    {
        $stmt = $this->connection->prepare(sprintf('SELECT * FROM %s where id = :event_id', self::TABLE_EVENT));
        $stmt->bindValue('event_id', $eventId->toString());
        $stmt->execute();

        return $stmt->fetch();
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
LIMIT %d, %d', self::TABLE_EVENT, self::TABLE_USER_HAS_EVENT, self::TABLE_USER_HAS_EVENT, self::TABLE_USER_HAS_EVENT, $start, $limit),
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
