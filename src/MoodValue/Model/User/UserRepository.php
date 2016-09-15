<?php

namespace MoodValue\Model\User;

interface UserRepository
{
    public function add(User $user);

    public function get(UserId $userId);
}
