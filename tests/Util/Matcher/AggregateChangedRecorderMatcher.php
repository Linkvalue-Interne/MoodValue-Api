<?php

namespace MoodValue\Tests\Util\Matcher;

use MoodValue\Tests\Util\EventChecker;
use PhpSpec\Exception\Example\FailureException;
use PhpSpec\Matcher\BasicMatcher;
use Prooph\EventSourcing\AggregateRoot;

class AggregateChangedRecorderMatcher extends BasicMatcher
{
    use EventChecker;

    public function supports($name, $subject, array $arguments)
    {
        return in_array($name, ['haveRecorded'])
            && $subject instanceof AggregateRoot
            && 1 === count($arguments);
    }

    public function getPriority()
    {
        return 110;
    }

    protected function matches($subject, array $arguments)
    {
        $expectedEvent = $arguments[0];

        return $this->itWasRecorded($subject, $expectedEvent);
    }

    protected function getFailureException($name, $subject, array $arguments)
    {
        return new FailureException(sprintf(
            'Expected %s to have recorded event %s, but it hasn\'t.',
            get_class($subject),
            get_class($arguments[0])
        ));
    }

    protected function getNegativeFailureException($name, $subject, array $arguments)
    {
        return new FailureException(sprintf(
            'Expected %s to not have recorded event %s, but it has.',
            get_class($subject),
            get_class($arguments[0])
        ));
    }
}
