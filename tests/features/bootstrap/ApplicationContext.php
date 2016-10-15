<?php

namespace MoodValue\Behat;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use MoodValue\Model\User\Command\RegisterUser;
use MoodValue\Model\User\Event\UserWasRegistered;
use MoodValue\Model\User\UserId;
use MoodValue\Tests\Util\EventChecker;
use PHPUnit_Framework_Assert as Assert;

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
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var \Prooph\ServiceBus\EventBus
     */
    private $eventBus;

    /**
     * @var \Prooph\ServiceBus\CommandBus
     */
    private $commandBus;

    /**
     * Initializes context
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
        $kernel = new \AppKernel('dev', false);
        $kernel->boot();

        $this->container = $kernel->getContainer();
        $this->eventBus = $this->container->get('prooph_service_bus.moodvalue_event_bus');
        $this->commandBus = $this->container->get('prooph_service_bus.moodvalue_command_bus');
    }

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
        $this->startCollectingEventsFromBus($this->eventBus);

        try {
            $this->commandBus->dispatch(
                RegisterUser::withData(
                    UserId::generate()->toString(),
                    $this->userEmail,
                    $this->userDeviceToken
                )
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
        Assert::assertCount(1, $this->events);

        Assert::assertInstanceOf(UserWasRegistered::class, $this->events[0]);

        $expectedPayload = [
            'email' => $this->userEmail,
            'device_token' => $this->userDeviceToken,
            'created_at' => (new \DateTimeImmutable())->format(DATE_ISO8601)
        ];

        Assert::assertSame($expectedPayload, $this->events[0]->payload());
    }

    /**
     * @Then My registration should be rejected with the message :message
     */
    public function iShouldNotBeRegistered(string $message)
    {
        Assert::assertEquals($message, $this->thrownException->getPrevious()->getMessage());
    }
}
