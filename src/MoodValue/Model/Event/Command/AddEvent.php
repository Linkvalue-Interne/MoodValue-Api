<?php

namespace MoodValue\Model\Event\Command;

use MoodValue\Model\Event\EventId;
use Prooph\Common\Messaging\Command;
use Prooph\Common\Messaging\PayloadConstructable;
use Prooph\Common\Messaging\PayloadTrait;

final class AddEvent extends Command implements PayloadConstructable
{
    use PayloadTrait;

    public static function withData(
        string $eventId,
        string $name,
        string $text,
        string $startDate,
        string $endDate,
        int $dayOfWeek,
        bool $mobileSplashscreen
    ) : self
    {
        return new self(
            [
                'event_id' => $eventId,
                'name' => $name,
                'text' => $text,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'day_of_week' => $dayOfWeek,
                'mobile_splashscreen' => $mobileSplashscreen
            ]
        );
    }

    public function eventId() : EventId
    {
        return EventId::fromString($this->payload['event_id']);
    }

    public function name() : string
    {
        return $this->payload['name'];
    }

    public function text() : string
    {
        return $this->payload['text'];
    }

    public function startDate() : \DateTimeInterface
    {
        return new \DateTimeImmutable($this->payload['start_date']);
    }

    public function endDate() : \DateTimeInterface
    {
        return new \DateTimeImmutable($this->payload['end_date']);
    }

    public function dayOfWeek() : int
    {
        return $this->payload['day_of_week'];
    }

    public function mobileSplashscreen() : bool
    {
        return $this->payload['mobile_splashscreen'];
    }
}
