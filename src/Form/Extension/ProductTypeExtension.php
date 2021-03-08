<?php

declare(strict_types=1);

namespace App\Form\Extension;

use App\Form\DataTransformer\FileToHttpFileTransformer;
use Sylius\Bundle\ProductBundle\Form\Type\ProductType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ProductTypeExtension extends AbstractTypeExtension
{
  private $fileTransformer;

  public function __construct(
    FileToHttpFileTransformer $fileToHttpFileTransformer
  ) {
    $this->fileTransformer = $fileToHttpFileTransformer;
  }

  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    // Generate a collection of form inputs associated with ProductFiles
    //    $builder->add('productFiles', CollectionType::class, [
    //      'entry_type' => BuilderFileType::class,
    //      'required' => false,
    //      'prototype' => true,
    //      'allow_add' => true,
    //      'allow_delete' => true,
    //      'by_reference' => false,
    //      'label' => false,
    //    ]);

    //    $builder->get('productFiles')->addModelTransformer(
    //      $this->fileTransformer
    //    );
  }

  public function configureOptions(OptionsResolver $resolver)
  {
    // Allows for additional file upload input(s)
    $resolver->setDefault('allow_extra_fields', true);
  }

  public function getExtendedType(): string
  {
    return ProductType::class;
  }

  public static function getExtendedTypes(): iterable
  {
    return [ProductType::class];
  }
}
