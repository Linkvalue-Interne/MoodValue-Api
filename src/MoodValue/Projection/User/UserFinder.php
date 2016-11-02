<?php

namespace MoodValue\Projection\User;

use Doctrine\DBAL\Connection;
use MoodValue\Infrastructure\Repository\CollectionResult;

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

    public function findOneById(string $userId) : array
    {
        $stmt = $this->connection->prepare(sprintf('SELECT * FROM %s WHERE id = :user_id LIMIT 1', self::TABLE_USER));
        $stmt->bindValue('user_id', $userId);
        $stmt->execute();

        return $stmt->fetch();
    }

    public function findOneByEmail(string $emailAddress) : array
    {
        $stmt = $this->connection->prepare(sprintf('SELECT * FROM %s WHERE email = :email LIMIT 1', self::TABLE_USER));
        $stmt->bindValue('email', $emailAddress);
        $stmt->execute();

        return $stmt->fetch();
    }

    public function emailExists(string $emailAddress) : bool
    {
        $stmt = $this->connection->prepare(sprintf('SELECT COUNT(*) FROM %s WHERE email = :email', self::TABLE_USER));
        $stmt->bindValue('email', $emailAddress);
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
}
