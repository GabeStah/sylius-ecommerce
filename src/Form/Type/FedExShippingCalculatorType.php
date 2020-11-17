<?php

declare(strict_types=1);

namespace App\Form\Type;

use Sylius\Bundle\MoneyBundle\Form\Type\MoneyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

final class FedExShippingCalculatorType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $builder
      ->add('minimum', MoneyType::class, [
        'currency' => 'USD',
      ])
      ->add('code');
  }
}
