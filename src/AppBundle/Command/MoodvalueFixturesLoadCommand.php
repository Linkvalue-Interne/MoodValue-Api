<?php

namespace AppBundle\Command;

use MoodValue\Model\Event\Command\AddEvent;
use MoodValue\Model\Event\Command\AddUserToEvent;
use MoodValue\Model\Event\EventId;
use MoodValue\Model\User\Command\AddDeviceTokenToUser;
use MoodValue\Model\User\Command\RegisterUser;
use MoodValue\Model\User\UserId;
use Prooph\ServiceBus\CommandBus;
use Rhumsaa\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MoodvalueFixturesLoadCommand extends ContainerAwareCommand
{
    /**
     * @var EventId[]
     */
    private $eventIds = [];

    protected function configure()
    {
        $this
            ->setName('moodvalue:fixtures:load')
            ->setDescription('Load fixtures');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Loading fixtures...');
        $this->loadEvents($output);
        $this->loadUsers($output);
    }

    protected function loadEvents(OutputInterface $output)
    {
        $nbAddedEvents = 0;

        for ($i = 1; $i <= 100; $i++) {
            $this->getCommandBus()->dispatch(AddEvent::withData(
                $eventId = EventId::generate()->toString(),
                'name' . $i,
                'text' . $i,
                (new \DateTimeImmutable())->format(\DATE_ISO8601),
                (new \DateTimeImmutable('+5 days'))->format(\DATE_ISO8601),
                3,
                true
            ));

            $this->eventIds[] = $eventId;

            $nbAddedEvents++;
        }

        $output->writeln(sprintf('%d events have been added!', $nbAddedEvents));
    }

    protected function loadUsers(OutputInterface $output)
    {
        $nbCreatedUsers = 0;

        for ($i = 1; $i <= 100; $i++) {
            $this->getCommandBus()->dispatch(RegisterUser::withData(
                $userId = UserId::generate()->toString(),
                sprintf('user%s@example.com', uniqid()),
                Uuid::uuid4()->toString()
            ));

            $this->getCommandBus()->dispatch(AddUserToEvent::withData(
                $userId,
                $this->eventIds[array_rand($this->eventIds)]
            ));

            $nbCreatedUsers++;

            // Add second device tokens for some of the users
            if ($i % 2) {
                $this->getCommandBus()->dispatch(AddDeviceTokenToUser::withData(
                    $userId,
                    Uuid::uuid4()->toString()
                ));
            }
        }

        $output->writeln(sprintf('%d users have been created!', $nbCreatedUsers));
    }

    private function getCommandBus() : CommandBus
    {
        return $this->getContainer()->get('prooph_service_bus.moodvalue_command_bus');
    }
}
