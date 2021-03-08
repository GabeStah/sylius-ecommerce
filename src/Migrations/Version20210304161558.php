<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210304161558 extends AbstractMigration
{
  public function getDescription(): string
  {
    return '';
  }

  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->addSql(
      'CREATE TABLE sylius_file (id INT AUTO_INCREMENT NOT NULL, checksum VARCHAR(255) DEFAULT NULL, created_by INT DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, path VARCHAR(255) DEFAULT NULL, size INT NOT NULL, type VARCHAR(255) NOT NULL, url VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, version INT NOT NULL, UNIQUE INDEX UNIQ_5796D399DE6FDF9A (checksum), UNIQUE INDEX UNIQ_5796D399B548B0F (path), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB'
    );
    $this->addSql(
      'CREATE TABLE sylius_product_file (id INT AUTO_INCREMENT NOT NULL, file_id INT DEFAULT NULL, owner_id INT DEFAULT NULL, enabled TINYINT(1) NOT NULL, INDEX IDX_C5B11C7093CB796C (file_id), INDEX IDX_C5B11C707E3C61F9 (owner_id), UNIQUE INDEX owner_id_file_id (owner_id, file_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB'
    );
    $this->addSql(
      'ALTER TABLE sylius_product_file ADD CONSTRAINT FK_C5B11C7093CB796C FOREIGN KEY (file_id) REFERENCES sylius_file (id) ON DELETE CASCADE'
    );
    $this->addSql(
      'ALTER TABLE sylius_product_file ADD CONSTRAINT FK_C5B11C707E3C61F9 FOREIGN KEY (owner_id) REFERENCES sylius_product (id) ON DELETE CASCADE'
    );
  }

  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->addSql(
      'ALTER TABLE sylius_product_file DROP FOREIGN KEY FK_C5B11C7093CB796C'
    );
    $this->addSql('DROP TABLE sylius_file');
    $this->addSql('DROP TABLE sylius_product_file');
  }
}
