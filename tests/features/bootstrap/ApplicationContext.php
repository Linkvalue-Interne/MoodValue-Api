<?php

namespace MoodValue\Behat;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use MoodValue\Infrastructure\Repository\EventStoreEventRepository;
use MoodValue\Infrastructure\Repository\EventStoreUserRepository;
use MoodValue\Model\Event\Command\AddEvent;
use MoodValue\Model\Event\Command\AddUserToEvent;
use MoodValue\Model\Event\Event;
use MoodValue\Model\Event\Event\EventWasAdded;
use MoodValue\Model\Event\EventId;
use MoodValue\Model\Event\Handler\AddEventHandler;
use MoodValue\Model\Event\Handler\AddUserToEventHandler;
use MoodValue\Model\User\Command\RegisterUser;
use MoodValue\Model\User\DeviceToken;
use MoodValue\Model\User\EmailAddress;
use MoodValue\Model\User\Event\UserWasRegistered;
use MoodValue\Model\User\Handler\RegisterUserHandler;
use MoodValue\Model\User\User;
use MoodValue\Model\User\UserId;
use PHPUnit_Framework_Assert as Assert;
use Prooph\Common\Event\ProophActionEventEmitter;
use Prooph\EventSourcing\EventStoreIntegration\AggregateTranslator;
use Prooph\EventStore\{
    Adapter\InMemoryAdapter, Aggregate\AggregateType, EventStore, Stream\Stream, Stream\StreamName
};
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\Plugin\Router\CommandRouter;

class ApplicationContext implements Context
{
    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @var CommandBus
     */
    private $commandBus;

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
     * @var array MoodValue event input data
     */
    private $event;

    /**
     * @var UserId
     */
    private $userId;

    /**
     * @var EventId
     */
    private $eventId;

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
            ))
            ->route(AddUserToEvent::class)->to(new AddUserToEventHandler(
                new EventStoreUserRepository(
                    $this->eventStore,
                    AggregateType::fromAggregateRootClass(User::class),
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
     * @Given I try to register with (in)valid data:
     */
    public function iProvideValidData(TableNode $table)
    {
        $this->userEmail = $table->getRow(1)[0];
        $this->userDeviceToken = $table->getRow(1)[1];

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

    /**
     * ===== ADD USER TO EVENT
     */

    /**
     * @Given I'm registered
     */
    public function iMRegistered()
    {
        $this->eventStore->appendTo(new StreamName('moodvalue_test_event_stream'), new \ArrayIterator([
            $userWasRegistered = UserWasRegistered::withData(
                $this->userId = UserId::fromString('4bd5dfb0-2527-41db-b8a4-58400ee97857'),
                EmailAddress::fromString('john.doe@example.com'),
                DeviceToken::fromString(md5('test')),
                new \DateTimeImmutable('now')
            )->withAddedMetadata('aggregate_type', User::class)
        ]));
    }

    /**
     * @Given there is an event
     */
    public function thereIsAnEventCalled()
    {
        $this->eventStore->appendTo(new StreamName('moodvalue_test_event_stream'), new \ArrayIterator([
            EventWasAdded::withData(
                $this->eventId = EventId::generate(),
                'Halloween',
                'text',
                new \DateTimeImmutable('now'),
                new \DateTimeImmutable('+10 days'),
                2,
                true
            )->withAddedMetadata('aggregate_type', Event::class)
        ]));
    }

    /**
     * @When I add myself to the event
     */
    public function iAddMyselfTheEvent()
    {
        $this->commandBus->dispatch(
            AddUserToEvent::withData(
                $this->userId->toString(),
                $this->eventId->toString()
            )
        );
    }

    /**
     * @Then I should be added to the event
     */
    public function iShouldBeAddedToTheEventCalled()
    {
        foreach ($this->eventStore->getRecordedEvents() as $recordedEvent) {
            if ($recordedEvent instanceof Event\UserJoinedEvent) {
                $expectedPayload = [
                    'event_id' => $this->eventId->toString(),
                    'joined_at' => (new \DateTimeImmutable('now'))->format(\DATE_ISO8601)
                ];

                Assert::assertSame($expectedPayload, $recordedEvent->payload());

                return;
            }
        }

        Assert::fail(sprintf('No event of type %s found.', Event\UserJoinedEvent::class));
    }
}
