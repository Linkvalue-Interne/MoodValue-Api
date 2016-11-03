<?php

namespace MoodValue\Behat\Context\Domain;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use MoodValue\Behat\Context\Domain\Behaviour\Prooph;
use MoodValue\Model\Event\{
    Event, Event\EventWasAdded, EventId
};
use MoodValue\Model\User\Command\{
    AddDeviceTokenToUser, JoinEvent, RegisterUser
};
use MoodValue\Model\User\DeviceToken;
use MoodValue\Model\User\EmailAddress;
use MoodValue\Model\User\Event\{
    DeviceTokenWasAdded, UserJoinedEvent, UserWasRegistered
};
use MoodValue\Model\User\User;
use MoodValue\Model\User\UserId;
use PHPUnit_Framework_Assert as Assert;

class UserContext implements Context
{
    use Prooph;

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
     * @var UserId
     */
    private $userId;

    /**
     * @var EventId
     */
    private $eventId;

    public function __construct()
    {
        $this->initProoph();
    }

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
     * @Given I'm registered
     */
    public function iMRegistered()
    {
        $this->userId = UserId::fromString('4bd5dfb0-2527-41db-b8a4-58400ee97857');
        $this->userDeviceToken = '654C4DB3-3F68-4969-8ED2-80EA16B46EB0';

        $this->eventStore->appendTo($this->streamName, new \ArrayIterator([
            $userWasRegistered = UserWasRegistered::withData(
                $this->userId,
                EmailAddress::fromString('john.doe@example.com'),
                DeviceToken::fromString($this->userDeviceToken),
                new \DateTimeImmutable('now')
            )->withAddedMetadata('aggregate_type', User::class)
        ]));
    }

    /**
     * @Given there is an event
     */
    public function thereIsAnEvent()
    {
        $this->eventStore->appendTo($this->streamName, new \ArrayIterator([
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
     * @When I join the event
     */
    public function iJoinTheEvent()
    {
        $this->commandBus->dispatch(
            JoinEvent::withData($this->userId->toString(), $this->eventId->toString())
        );
    }

    /**
     * @When I add a new device token :deviceToken
     */
    public function iAddANewDeviceToken($deviceToken)
    {
        $this->commandBus->dispatch(
            AddDeviceTokenToUser::withData(
                $this->userId->toString(),
                $deviceToken
            )
        );
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
     * @Then I should be added to the event
     */
    public function iShouldBeAddedToTheEvent()
    {
        foreach ($this->eventStore->getRecordedEvents() as $recordedEvent) {
            if ($recordedEvent instanceof UserJoinedEvent) {
                $expectedPayload = [
                    'event_id' => $this->eventId->toString(),
                    'joined_at' => (new \DateTimeImmutable('now'))->format(\DATE_ISO8601)
                ];

                Assert::assertSame($expectedPayload, $recordedEvent->payload());

                return;
            }
        }

        Assert::fail(sprintf('No event of type %s found.', UserJoinedEvent::class));
    }

    /**
     * @Then I should have my new device token registered
     */
    public function iShouldHaveMyNewDeviceTokenRegistered()
    {
        foreach ($this->eventStore->getRecordedEvents() as $recordedEvent) {
            if ($recordedEvent instanceof DeviceTokenWasAdded) {
                $expectedPayload = [
                    'device_token' => $this->userDeviceToken
                ];

                Assert::assertSame($expectedPayload, $recordedEvent->payload());

                return;
            }
        }

        Assert::fail(sprintf('No event of type %s found.', UserJoinedEvent::class));
    }

}
