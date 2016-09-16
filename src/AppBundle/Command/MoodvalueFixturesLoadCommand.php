<?php

namespace AppBundle\Command;

use MoodValue\Model\User\DeviceToken;
use MoodValue\Model\User\EmailAddress;
use MoodValue\Model\User\User;
use MoodValue\Model\User\UserId;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MoodvalueFixturesLoadCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('moodvalue:fixtures:load')
            ->setDescription('Load fixtures');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Loading fixtures...');

        $userRepository = $this->getContainer()->get('user.repository');
        $nbCreatedUsers = 0;

        for ($i = 1; $i <= 100; $i++) {
            $user = User::create(
                UserId::generate(),
                EmailAddress::fromString(sprintf('user%s@example.com', $i)),
                DeviceToken::fromString(Uuid::uuid4()->toString())
            );

            if ($userRepository->add($user)) {
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
}
