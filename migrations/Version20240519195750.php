<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240519195750 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE release_publication (id UUID NOT NULL, branch_name VARCHAR(24) NOT NULL, status VARCHAR(32) NOT NULL, ready_to_merge_tasks JSON NOT NULL, created_at TIMESTAMP(6) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_84E5DAE18B8E8428 ON release_publication (created_at)');
        $this->addSql('COMMENT ON COLUMN release_publication.created_at IS \'(DC2Type:utc_datetime_microseconds_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE release_publication');
    }
}
