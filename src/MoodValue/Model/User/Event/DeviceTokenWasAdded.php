<?php

namespace MoodValue\Model\User\Event;

use MoodValue\Model\User\DeviceToken;
use MoodValue\Model\User\UserId;
use Prooph\EventSourcing\AggregateChanged;

final class DeviceTokenWasAdded extends AggregateChanged
{
    private $userId;
    private $deviceToken;

    public static function withData(
        UserId $userId,
        DeviceToken $deviceToken
    ) {
        $event = self::occur(
            $userId->toString(),
            [
                'device_token' => $deviceToken->toString()
            ]
        );

        $event->userId = $userId;
        $event->deviceToken = $deviceToken;

        return $event;
    }

    public function userId() : UserId
    {
        if ($this->userId === null) {
            $this->userId = UserId::fromString($this->aggregateId());
        }

        return $this->userId;
    }

    public function deviceToken() : DeviceToken
    {
        if ($this->deviceToken === null) {
            $this->deviceToken = DeviceToken::fromString($this->payload['device_token']);
        }

        return $this->deviceToken;
    }
}
