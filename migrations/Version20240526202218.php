<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240526202218 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE hotfix_publication (id UUID NOT NULL, status VARCHAR(32) NOT NULL, hotfixes JSON NOT NULL, created_at TIMESTAMP(6) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_15D6F2D8B8E8428 ON hotfix_publication (created_at)');
        $this->addSql('COMMENT ON COLUMN hotfix_publication.created_at IS \'(DC2Type:utc_datetime_microseconds_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE hotfix_publication');
    }
}
