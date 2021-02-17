<?php

namespace App\Twig;

use App\Repository\ChannelSettingRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Templating\EngineInterface;

class SettingExtension extends \Twig_Extension
{
  /**
   * @var ContainerInterface
   */
  private $container;
  /**
   * @var ChannelSettingRepository
   */
  private $channelSettingRepository;
  /**
   * @var EngineInterface
   */
  private $templatingEngine;

  public function __construct(
    ChannelSettingRepository $channelSettingRepository,
    ContainerInterface $containerInterface,
    EngineInterface $templatingEngine
  ) {
    $this->channelSettingRepository = $channelSettingRepository;
    $this->container = $containerInterface;
    $this->templatingEngine = $templatingEngine;
  }

  public function getFunctions(): array
  {
    return [
      new \Twig_Function(
        'channel_setting',
        [$this, 'renderBlock'],
        ['is_safe' => ['html']]
      ),
    ];
  }

  public function renderBlock(string $code, ?string $template = null): string
  {
    $channel = $this->container->get('sylius.context.channel')->getChannel();
    $setting = $this->channelSettingRepository->findOneByCode($code, $channel);

    if (null !== $setting) {
      return (string) html_entity_decode(
        (string) $setting->getValue(),
        ENT_QUOTES
      );
    }

    return '';
  }
}
