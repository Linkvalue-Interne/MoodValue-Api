<?php

namespace MoodValue\Model\User;

final class DeviceToken
{
    private $token;

    private function __construct() {}

    public static function fromString(string $deviceTokenValue) : DeviceToken
    {
        if (strlen($deviceTokenValue) < 16) {
            throw new \InvalidArgumentException('Invalid device token');
        }

        $deviceToken = new self();
        $deviceToken->token = $deviceTokenValue;

        return $deviceToken;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return $this->token;
    }
}
