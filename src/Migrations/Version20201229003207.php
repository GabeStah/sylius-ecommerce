<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201229003207 extends AbstractMigration
{
  public function getDescription(): string
  {
    return '';
  }

  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->addSql(
      'CREATE TABLE Dealer (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, address1 VARCHAR(100) DEFAULT NULL, address2 VARCHAR(100) DEFAULT NULL, city VARCHAR(100) DEFAULT NULL, postal_code VARCHAR(20) DEFAULT NULL, state VARCHAR(100) DEFAULT NULL, country VARCHAR(2) DEFAULT NULL, phone VARCHAR(50) DEFAULT NULL, fax VARCHAR(50) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, url VARCHAR(255) DEFAULT NULL, enabled TINYINT(1) NOT NULL, type SMALLINT DEFAULT NULL, wholesale TINYINT(1) NOT NULL, comment LONGTEXT DEFAULT NULL, latitude VARCHAR(20) DEFAULT NULL, longitude VARCHAR(20) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB'
    );
    $this->addSql(
      'ALTER TABLE sylius_order CHANGE rates rates MEDIUMTEXT DEFAULT NULL'
    );
  }

  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->addSql('DROP TABLE Dealer');
    $this->addSql(
      'ALTER TABLE sylius_order CHANGE rates rates MEDIUMTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`'
    );
  }
}
