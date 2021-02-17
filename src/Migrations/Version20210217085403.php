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
final class Version20210217085403 extends AbstractMigration implements
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
      ->setCode('header_script')
      ->setEnabled(true)
      ->setValue(
        <<<EOF
<!-- TrafficFuel PowerPixel -->
<script>
var TFConfig = {
"pixel_id":"927432-WJVJ4B4-327702"
};
</script>
<noscript><iframe src="//www.googletagmanager.com/ns.html?id=GTM-WJVJ4B4"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<script type="text/javascript" defer src="https://trafficfuelpixel.s3-us-west-2.amazonaws.com/pixel.js"></script>
<noscript><iframe src="//my.trafficfuel.com/powerpixel.php?pixel_id=927432-WJVJ4B4-327702" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<noscript><iframe src="//www.googletagmanager.com/ns.html?id=GTM-WJVJ4B4"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-WJVJ4B4');</script>
<!-- End of TrafficFuel PowerPixel -->

<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-56797593-1"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-56797593-1');
</script>

<!-- WCASG -->
<script src='https://app.wcasg.com/api/widget/1f85c3f11c44814ea2bbb419e08dd6434f9e' crossorigin='anonymous' defer></script>

<!-- www.clarity.ms -->
<script type="text/javascript">
    (function(c,l,a,r,i,t,y){
        c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
        t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
        y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
    })(window, document, "clarity", "script", "4jc7o4rwke");
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
