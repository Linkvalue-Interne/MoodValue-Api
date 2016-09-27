<?php

namespace MoodValue\Infrastructure\Repository;

use MoodValue\Model\User\User;
use MoodValue\Model\User\UserId;
use MoodValue\Model\User\UserRepository;
use Prooph\EventStore\Aggregate\AggregateRepository;

final class EventStoreUserRepository extends AggregateRepository implements UserRepository
{
    public function add(User $user)
    {
        $this->addAggregateRoot($user);
    }

    public function get(UserId $userId) : User
    {
        return $this->getAggregateRoot($userId->toString());
    }
}
