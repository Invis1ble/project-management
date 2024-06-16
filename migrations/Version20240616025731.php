<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240616025731 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE hotfix_publication ALTER status TYPE VARCHAR(64)');
        $this->addSql('ALTER TABLE release_publication ALTER status TYPE VARCHAR(64)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE hotfix_publication ALTER status TYPE VARCHAR(32)');
        $this->addSql('ALTER TABLE release_publication ALTER status TYPE VARCHAR(32)');
    }
}
