<?php

namespace MoodValue\Projection\User;

use Doctrine\DBAL\Connection;
use MoodValue\Infrastructure\Repository\CollectionResult;
use MoodValue\Model\User\EmailAddress;
use MoodValue\Model\User\UserId;

class UserFinder
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

    public function findOneById(UserId $userId)
    {
        $stmt = $this->connection->prepare(sprintf('SELECT * FROM %s where id = :user_id', self::TABLE_USER));
        $stmt->bindValue('user_id', $userId->toString());
        $stmt->execute();

        return $stmt->fetch();
    }

    public function findOneByEmail(EmailAddress $emailAddress)
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

//    public function addDeviceToken(UserId $userId, DeviceToken $deviceToken) : int
//    {
//        if (!$user = $this->get($userId)) {
//            return 0;
//        }
//
//        $userDeviceTokens = explode(',', $user['device_tokens']);
//
//        if (in_array($deviceToken->toString(), $userDeviceTokens)) {
//            return 0;
//        }
//
//        $userDeviceTokens[] = $deviceToken->toString();
//
//        return $this->connection->update(
//            self::TABLE_USER,
//            [
//                'device_tokens' => implode(',', $userDeviceTokens)
//            ],
//            [
//                'id' => $userId->toString()
//            ]
//        );
//    }
}
