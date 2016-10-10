<?php

namespace MoodValue\Behat;

use Prooph\Common\Messaging\Message;
use Prooph\EventSourcing\AggregateChanged;
use Prooph\EventSourcing\AggregateRoot;
use Prooph\EventSourcing\EventStoreIntegration\AggregateTranslator;
use Prooph\EventStore\Aggregate\AggregateType;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\Plugin\Router\RegexRouter;

trait EventChecker
{
    /**
     * @var AggregateTranslator
     */
    private $aggregateTranslator;

    /**
     * @var Message[]
     */
    private $events;

    /**
     * Define an additional catchall router and adds it to a specific event bus.
     * The recorded events are stored in $this->events.
     */
    protected function startCollectingEventsFromBus(EventBus $eventBus)
    {
        $router = new RegexRouter();
        $router->route(RegexRouter::ALL)->to(function ($event) {
            $this->events[] = $event;
        });

        $eventBus->utilize($router);
    }

    /**
     * @return AggregateChanged[]
     */
    protected function popRecordedEvent(AggregateRoot $aggregateRoot) : array
    {
        return $this->getAggregateTranslator()->extractPendingStreamEvents($aggregateRoot);
    }

    /**
     * @return object Aggregate
     */
    protected function reconstituteAggregateFromHistory($aggregateRootClass, array $events)
    {
        return $this->getAggregateTranslator()->reconstituteAggregateFromHistory(
            AggregateType::fromAggregateRootClass($aggregateRootClass),
            new \ArrayIterator($events)
        );
    }

    /**
     * @return AggregateTranslator
     */
    private function getAggregateTranslator() : AggregateTranslator
    {
        if (null === $this->aggregateTranslator) {
            $this->aggregateTranslator = new AggregateTranslator();
        }

        return $this->aggregateTranslator;
    }
}
