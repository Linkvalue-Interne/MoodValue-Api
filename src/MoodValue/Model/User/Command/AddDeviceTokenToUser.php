<?php

namespace MoodValue\Model\User\Command;

use MoodValue\Model\User\DeviceToken;
use MoodValue\Model\User\UserId;
use Prooph\Common\Messaging\Command;
use Prooph\Common\Messaging\PayloadConstructable;
use Prooph\Common\Messaging\PayloadTrait;

final class AddDeviceTokenToUser extends Command implements PayloadConstructable
{
    use PayloadTrait;

    public static function withData(string $userId, string $deviceToken)
    {
        return new self(
            [
                'user_id' => $userId,
                'device_token' => $deviceToken
            ]
        );
    }

    public function userId() : UserId
    {
        return UserId::fromString($this->payload['user_id']);
    }

    public function deviceToken() : DeviceToken
    {
        return DeviceToken::fromString($this->payload['device_token']);
    }
}
