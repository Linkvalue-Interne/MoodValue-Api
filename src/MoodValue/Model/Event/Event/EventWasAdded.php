<?php

namespace MoodValue\Model\Event\Event;

use MoodValue\Model\Event\EventId;
use Prooph\EventSourcing\AggregateChanged;

final class EventWasAdded extends AggregateChanged
{
    private $eventId;
    private $name;
    private $text;
    private $startDate;
    private $endDate;
    private $dayOfWeek;
    private $mobileSplashscreen;

    public static function withData(
        EventId $eventId,
        string $name,
        string $text,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        int $dayOfWeek,
        bool $mobileSplashscreen
    ) : EventWasAdded
    {
        $event = self::occur(
            $eventId->toString(),
            [
                'name' => $name,
                'text' => $text,
                'start_date' => $startDate->format(\DateTime::ISO8601),
                'end_date' => $endDate->format(\DateTime::ISO8601),
                'day_of_week' => $dayOfWeek,
                'mobile_splashscreen' => $mobileSplashscreen
            ]
        );

        $event->eventId = $eventId;
        $event->name = $name;
        $event->text = $text;
        $event->startDate = $startDate;
        $event->endDate = $endDate;
        $event->dayOfWeek = $dayOfWeek;
        $event->mobileSplashscreen = $mobileSplashscreen;

        return $event;
    }

    public function eventId() : EventId
    {
        if ($this->eventId === null) {
            $this->eventId = EventId::fromString($this->aggregateId());
        }

        return $this->eventId;
    }

    public function name() : string
    {
        if ($this->name === null) {
            $this->name = $this->payload['name'];
        }

        return $this->name;
    }


    public function text() : string
    {
        if ($this->text === null) {
            $this->text = $this->payload['text'];
        }

        return $this->text;
    }

    public function startDate() : \DateTimeInterface
    {
        if ($this->startDate === null) {
            $this->startDate = new \DateTimeImmutable($this->payload['start_date']);
        }

        return $this->startDate;
    }

    public function endDate() : \DateTimeInterface
    {
        if ($this->endDate === null) {
            $this->endDate = new \DateTimeImmutable($this->payload['end_date']);
        }

        return $this->endDate;
    }

    public function dayOfWeek() : int
    {
        if ($this->dayOfWeek === null) {
            $this->dayOfWeek = $this->payload['day_of_week'];
        }

        return $this->dayOfWeek;
    }

    public function mobileSplashscreen() : bool
    {
        if ($this->mobileSplashscreen === null) {
            $this->mobileSplashscreen = $this->payload['mobile_splashscreen'];
        }

        return $this->mobileSplashscreen;
    }
}