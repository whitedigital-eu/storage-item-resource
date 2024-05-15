<?php

declare(strict_types=1);

namespace WhiteDigital\StorageItemResource\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240515144134 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Alter any existing storage_item table to use whitedigital schema';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA IF NOT EXISTS whitedigital');
        $this->addSql('ALTER TABLE IF EXISTS storage_item SET SCHEMA whitedigital');
    }

    public function down(Schema $schema): void
    {
    }
}
