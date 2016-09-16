<?php

namespace MoodValue\Infrastructure\Repository;

use Doctrine\DBAL\Connection;
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
            'device_tokens' => $user->getDeviceToken()->toString()
        ]);
    }

    public function get(UserId $userId)
    {
        $stmt = $this->connection->prepare(sprintf('SELECT * FROM %s where id = :user_id', self::TABLE_USER));
        $stmt->bindValue('user_id', $userId);
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

    public function findAll() : array
    {
        return $this->connection->fetchAll(sprintf('SELECT * FROM %s', self::TABLE_USER));
    }
}
