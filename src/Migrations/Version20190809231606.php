<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190809231606 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TABLE wine_order_detail (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, wine_id INTEGER NOT NULL, wine_order_head_id INTEGER NOT NULL, status VARCHAR(50) NOT NULL)');
        $this->addSql('CREATE INDEX IDX_7666376328A2BD76 ON wine_order_detail (wine_id)');
        $this->addSql('CREATE INDEX IDX_7666376349FFF3CA ON wine_order_detail (wine_order_head_id)');
        $this->addSql('CREATE TABLE wine_order_head (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, waiter_id INTEGER NOT NULL, sommelier_id INTEGER DEFAULT NULL, created_at DATETIME NOT NULL, modified_at DATETIME DEFAULT NULL, status VARCHAR(50) NOT NULL)');
        $this->addSql('CREATE INDEX IDX_CE2FE6D1E9F3D07E ON wine_order_head (waiter_id)');
        $this->addSql('CREATE INDEX IDX_CE2FE6D1901A4472 ON wine_order_head (sommelier_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP TABLE wine_order_detail');
        $this->addSql('DROP TABLE wine_order_head');
    }
}
