<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240724210511 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE release_publication ADD tag_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE release_publication ADD tag_message TEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE release_publication DROP tag_name');
        $this->addSql('ALTER TABLE release_publication DROP tag_message');
    }
}
