<?php

namespace MoodValue\Tests\Util;

use Prooph\Common\Messaging\Message;
use Prooph\EventSourcing\AggregateChanged;
use Prooph\EventSourcing\AggregateRoot;
use Prooph\EventSourcing\EventStoreIntegration\AggregateRootDecorator;
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
     * @param AggregateRoot $recorder
     * @param AggregateChanged|string $expectedEvent
     *
     * @return bool
     */
    protected function itWasRecorded(AggregateRoot $recorder, $expectedEvent) : bool {
        $decorator = AggregateRootDecorator::newInstance();
        $events = $decorator->extractRecordedEvents($recorder);

        if ($expectedEvent instanceof AggregateChanged) {
            foreach ($events as $recordedEvent) {
                if ($recordedEvent instanceof $expectedEvent
                    && $expectedEvent->aggregateId() == $recordedEvent->aggregateId()
                    && $expectedEvent->payload() == $recordedEvent->payload()
                ) {
                    return true;
                }
            }
        }

        if (is_string($expectedEvent)) {
            foreach ($events as $recordedEvent) {
                if ($recordedEvent instanceof $expectedEvent) {
                    return true;
                }
            }
        }

        return false;
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
