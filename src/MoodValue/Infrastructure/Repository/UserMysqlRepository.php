<?php

namespace MoodValue\Infrastructure\Repository;

use Doctrine\DBAL\Connection;
use MoodValue\Model\User\DeviceToken;
use MoodValue\Model\User\EmailAddress;
use MoodValue\Model\User\User;
use MoodValue\Model\User\UserId;
use MoodValue\Model\User\UserRepository;

class UserMysqlRepository implements UserRepository
{
    const TABLE_USER = 'user';

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->connection->setFetchMode(\PDO::FETCH_ASSOC);
    }

    public function add(User $user) : int
    {
        if ($this->emailExists($user->getEmailAddress())) {
            return 0;
        }

        return $this->connection->insert(self::TABLE_USER, [
            'id' => $user->getUserId()->toString(),
            'email' => $user->getEmailAddress()->toString(),
            'device_tokens' => $user->getDeviceToken()->toString(),
            'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s')
        ]);
    }

    public function get(UserId $userId)
    {
        $stmt = $this->connection->prepare(sprintf('SELECT * FROM %s where id = :user_id', self::TABLE_USER));
        $stmt->bindValue('user_id', $userId->toString());
        $stmt->execute();

        return $stmt->fetch();
    }

    public function getByEmail(EmailAddress $emailAddress)
    {
        $stmt = $this->connection->prepare(sprintf('SELECT * FROM %s where email = :email', self::TABLE_USER));
        $stmt->bindValue('email', $emailAddress->toString());
        $stmt->execute();

        return $stmt->fetch();
    }

    public function emailExists(EmailAddress $emailAddress) : bool
    {
        $stmt = $this->connection->prepare(sprintf('SELECT COUNT(*) FROM %s where email = :email', self::TABLE_USER));
        $stmt->bindValue('email', $emailAddress->toString());
        $stmt->execute();

        return $stmt->fetchColumn() >= 1;
    }

    public function findAll(int $start = 0, int $limit = 10) : CollectionResult
    {
        $results = $this->connection->fetchAll(
            sprintf('SELECT SQL_CALC_FOUND_ROWS * FROM %s LIMIT %d, %d', self::TABLE_USER, $start, $limit)
        );

        $total = $this->connection->fetchColumn('SELECT FOUND_ROWS()');

        return new CollectionResult($results, $total);
    }

    public function addDeviceToken(UserId $userId, DeviceToken $deviceToken) : int
    {
        if (!$user = $this->get($userId)) {
            return 0;
        }

        $userDeviceTokens = explode(',', $user['device_tokens']);
        if (in_array($deviceToken->toString(), $userDeviceTokens)) {
            return 0;
        }

        $userDeviceTokens[] = $deviceToken->toString();
        return $this->connection->update(
            self::TABLE_USER,
            [
                'device_tokens' => implode(',', $userDeviceTokens)
            ],
            [
                'id' => $userId->toString()
            ]
        );
    }
}
