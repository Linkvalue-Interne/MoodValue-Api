<?php

namespace MoodValue\Behat\Context\Domain;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use MoodValue\Behat\Context\Domain\Behaviour\Prooph;
use MoodValue\Model\Event\{
    Command\AddEvent, Event\EventWasAdded
};
use PHPUnit_Framework_Assert as Assert;

class EventContext implements Context
{
    use Prooph;

    /**
     * @var \Exception
     */
    private $thrownException;

    /**
     * @var array MoodValue event input data
     */
    private $event;

    public function __construct()
    {
        $this->initProoph();
    }

    /**
     * @When I add a new event with data:
     */
    public function iAddANewEventWithData(TableNode $table)
    {
        $this->event = $event = $table->getIterator()[0];

        try {
            $this->commandBus->dispatch(
                AddEvent::withData(
                    $event['id'],
                    $event['name'],
                    $event['text'],
                    $event['from'],
                    $event['to'],
                    $event['day of week'],
                    $event['splash screen']
                )
            );
        } catch (\Exception $e) {
            $this->thrownException = $e;
        }
    }

    /**
     * @Then a new event should be added
     */
    public function aNewEventShouldBeAdded()
    {
        $events = iterator_to_array($this->eventStore->getRecordedEvents());

        Assert::assertCount(1, $events);
        Assert::assertInstanceOf(EventWasAdded::class, $events[0]);

        $expectedPayload = [
            'name' => $this->event['name'],
            'text' => $this->event['text'],
            'start_date' => (new \DateTimeImmutable($this->event['from']))->format(\DATE_ISO8601),
            'end_date' => (new \DateTimeImmutable($this->event['to']))->format(\DATE_ISO8601),
            'day_of_week' => (int) $this->event['day of week'],
            'mobile_splashscreen' => (bool) $this->event['splash screen'],
        ];

        Assert::assertSame($expectedPayload, $events[0]->payload());
    }
}
