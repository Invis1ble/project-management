<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240928142119 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE release_publication RENAME ready_to_merge_tasks TO tasks');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE release_publication RENAME tasks TO ready_to_merge_tasks');
    }
}
