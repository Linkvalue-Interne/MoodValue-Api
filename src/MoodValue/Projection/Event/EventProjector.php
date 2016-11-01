<?php

namespace MoodValue\Projection\Event;

use Doctrine\DBAL\Connection;
use MoodValue\Model\Event\Event\EventWasAdded;

class EventProjector
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var EventFinder
     */
    private $eventFinder;

    public function __construct(Connection $connection, EventFinder $eventFinder)
    {
        $this->connection = $connection;
        $this->eventFinder = $eventFinder;
    }

    public function onEventWasAdded(EventWasAdded $event)
    {
        $this->connection->insert(
            EventFinder::TABLE_EVENT,
            [
                'id' => $event->eventId()->toString(),
                'name' => $event->name(),
                'text' => $event->text(),
                'start_date' => $event->startDate()->format('Y-m-d H:i:s'),
                'end_date' => $event->endDate()->format('Y-m-d H:i:s'),
                'day_of_week' => $event->dayOfWeek(),
                'mobile_splashscreen' => $event->mobileSplashscreen()
            ]
        );
    }
}
