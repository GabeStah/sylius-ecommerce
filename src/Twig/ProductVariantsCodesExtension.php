<?php

declare(strict_types=1);

namespace App\Twig;

use App\Templating\Helper\ProductVariantsCodesHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class ProductVariantsCodesExtension extends AbstractExtension
{
  /** @var ProductVariantsCodesHelper */
  private $productVariantsCodesHelper;

  public function __construct(
    ProductVariantsCodesHelper $productVariantsCodesHelper
  ) {
    $this->productVariantsCodesHelper = $productVariantsCodesHelper;
  }

  public function getFunctions(): array
  {
    return [
      new TwigFunction('app_product_variant_codes', [
        $this->productVariantsCodesHelper,
        'getCodes',
      ]),
    ];
  }
}
