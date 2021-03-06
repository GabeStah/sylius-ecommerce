<?php

declare(strict_types=1);

namespace App\Form\Extension;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Sylius\Bundle\ProductBundle\Form\Type\ProductTranslationType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

final class ProductTranslationTypeExtension extends AbstractTypeExtension
{
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $builder
      ->remove('description')
      ->add('description', CKEditorType::class, [
        'required' => false,
        'label' => 'sylius.form.product.description',
        'config_name' => 'default',
      ])
      ->remove('shortDescription')
      ->add('shortDescription', CKEditorType::class, [
        'required' => false,
        'label' => 'sylius.form.product.short_description',
        'config_name' => 'default',
      ])
      ->add('content', CKEditorType::class, [
        'required' => false,
        'label' => 'sylius.form.product.content.title',
        'config_name' => 'default',
      ]);
  }

  public function getExtendedType(): string
  {
    return ProductTranslationType::class;
  }

  public static function getExtendedTypes(): iterable
  {
    return [ProductTranslationType::class];
  }
}
