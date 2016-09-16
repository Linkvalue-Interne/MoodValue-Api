<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use MoodValue\Infrastructure\Repository\UserMysqlRepository;

class Version20160916153012 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $schema->getTable(UserMysqlRepository::TABLE_USER)->addColumn('created_at', Type::DATETIME);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $schema->getTable(UserMysqlRepository::TABLE_USER)->dropColumn('created_at');
    }
}
