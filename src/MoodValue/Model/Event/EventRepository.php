<?php

namespace MoodValue\Model\Event;

interface EventRepository
{
    public function add(Event $event);

    public function get(EventId $eventId);
}
