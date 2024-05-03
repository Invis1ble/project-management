<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240501223503 extends AbstractMigration
{
    private const string SAGA_STATE_TABLE_NAME = 'saga_state';

    public function getDescription(): string
    {
        return 'Creates Saga State table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable(self::SAGA_STATE_TABLE_NAME);

        $table->addColumn('id', 'guid', ['length' => 36]);
        $table->addColumn('done', 'boolean', ['default' => false]);
        $table->addColumn('sagaId', 'string');
        $table->setPrimaryKey(['id']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable(self::SAGA_STATE_TABLE_NAME);
    }
}
