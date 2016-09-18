<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use MoodValue\Infrastructure\Repository\EventMysqlRepository;

class Version20160918154109 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $userTable = $schema->createTable(EventMysqlRepository::TABLE_EVENT);
        $userTable->addColumn('id', Type::STRING, ['length' => 36]);
        $userTable->addColumn('name', Type::STRING);
        $userTable->addColumn('text', Type::TEXT);
        $userTable->addColumn('start_date', Type::DATETIME);
        $userTable->addColumn('end_date', Type::DATETIME);
        $userTable->addColumn('day_of_week', Type::SMALLINT);
        $userTable->addColumn('mobile_splashscreen', Type::BOOLEAN);
        $userTable->setPrimaryKey(['id']);

        $userTable = $schema->createTable(EventMysqlRepository::TABLE_USER_HAS_EVENT);
        $userTable->addColumn('user_id', Type::STRING, ['length' => 36]);
        $userTable->addColumn('event_id', Type::STRING, ['length' => 36]);
        $userTable->addColumn('joined_at', Type::DATETIME);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $schema->dropTable(EventMysqlRepository::TABLE_EVENT);
        $schema->dropTable(EventMysqlRepository::TABLE_USER_HAS_EVENT);
    }
}
