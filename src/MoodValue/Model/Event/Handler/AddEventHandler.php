<?php

namespace MoodValue\Model\Event\Handler;

use MoodValue\Model\Event\Event;
use MoodValue\Model\Event\EventRepository;
use MoodValue\Model\Event\Command\AddEvent;

final class AddEventHandler
{
    /**
     * @var EventRepository
     */
    private $eventRepository;

    public function __construct(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    public function __invoke(AddEvent $command)
    {
        $event = Event::add(
            $command->eventId(),
            $command->name(),
            $command->text(),
            $command->startDate(),
            $command->endDate(),
            $command->dayOfWeek(),
            $command->mobileSplashscreen()
        );

        $this->eventRepository->add($event);
    }
}
