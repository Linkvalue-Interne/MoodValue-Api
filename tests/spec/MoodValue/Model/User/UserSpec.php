<?php

namespace spec\MoodValue\Model\User;

use MoodValue\Model\User\DeviceToken;
use MoodValue\Model\User\EmailAddress;
use MoodValue\Model\User\Event\UserWasRegistered;
use MoodValue\Model\User\User;
use MoodValue\Model\User\UserId;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UserSpec extends ObjectBehavior
{
    private $userId;

    function let()
    {
        $this->userId = UserId::generate();
        $this->beConstructedThrough('registerWithData', [
            $this->userId,
            EmailAddress::fromString('john.doe@example.com'),
            DeviceToken::fromString(md5('test'))
        ]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(User::class);
        $this->shouldHaveRecorded(UserWasRegistered::class);
    }
}
