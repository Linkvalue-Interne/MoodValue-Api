<?php

namespace MoodValue\Behat\Context\Domain\Behaviour;

use MoodValue\Infrastructure\Repository\EventStoreEventRepository;
use MoodValue\Infrastructure\Repository\EventStoreUserRepository;
use MoodValue\Model\Event\Command\AddEvent;
use MoodValue\Model\User\Command\JoinEvent;
use MoodValue\Model\Event\Event;
use MoodValue\Model\Event\Handler\AddEventHandler;
use MoodValue\Model\User\Handler\JoinEventHandler;
use MoodValue\Model\User\Command\RegisterUser;
use MoodValue\Model\User\Handler\RegisterUserHandler;
use MoodValue\Model\User\User;
use Prooph\Common\Event\ProophActionEventEmitter;
use Prooph\EventSourcing\EventStoreIntegration\AggregateTranslator;
use Prooph\EventStore\Adapter\InMemoryAdapter;
use Prooph\EventStore\Aggregate\AggregateType;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\Stream\Stream;
use Prooph\EventStore\Stream\StreamName;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\Plugin\Router\CommandRouter;

trait Prooph
{
    /**
     * @var StreamName
     */
    private $streamName;

    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @var CommandBus
     */
    private $commandBus;

    public function initProoph()
    {
        $this->streamName = new StreamName('moodvalue_test_event_stream');
        $stream = new Stream($this->streamName, new \ArrayIterator());

        $eventStoreAdapter = new InMemoryAdapter();
        $eventStoreAdapter->create($stream);

        $this->eventStore = new EventStore($eventStoreAdapter, new ProophActionEventEmitter());
        $this->eventStore->beginTransaction();

        $userRepository = new EventStoreUserRepository(
            $this->eventStore,
            AggregateType::fromAggregateRootClass(User::class),
            new AggregateTranslator(),
            null,
            $this->streamName
        );
        $eventRepository = new EventStoreEventRepository(
            $this->eventStore,
            AggregateType::fromAggregateRootClass(Event::class),
            new AggregateTranslator(),
            null,
            $this->streamName
        );

        $commandRouter = (new CommandRouter())
            ->route(RegisterUser::class)->to(new RegisterUserHandler($userRepository))
            ->route(AddEvent::class)->to(new AddEventHandler($eventRepository))
            ->route(JoinEvent::class)->to(new JoinEventHandler($userRepository));
        $this->commandBus = new CommandBus();
        $this->commandBus->utilize($commandRouter);
    }
}
