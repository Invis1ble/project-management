<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240731174317 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE release_publication ALTER status TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE release_publication ALTER tag_name TYPE VARCHAR(16)');
        $this->addSql('ALTER TABLE hotfix_publication ALTER tag_name TYPE VARCHAR(16)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE hotfix_publication ALTER tag_name TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE release_publication ALTER tag_name TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE release_publication ALTER status TYPE VARCHAR(64)');
    }
}
