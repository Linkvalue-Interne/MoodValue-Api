<?php

namespace MoodValue\Model\Event;

use MoodValue\Model\User\UserId;

class Event
{
    private $eventId;

    private $name;

    private $text;

    private $startDate;

    private $endDate;

    private $dayOfWeek;

    private $mobileSplashscreen;

    private $users;

    private function __construct() {}

    public function create(
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

    public function add(UserId $userId)
    {
        $this->users[$userId->toString()] = $userId;
    }

    /**
     * @return UserId[]
     */
    public function getUsers() : array
    {
        return $this->users;
    }
}
