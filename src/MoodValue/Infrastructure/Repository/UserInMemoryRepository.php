<?php

namespace MoodValue\Infrastructure\Repository;

use MoodValue\Model\User\User;
use MoodValue\Model\User\UserId;
use MoodValue\Model\User\UserRepository;

class UserInMemoryRepository implements UserRepository
{
    private $users;

    public function __construct()
    {
        $this->users = [];
    }

    public function add(User $user)
    {
        $this->users[$user->getUserId()->toString()] = $user;
    }

    public function get(UserId $userId) : User
    {
        return $this->users[$userId->toString()];
    }
}
