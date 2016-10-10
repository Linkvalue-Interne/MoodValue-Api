<?php

namespace MoodValue\Specs\PhpSpec\Matcher;

use PhpSpec\Matcher\Matcher;
use Prooph\EventSourcing\AggregateChanged;
use Prooph\EventSourcing\AggregateRoot;
use Prooph\EventSourcing\EventStoreIntegration\AggregateRootDecorator;

class AggregateChangedRecorderMatcher implements Matcher
{
    /**
     * @inheritDoc
     */
    public function getMatchers()
    {
        $itWasRecorded = function (AggregateRoot $recorder, $expectedEvent) {
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
        };

        return [
            'haveRecorded' =>
                function (AggregateRoot $recorder, $expectedEvent) use ($itWasRecorded) {
                    return $itWasRecorded($recorder, $expectedEvent);
                },
            'haveNotRecorded' =>
                function (AggregateRoot $recorder, $expectedEvent) use ($itWasRecorded) {
                    return !$itWasRecorded($recorder, $expectedEvent);
                },
        ];
    }

    /**
     * Checks if matcher supports provided subject and matcher name.
     *
     * @param string $name
     * @param mixed $subject
     * @param array $arguments
     *
     * @return Boolean
     */
    public function supports($name, $subject, array $arguments)
    {
        // TODO: Implement supports() method.
    }

    /**
     * Evaluates positive match.
     *
     * @param string $name
     * @param mixed $subject
     * @param array $arguments
     */
    public function positiveMatch($name, $subject, array $arguments)
    {
        // TODO: Implement positiveMatch() method.
    }

    /**
     * Evaluates negative match.
     *
     * @param string $name
     * @param mixed $subject
     * @param array $arguments
     */
    public function negativeMatch($name, $subject, array $arguments)
    {
        // TODO: Implement negativeMatch() method.
    }

    /**
     * Returns matcher priority.
     *
     * @return integer
     */
    public function getPriority()
    {
        // TODO: Implement getPriority() method.
    }
}
