<?php

namespace MoodValue\Model\User\Event;

use MoodValue\Model\User\DeviceToken;
use MoodValue\Model\User\EmailAddress;
use MoodValue\Model\User\UserId;
use Prooph\EventSourcing\AggregateChanged;

final class UserWasRegistered extends AggregateChanged
{
    private $userId;

    private $emailAddress;

    private $deviceToken;

    public static function withData(
        UserId $userId,
        EmailAddress $emailAddress,
        DeviceToken $deviceToken,
        \DateTimeInterface $createdAt
    ) : UserWasRegistered
    {
        $event = self::occur(
            $userId->toString(),
            [
                'email' => $emailAddress->toString(),
                'device_token' => $deviceToken->toString(),
                'created_at' => $createdAt->format(\DateTime::ISO8601)
            ]
        );

        $event->userId = $userId;
        $event->emailAddress = $emailAddress;
        $event->deviceToken = $deviceToken;
        $event->createdAt = $createdAt;

        return $event;
    }

    public function userId() : UserId
    {
        if ($this->userId === null) {
            $this->userId = UserId::fromString($this->aggregateId());
        }

        return $this->userId;
    }

    public function emailAddress() : EmailAddress
    {
        if ($this->emailAddress === null) {
            $this->emailAddress = EmailAddress::fromString($this->payload['email']);
        }

        return $this->emailAddress;
    }

    public function deviceToken() : DeviceToken
    {
        if ($this->deviceToken === null) {
            $this->deviceToken = DeviceToken::fromString($this->payload['device_token']);
        }

        return $this->deviceToken;
    }

    public function createdAt() : \DateTimeInterface
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTimeImmutable($this->payload['created_at']);
        }

        return $this->createdAt;
    }
}