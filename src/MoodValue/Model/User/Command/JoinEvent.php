<?php

namespace MoodValue\Model\User\Command;

use MoodValue\Model\Event\EventId;
use MoodValue\Model\User\UserId;
use Prooph\Common\Messaging\Command;
use Prooph\Common\Messaging\PayloadConstructable;
use Prooph\Common\Messaging\PayloadTrait;

final class JoinEvent extends Command implements PayloadConstructable
{
    use PayloadTrait;

    public static function withData(
        string $userId,
        string $eventId
    ) : self
    {
        return new self(
            [
                'user_id' => $userId,
                'event_id' => $eventId
            ]
        );
    }

    public function userId() : UserId
    {
        return UserId::fromString($this->payload['user_id']);
    }

    public function eventId() : EventId
    {
        return EventId::fromString($this->payload['event_id']);
    }
}
