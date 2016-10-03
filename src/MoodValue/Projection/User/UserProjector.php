<?php

namespace MoodValue\Projection\User;

use Doctrine\DBAL\Connection;
use MoodValue\Model\User\Event\DeviceTokenWasAdded;
use MoodValue\Model\User\Event\UserWasRegistered;

class UserProjector
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var UserFinder
     */
    private $userFinder;

    public function __construct(Connection $connection, UserFinder $userFinder)
    {
        $this->connection = $connection;
        $this->userFinder = $userFinder;
    }

    public function onUserWasRegistered(UserWasRegistered $event)
    {
        $this->connection->insert(
            UserFinder::TABLE_USER,
            [
                'id' => $event->userId()->toString(),
                'email' => $event->emailAddress()->toString(),
                'device_tokens' => $event->deviceToken()->toString(),
                'created_at' => $event->createdAt()->format('Y-m-d H:i:s')
            ]
        );
    }

    public function onDeviceTokenWasAdded(DeviceTokenWasAdded $event)
    {
        $user = $this->userFinder->findOneById($event->userId());

        $userDeviceTokens = explode(',', $user['device_tokens']);

        if (in_array($event->deviceToken()->toString(), $userDeviceTokens)) {
            return;
        }

        $userDeviceTokens[] = $event->deviceToken()->toString();

        $this->connection->update(
            UserFinder::TABLE_USER,
            [
                'device_tokens' => implode(',', $userDeviceTokens)
            ],
            [
                'id' => $event->userId()->toString()
            ]
        );
    }
}
