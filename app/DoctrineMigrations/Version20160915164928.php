<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use MoodValue\Infrastructure\Repository\UserMysqlRepository;

class Version20160915164928 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $userTable = $schema->createTable(UserMysqlRepository::TABLE_USER);
        $userTable->addColumn('id', Type::STRING, ['length' => 36]);
        $userTable->addColumn('email', Type::STRING);
        $userTable->addColumn('device_tokens', Type::TEXT);
        $userTable->setPrimaryKey(['id']);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $schema->dropTable(UserMysqlRepository::TABLE_USER);
    }
}
