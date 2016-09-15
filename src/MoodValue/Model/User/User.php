<?php

namespace MoodValue\Model\User;

class User
{
    /**
     * @var UserId
     */
    private $userId;

    /**
     * @var EmailAddress
     */
    private $emailAddress;

    /**
     * @var DeviceToken
     */
    private $deviceToken;

    public static function create(UserId $userId, EmailAddress $emailAddress, DeviceToken $deviceToken) : User
    {
        $user = new self();

        $user->userId = $userId;
        $user->emailAddress = $emailAddress;
        $user->deviceToken = $deviceToken;

        return $user;
    }

    /**
     * @return UserId
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @return EmailAddress
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    /**
     * @return DeviceToken
     */
    public function getDeviceToken()
    {
        return $this->deviceToken;
    }
}
