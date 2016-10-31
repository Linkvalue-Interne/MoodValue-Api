<?php

namespace MoodValue\Model\Event\Handler;

use MoodValue\Model\Event\Command\AddUserToEvent;
use MoodValue\Model\User\UserRepository;

final class AddUserToEventHandler
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function __invoke(AddUserToEvent $command)
    {
        $user = $this->userRepository->get($command->userId());

        $user->join($command->eventId());

        $this->userRepository->add($user); // @todo rm ?
    }
}
