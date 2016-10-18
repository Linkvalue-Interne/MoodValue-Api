<?php

namespace spec\MoodValue\Model\Event;

use MoodValue\Model\Event\Event;
use MoodValue\Model\Event\Event\EventWasAdded;
use MoodValue\Model\Event\EventId;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class EventSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Event::class);
    }

    function it_adds_a_new_event()
    {
        $this->beConstructedThrough('add', [
            $eventId = EventId::generate(),
            $name = 'name',
            $text = 'text',
            $from = new \DateTimeImmutable(),
            $to = new \DateTimeImmutable('+5 days'),
            $dayOfWeek = 3,
            $splashScreen = true
        ]);

        $this->shouldHaveRecorded(EventWasAdded::withData(
            $eventId, $name, $text, $from, $to, $dayOfWeek, $splashScreen
        ));
    }
}
