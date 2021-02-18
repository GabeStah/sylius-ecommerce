<?php

declare(strict_types=1);

namespace App\Templating\Helper;

use App\Provider\ProductVariantsCodesProvider;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Provider\ProductVariantsPricesProviderInterface;
use Symfony\Component\Templating\Helper\Helper;

class ProductVariantsCodesHelper extends Helper
{
  /** @var ProductVariantsPricesProviderInterface */
  private $productVariantsCodesProvider;

  public function __construct(
    ProductVariantsCodesProvider $productVariantsCodesProvider
  ) {
    $this->productVariantsCodesProvider = $productVariantsCodesProvider;
  }

  public function getCodes(
    ProductInterface $product,
    ChannelInterface $channel
  ): array {
    return $this->productVariantsCodesProvider->provideVariantsCodes(
      $product,
      $channel
    );
  }

  public function getName(): string
  {
    return 'app_product_variants_codes';
  }
}
