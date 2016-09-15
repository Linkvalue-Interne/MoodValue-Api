<?php

namespace MoodValue\Model\User;

final class EmailAddress
{
    private $email;

    private function __construct() {}

    public static function fromString(string $email) : EmailAddress
    {
        $filteredEmail = filter_var($email, FILTER_VALIDATE_EMAIL);

        if ($filteredEmail === false) {
            throw new \InvalidArgumentException('Invalid email address');
        }

        $emailAddress = new self();
        $emailAddress->email = $filteredEmail;

        return $emailAddress;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return $this->email;
    }

    public function sameValueAs(EmailAddress $other)
    {
        return $this->toString() === $other->toString();
    }
}
