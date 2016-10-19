<?php

namespace specs\MoodValue\Model\User;

use MoodValue\Model\User\DeviceToken;
use MoodValue\Model\User\EmailAddress;
use MoodValue\Model\User\Event\DeviceTokenWasAdded;
use MoodValue\Model\User\Event\UserWasRegistered;
use MoodValue\Model\User\User;
use MoodValue\Model\User\UserId;
use PhpSpec\ObjectBehavior;
use Prooph\EventSourcing\AggregateRoot;
use Prophecy\Argument;

class UserSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(User::class);
    }

    function it_registers()
    {
        $this->beConstructedThrough('registerWithData', [
            $userId = UserId::generate(),
            $email = EmailAddress::fromString('john.doe@example.com'),
            $deviceToken = DeviceToken::fromString(md5('test'))
        ]);

        $this->shouldHaveRecorded(UserWasRegistered::withData($userId, $email, $deviceToken, new \DateTimeImmutable('now')));
    }

    function it_adds_a_device_token()
    {
        $this->beConstructedThrough('fromHistory', [
            new \ArrayIterator([
                UserWasRegistered::withData(
                    $userId = UserId::generate(),
                    EmailAddress::fromString('john.doe@example.com'),
                    DeviceToken::fromString(md5('test')),
                    new \DateTimeImmutable('5 days ago')
                )
            ])
        ]);
        $this->addDeviceToken($deviceToken = DeviceToken::fromString(md5('second token')));
        $this->shouldHaveRecorded(DeviceTokenWasAdded::withData($userId, $deviceToken));
    }
}
