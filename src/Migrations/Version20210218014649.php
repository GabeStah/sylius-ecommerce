<?php

declare(strict_types=1);

namespace App\Migrations;

use App\Entity\Channel\ChannelSetting;
use App\Repository\ChannelSettingRepository;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210218014649 extends AbstractMigration implements
  ContainerAwareInterface
{
  /**
   * @var ContainerInterface|null
   */
  private $container;

  public function getDescription(): string
  {
    return '';
  }

  public function up(Schema $schema): void
  {
    $channel = $this->container->get('sylius.context.channel')->getChannel();
    $setting = new ChannelSetting();
    $setting
      ->setChannel($channel)
      ->setCode('footer_cookie_consent')
      ->setEnabled(true)
      ->setValue(
        <<<EOF
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/cookieconsent@3/build/cookieconsent.min.css" />
<script src="https://cdn.jsdelivr.net/npm/cookieconsent@3/build/cookieconsent.min.js" data-cfasync="false"></script>
<script>
window.cookieconsent.initialise({
  "palette": {
    "popup": {
      "background": "#000"
    },
    "button": {
      "background": "#f1d600"
    }
  },
  "content": {
    "href": "/en_US/page/privacy-policy"
  }
});
</script>
EOF
      );

    /** @var ChannelSettingRepository $repository */
    $repository = $this->getContainer()->get('app.repository.channel_setting');
    $repository->add($setting);
  }

  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
  }

  /**
   * Sets the container.
   *
   * @param ContainerInterface|null $container
   *
   */
  public function setContainer(ContainerInterface $container = null)
  {
    $this->container = $container;
  }

  public function getContainer(): ContainerInterface
  {
    return $this->container;
  }
}
