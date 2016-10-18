<?php

namespace MoodValue\Behat;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use MoodValue\Infrastructure\Repository\EventStoreEventRepository;
use MoodValue\Infrastructure\Repository\EventStoreUserRepository;
use MoodValue\Model\Event\Command\AddEvent;
use MoodValue\Model\Event\Event;
use MoodValue\Model\Event\Event\EventWasAdded;
use MoodValue\Model\Event\Handler\AddEventHandler;
use MoodValue\Model\User\Command\RegisterUser;
use MoodValue\Model\User\Event\UserWasRegistered;
use MoodValue\Model\User\Handler\RegisterUserHandler;
use MoodValue\Model\User\User;
use MoodValue\Model\User\UserId;
use MoodValue\Tests\Util\EventChecker;
use PHPUnit_Framework_Assert as Assert;
use Prooph\Common\Event\ProophActionEventEmitter;
use Prooph\EventSourcing\EventStoreIntegration\AggregateTranslator;
use Prooph\EventStore\Adapter\InMemoryAdapter;
use Prooph\EventStore\Aggregate\AggregateType;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\Stream\Stream;
use Prooph\EventStore\Stream\StreamName;
use Prooph\EventStoreBusBridge\EventPublisher;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\Plugin\Router\CommandRouter;

class ApplicationContext implements Context
{
    use EventChecker;

    /**
     * @var string
     */
    private $userEmail;

    /**
     * @var string
     */
    private $userDeviceToken;

    /**
     * @var \Exception
     */
    private $thrownException;

    /**
     * @var \Prooph\ServiceBus\CommandBus
     */
    private $commandBus;

    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * Initializes context
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
        $streamName = new StreamName('moodvalue_test_event_stream');
        $stream = new Stream($streamName, new \ArrayIterator());

        $eventStoreAdapter = new InMemoryAdapter();
        $eventStoreAdapter->create($stream);

        $this->eventStore = new EventStore($eventStoreAdapter, new ProophActionEventEmitter());
        $this->eventStore->beginTransaction();

        $commandRouter = (new CommandRouter())
            ->route(RegisterUser::class)->to(new RegisterUserHandler(
                new EventStoreUserRepository(
                    $this->eventStore,
                    AggregateType::fromAggregateRootClass(User::class),
                    new AggregateTranslator(),
                    null,
                    $streamName
                )
            ))
            ->route(AddEvent::class)->to(new AddEventHandler(
                new EventStoreEventRepository(
                    $this->eventStore,
                    AggregateType::fromAggregateRootClass(Event::class),
                    new AggregateTranslator(),
                    null,
                    $streamName
                )
            ));
        $this->commandBus = new CommandBus();
        $this->commandBus->utilize($commandRouter);
    }

    /**
     * ===== USER
     */

    /**
     * @Given I'm not registered yet
     */
    public function iMNotRegisteredYet()
    {
    }

    /**
     * @Given I provide (in)valid data:
     */
    public function iProvideValidData(TableNode $table)
    {
        $this->userEmail = $table->getRow(1)[0];
        $this->userDeviceToken = $table->getRow(1)[1];
    }

    /**
     * @When I try to register
     */
    public function iTryToRegister()
    {
        try {
            $this->commandBus->dispatch(
                RegisterUser::withData(
                    UserId::generate()->toString(),
                    $this->userEmail,
                    $this->userDeviceToken
                )
            );
        } catch (\Exception $e) {
            $this->thrownException = $e;
        }
    }

    /**
     * @Then I should be registered
     */
    public function iShouldBeRegistered()
    {
        $events = iterator_to_array($this->eventStore->getRecordedEvents());

        Assert::assertCount(1, $events);

        Assert::assertInstanceOf(UserWasRegistered::class, $events[0]);

        $expectedPayload = [
            'email' => $this->userEmail,
            'device_token' => $this->userDeviceToken,
            'created_at' => $events[0]->createdAt()->format(\DATE_ISO8601)
        ];

        Assert::assertSame($expectedPayload, $events[0]->payload());
    }

    /**
     * @Then My registration should be rejected with the message :message
     */
    public function iShouldNotBeRegistered(string $message)
    {
        Assert::assertEquals($message, $this->thrownException->getPrevious()->getMessage());
    }

    /**
     * ===== EVENT
     */

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
