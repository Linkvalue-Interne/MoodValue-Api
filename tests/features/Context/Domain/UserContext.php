<?php

namespace MoodValue\Behat\Context\Domain;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use MoodValue\Behat\Context\Domain\Behaviour\Prooph;
use MoodValue\Model\User\Command\JoinEvent;
use MoodValue\Model\Event\Event\EventWasAdded;
use MoodValue\Model\Event\EventId;
use MoodValue\Model\User\Command\RegisterUser;
use MoodValue\Model\User\DeviceToken;
use MoodValue\Model\User\EmailAddress;
use MoodValue\Model\User\Event\UserJoinedEvent;
use MoodValue\Model\User\Event\UserWasRegistered;
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
     * ===== ADD USER
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
     * ===== JOIN EVENT
     */

    /**
     * @Given I'm registered
     */
    public function iMRegistered()
    {
        $this->eventStore->appendTo($this->streamName, new \ArrayIterator([
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

        Assert::fail(sprintf('No event of type %s found.', Event\UserJoinedEvent::class));
    }
}
