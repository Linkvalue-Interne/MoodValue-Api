<?php

namespace MoodValue\Model\Event\Event;

use MoodValue\Model\Event\EventId;
use MoodValue\Model\User\UserId;
use Prooph\EventSourcing\AggregateChanged;

final class UserJoinedEvent extends AggregateChanged
{
    private $userId;
    private $eventId;
    private $joinedAt;

    public static function withData(
        UserId $userId,
        EventId $eventId,
        \DateTimeInterface $joinedAt
    ) : self
    {
        $user = self::occur(
            $userId->toString(),
            [
                'event_id' => $eventId->toString(),
                'joined_at' => $joinedAt->format(\DateTime::ISO8601)
            ]
        );

        $user->userId = $userId;
        $user->eventId = $eventId;
        $user->joinedAt = $joinedAt;

        return $user;
    }

    public function userId() : UserId
    {
        if ($this->userId === null) {
            $this->userId = UserId::fromString($this->aggregateId());
        }

        return $this->userId;
    }

    public function eventId() : EventId
    {
        if ($this->eventId === null) {
            $this->eventId = EventId::fromString($this->aggregateId());
        }

        return $this->eventId;
    }

    public function joinedAt() : \DateTimeInterface
    {
        if ($this->joinedAt === null) {
            $this->joinedAt = new \DateTimeImmutable($this->payload['joined_at']);
        }

        return $this->joinedAt;
    }
}