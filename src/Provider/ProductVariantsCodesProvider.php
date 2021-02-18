<?php

declare(strict_types=1);

namespace App\Provider;

use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Product\Model\ProductOptionValueInterface;

final class ProductVariantsCodesProvider
{
  public function provideVariantsCodes(
    ProductInterface $product,
    ChannelInterface $channel
  ): array {
    $data = [];

    /** @var ProductVariantInterface $variant */
    foreach ($product->getEnabledVariants() as $variant) {
      $data[] = $this->constructOptionsMap($variant, $channel);
    }

    return $data;
  }

  private function constructOptionsMap(
    ProductVariantInterface $variant,
    ChannelInterface $channel
  ): array {
    $optionMap = [];

    /** @var ProductOptionValueInterface $option */
    foreach ($variant->getOptionValues() as $option) {
      $optionMap[$option->getOptionCode()] = $option->getCode();
    }

    $optionMap['value'] = $variant->getCode();

    return $optionMap;
  }
}
