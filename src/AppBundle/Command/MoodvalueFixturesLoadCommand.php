<?php

namespace AppBundle\Command;

use MoodValue\Model\Event\Event;
use MoodValue\Model\Event\EventId;
use MoodValue\Model\User\DeviceToken;
use MoodValue\Model\User\EmailAddress;
use MoodValue\Model\User\User;
use MoodValue\Model\User\UserId;
use Rhumsaa\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MoodvalueFixturesLoadCommand extends ContainerAwareCommand
{
    private $userIds = [];

    protected function configure()
    {
        $this
            ->setName('moodvalue:fixtures:load')
            ->setDescription('Load fixtures');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Loading fixtures...');

        $this->loadUsers($output);
        $this->loadEvents($output);
    }

    protected function loadUsers(OutputInterface $output)
    {
        $userRepository = $this->getContainer()->get('user.repository');
        $nbCreatedUsers = 0;

        for ($i = 1; $i <= 100; $i++) {
            $userId = UserId::generate();

            $user = User::create(
                $userId,
                EmailAddress::fromString(sprintf('user%s@example.com', uniqid())),
                DeviceToken::fromString(Uuid::uuid4()->toString())
            );

            if ($userRepository->add($user)) {
                $this->userIds[] = $userId;
                $nbCreatedUsers++;
            }

            // Add some device tokens
            if ($i % 2) {
                $userRepository->addDeviceToken(
                    $user->getUserId(),
                    DeviceToken::fromString(Uuid::uuid4()->toString())
                );
            }
        }

        $output->writeln(sprintf('%d users have been created!', $nbCreatedUsers));
    }

    private function loadEvents($output)
    {
        $eventRepository = $this->getContainer()->get('event.repository');
        $nbCreatedEvents = 0;

        for ($i = 1; $i <= 100; $i++) {
            $event = Event::create(
                EventId::generate(),
                'Test ' . $i,
                'How do you feel?',
                new \DateTimeImmutable('now'),
                new \DateTimeImmutable('+5 days'),
                2,
                false
            );

            $userId = $this->userIds[array_rand($this->userIds, 1)];
            $event->add($userId);

            if ($eventRepository->add($event)) {
                $nbCreatedEvents++;
            }
        }

        $output->writeln(sprintf('%d events have been created!', $nbCreatedEvents));
    }
}
