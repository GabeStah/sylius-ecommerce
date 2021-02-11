<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210210234939 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'Change cancer warning attribute name.';
  }

  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->addSql(
      <<<EOF
UPDATE sylius_product_attribute_translation AS t
LEFT JOIN sylius_product_attribute a
ON t.translatable_id = a.id
SET t.name = 'Cancer Warning'
WHERE a.code = 'cancer_harm'
EOF
    );
  }

  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->addSql(
      <<<EOF
UPDATE sylius_product_attribute_translation AS t
LEFT JOIN sylius_product_attribute a
ON t.translatable_id = a.id
SET t.name = 'Cancer Harm'
WHERE a.code = 'cancer_harm'
EOF
    );
  }
}
