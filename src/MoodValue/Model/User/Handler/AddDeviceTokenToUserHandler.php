<?php

namespace MoodValue\Model\User\Handler;

use MoodValue\Model\User\Command\AddDeviceTokenToUser;
use MoodValue\Model\User\UserRepository;

final class AddDeviceTokenToUserHandler
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function __invoke(AddDeviceTokenToUser $command)
    {
        $user = $this->userRepository->get($command->userId());
        $user->addDeviceToken($command->deviceToken());
    }
}
