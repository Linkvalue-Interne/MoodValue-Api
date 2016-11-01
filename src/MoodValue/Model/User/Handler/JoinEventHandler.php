<?php

namespace MoodValue\Model\User\Handler;

use MoodValue\Model\User\Command\JoinEvent;
use MoodValue\Model\User\UserRepository;

final class JoinEventHandler
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function __invoke(JoinEvent $command)
    {
        $user = $this->userRepository->get($command->userId());

        $user->join($command->eventId());

        $this->userRepository->add($user); // @todo rm ?
    }
}
