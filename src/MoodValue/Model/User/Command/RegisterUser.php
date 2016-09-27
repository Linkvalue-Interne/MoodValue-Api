<?php

namespace MoodValue\Model\User\Command;

use MoodValue\Model\User\DeviceToken;
use MoodValue\Model\User\EmailAddress;
use MoodValue\Model\User\UserId;
use Prooph\Common\Messaging\Command;
use Prooph\Common\Messaging\PayloadConstructable;
use Prooph\Common\Messaging\PayloadTrait;

final class RegisterUser extends Command implements PayloadConstructable
{
    use PayloadTrait;

    public static function withData(string $userId, string $email, string $deviceToken)
    {
        return new self(
            [
                'user_id' => $userId,
                'email' => $email,
                'device_token' => $deviceToken
            ]
        );
    }

    public function userId() : UserId
    {
        return UserId::fromString($this->payload['user_id']);
    }

    public function emailAddress() : EmailAddress
    {
        return EmailAddress::fromString($this->payload['email']);
    }

    public function deviceToken() : DeviceToken
    {
        return DeviceToken::fromString($this->payload['device_token']);
    }
}
