<?php

namespace MoodValue\Infrastructure\Repository;

use MoodValue\Model\Event\Event;
use MoodValue\Model\Event\EventId;
use MoodValue\Model\Event\EventRepository;
use Prooph\EventStore\Aggregate\AggregateRepository;

final class EventStoreEventRepository extends AggregateRepository implements EventRepository
{
    public function add(Event $event)
    {
        $this->addAggregateRoot($event);
    }

    public function get(EventId $eventId) : Event
    {
        return $this->getAggregateRoot($eventId->toString());
    }
}
