<?php

namespace MoodValue\Model\User;

use MoodValue\Model\User\Event\DeviceTokenWasAdded;
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
     * @var DeviceToken[]
     */
    private $deviceTokens;

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

    /**
     * @return DeviceToken[]
     */
    public function getDeviceTokens() : array
    {
        return $this->deviceTokens;
    }

    public function getCreatedAt() : \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function addDeviceToken(DeviceToken $deviceToken)
    {
        $this->recordThat(DeviceTokenWasAdded::withData($this->userId, $deviceToken));
    }

    public static function fromHistory(\Iterator $historyEvents) : self
    {
        return self::reconstituteFromHistory($historyEvents);
    }

    protected function aggregateId() : string
    {
        return $this->userId->toString();
    }

    protected function whenUserWasRegistered(UserWasRegistered $event)
    {
        $this->userId = $event->userId();
        $this->emailAddress = $event->emailAddress();
        $this->deviceTokens = [$event->deviceToken()];
        $this->createdAt = $event->createdAt();
    }

    protected function whenDeviceTokenWasAdded(DeviceTokenWasAdded $event)
    {
        $this->userId = $event->userId();

        $this->deviceTokens[] = $event->deviceToken();
    }
}
