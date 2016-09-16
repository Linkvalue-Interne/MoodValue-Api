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

    /**
     * @var \DateTimeInterface
     */
    private $createdAt;

    public static function create(
        UserId $userId,
        EmailAddress $emailAddress,
        DeviceToken $deviceToken
    ) : User
    {
        $user = new self();

        $user->userId = $userId;
        $user->emailAddress = $emailAddress;
        $user->deviceToken = $deviceToken;
        $user->createdAt = new \DateTimeImmutable('now');

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

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}
