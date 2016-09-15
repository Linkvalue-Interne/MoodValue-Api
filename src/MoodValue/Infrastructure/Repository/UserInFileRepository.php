<?php

namespace MoodValue\Infrastructure\Repository;

use MoodValue\Model\User\User;
use MoodValue\Model\User\UserId;
use MoodValue\Model\User\UserRepository;

class UserInFileRepository implements UserRepository
{
    public function __construct($rootDir)
    {
        $this->file = $rootDir . '/../var/user.json';
    }

    public function add(User $user)
    {
        $users = $this->getContent();

        $users[$user->getUserId()->toString()] = [
            $user->getEmailAddress()->toString(),
            $user->getDeviceToken()->toString()
        ];

        $this->putContent($users);
    }

    public function get(UserId $userId)
    {
        $users = $this->getContent();

        if (isset($users[$userId->toString()])) {
            return $users[$userId->toString()];
        }

        return;
    }

    private function getContent()
    {
        if (!file_exists($this->file)) {
            touch($this->file);
        }

        return json_decode(file_get_contents($this->file), true);
    }

    private function putContent($content)
    {
        if (!file_exists($this->file)) {
            touch($this->file);
        }

        file_put_contents($this->file, json_encode($content));
    }
}
