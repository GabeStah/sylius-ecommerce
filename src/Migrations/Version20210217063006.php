<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210217063006 extends AbstractMigration
{
  public function getDescription(): string
  {
    return '';
  }

  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->addSql(
      'CREATE TABLE sylius_channel_settings (id INT AUTO_INCREMENT NOT NULL, channel_id INT DEFAULT NULL, code VARCHAR(255) NOT NULL, enabled TINYINT(1) NOT NULL, value LONGTEXT NOT NULL, INDEX IDX_53BE7DD672F5A1AA (channel_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB'
    );
    $this->addSql(
      'ALTER TABLE sylius_channel_settings ADD CONSTRAINT FK_53BE7DD672F5A1AA FOREIGN KEY (channel_id) REFERENCES sylius_channel (id)'
    );
  }

  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->addSql('DROP TABLE sylius_channel_settings');
  }
}
