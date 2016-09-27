<?php

namespace MoodValue\Model\User;

use MoodValue\Model\User\Event\UserWasRegistered;
use Prooph\EventSourcing\AggregateRoot;

class User extends AggregateRoot
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

    public static function registerWithData(
        UserId $userId,
        EmailAddress $emailAddress,
        DeviceToken $deviceToken
    ) : User
    {
        $user = new self();

        $createdAt = new \DateTimeImmutable('now');

        $user->recordThat(UserWasRegistered::withData($userId, $emailAddress, $deviceToken, $createdAt));

        return $user;
    }

    public function getUserId() : UserId
    {
        return $this->userId;
    }

    public function getEmailAddress() : EmailAddress
    {
        return $this->emailAddress;
    }

    public function getDeviceToken() : DeviceToken
    {
        return $this->deviceToken;
    }

    public function getCreatedAt() : \DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @return string representation of the unique identifier of the aggregate root
     */
    protected function aggregateId()
    {
        return $this->userId->toString();
    }

    protected function whenUserWasRegistered(UserWasRegistered $event)
    {
        $this->userId = $event->userId();
        $this->emailAddress = $event->emailAddress();
        $this->deviceToken = $event->deviceToken();
        $this->createdAt = $event->createdAt();
    }
}
