<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Increase product meta_description length
 *
 * Class Version20201102084347
 *
 * @package App\Migrations
 */
final class Version20201102084347 extends AbstractMigration
{
  public function getDescription(): string
  {
    return '';
  }

  public function up(Schema $schema): void
  {
    $table = $schema->getTable('sylius_product_translation');
    $table->changeColumn('meta_description', [
      'length' => 510,
    ]);
  }

  public function down(Schema $schema): void
  {
    $table = $schema->getTable('sylius_product_translation');
    $table->changeColumn('meta_description', [
      'length' => 255,
    ]);
  }
}
