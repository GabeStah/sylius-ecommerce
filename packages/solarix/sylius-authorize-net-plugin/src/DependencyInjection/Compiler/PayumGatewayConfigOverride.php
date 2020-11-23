<?php

namespace Solarix\SyliusAuthorizeNetPlugin\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class PayumGatewayConfigOverride implements CompilerPassInterface
{
  /** @var array */
  private $gatewayConfigs;

  public function __construct(array $gatewayConfigs)
  {
    $this->gatewayConfigs = $gatewayConfigs;
  }

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container): void
  {
    $builder = $container->getDefinition('payum.builder');
    foreach ($this->gatewayConfigs as $gatewayName => $factoryConfig) {
      $builder->addMethodCall('addGatewayFactoryConfig', [
        $gatewayName,
        $factoryConfig,
      ]);
    }
  }
}
