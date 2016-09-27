<?php

namespace MoodValue\Model\User\Handler;

use MoodValue\Model\User\Command\RegisterUser;
use MoodValue\Model\User\User;
use MoodValue\Model\User\UserRepository;

final class RegisterUserHandler
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function __invoke(RegisterUser $command)
    {
        $user = User::registerWithData($command->userId(), $command->emailAddress(), $command->deviceToken());

        $this->userRepository->add($user);
    }
}
