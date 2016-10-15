<?php

namespace MoodValue\Model\Event;

use MoodValue\Model\Event\Event\EventWasAdded;
use Prooph\EventSourcing\AggregateRoot;

class Event extends AggregateRoot
{
    /**
     * @var EventId
     */
    private $eventId;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $text;

    /**
     * @var \DateTimeInterface
     */
    private $startDate;

    /**
     * @var \DateTimeInterface
     */
    private $endDate;

    /**
     * @var int
     */
    private $dayOfWeek;

    /**
     * @var bool
     */
    private $mobileSplashscreen;

    public static function add(
        EventId $eventId,
        string $name,
        string $text,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        int $dayOfWeek,
        bool $mobileSplashscreen
    ) : Event
    {
        $event = new self();
        $event->eventId = $eventId;
        $event->name = $name;
        $event->text = $text;
        $event->startDate = $startDate;
        $event->endDate = $endDate;
        $event->dayOfWeek = $dayOfWeek;
        $event->mobileSplashscreen = $mobileSplashscreen;

        $event->recordThat(EventWasAdded::withData(
            $eventId,
            $name,
            $text,
            $startDate,
            $endDate,
            $dayOfWeek,
            $mobileSplashscreen
        ));

        return $event;
    }

    public function getEventId() : EventId
    {
        return $this->eventId;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getText() : string
    {
        return $this->text;
    }

    public function getStartDate() : \DateTimeInterface
    {
        return $this->startDate;
    }

    public function getEndDate() : \DateTimeInterface
    {
        return $this->endDate;
    }

    public function getDayOfWeek() : int
    {
        return $this->dayOfWeek;
    }

    public function getMobileSplashscreen() : bool
    {
        return $this->mobileSplashscreen;
    }

    protected function aggregateId() : string
    {
        return $this->eventId->toString();
    }

    protected function whenEventWasAdded(EventWasAdded $event)
    {
        $this->eventId = $event->eventId();
        $this->name = $event->name();
        $this->text = $event->text();
        $this->startDate = $event->startDate();
        $this->endDate = $event->endDate();
        $this->dayOfWeek = $event->dayOfWeek();
        $this->mobileSplashscreen = $event->mobileSplashscreen();
    }
}
