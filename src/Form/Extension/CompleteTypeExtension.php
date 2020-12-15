<?php

namespace App\Form\Extension;

use Sylius\Bundle\CoreBundle\Form\Type\Checkout\CompleteType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class CompleteTypeExtension extends AbstractTypeExtension
{
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $builder->add('notes', TextareaType::class, [
      'label' => 'sylius.form.notes',
      'required' => false,
    ]);
  }

  public function getExtendedType(): string
  {
    return CompleteType::class;
  }

  public static function getExtendedTypes(): iterable
  {
    return [CompleteType::class];
  }
}
