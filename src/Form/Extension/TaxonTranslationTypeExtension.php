<?php

declare(strict_types=1);

namespace App\Form\Extension;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Sylius\Bundle\TaxonomyBundle\Form\Type\TaxonTranslationType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

final class TaxonTranslationTypeExtension extends AbstractTypeExtension
{
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $builder->remove('description')->add('description', CKEditorType::class, [
      'required' => false,
      'label' => 'sylius.form.taxon.description',
    ]);
  }

  public function getExtendedType(): string
  {
    return TaxonTranslationType::class;
  }

  public static function getExtendedTypes(): iterable
  {
    return [TaxonTranslationType::class];
  }
}
