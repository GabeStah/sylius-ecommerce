<?php

declare(strict_types=1);

namespace App\Form\Extension;

use Sylius\Bundle\TaxonomyBundle\Form\Type\TaxonType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

final class TaxonTypeExtension extends AbstractTypeExtension
{
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $builder->add('product_visible', CheckboxType::class, [
      'required' => false,
      'label' => 'sylius.form.taxon.product_visible',
    ]);
  }

  public function getExtendedType(): string
  {
    return TaxonType::class;
  }

  public static function getExtendedTypes(): iterable
  {
    return [TaxonType::class];
  }
}
