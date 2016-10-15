<?php

namespace MoodValue\Behat;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use MoodValue\Model\User\DeviceToken;
use MoodValue\Model\User\EmailAddress;
use MoodValue\Model\User\Event\UserWasRegistered;
use MoodValue\Model\User\User;
use MoodValue\Model\User\UserId;
use MoodValue\Tests\Util\EventChecker;
use PHPUnit_Framework_Assert as Assert;

/**
 * Defines application features from the Domain context
 */
class DomainContext implements Context
{
    use EventChecker;

    /**
     * @var array
     */
    private $eventStore;

    /**
     * @var User
     */
    private $user;

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
     * Initializes context
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
        $this->eventStore = [];
    }

    /**
     * @Given I'm not registered yet
     */
    public function iMNotRegisteredYet()
    {
        $this->user = null;
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
            $this->user = User::registerWithData(
                UserId::generate(),
                EmailAddress::fromString($this->userEmail),
                DeviceToken::fromString($this->userDeviceToken)
            );
        } catch (\Throwable $e) {
            $this->thrownException = $e;
        }
    }

    /**
     * @Then I should be registered
     */
    public function iShouldBeRegistered()
    {
        Assert::assertInstanceOf(User::class, $this->user);

        $events = $this->popRecordedEvent($this->user);

        Assert::assertCount(1, $events);

        Assert::assertInstanceOf(UserWasRegistered::class, $events[0]);

        $expectedPayload = [
            'email' => $this->userEmail,
            'device_token' => $this->userDeviceToken,
            'created_at' => (new \DateTimeImmutable())->format(DATE_ISO8601)
        ];

        Assert::assertSame($expectedPayload, $events[0]->payload());
    }

    /**
     * @Then My registration should be rejected with the message :message
     */
    public function iShouldNotBeRegistered(string $message)
    {
        Assert::assertEquals(null, $this->user);
        Assert::assertEquals($message, $this->thrownException->getMessage());
    }
}
