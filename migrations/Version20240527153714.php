<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240527153714 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE hotfix_publication ADD tag_name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE hotfix_publication ADD tag_message VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE hotfix_publication DROP tag_name');
        $this->addSql('ALTER TABLE hotfix_publication DROP tag_message');
    }
}
