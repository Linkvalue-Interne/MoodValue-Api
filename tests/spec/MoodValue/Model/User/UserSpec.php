<?php

namespace spec\MoodValue\Model\User;

use MoodValue\Model\User\User;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UserSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(User::class);
    }
}
