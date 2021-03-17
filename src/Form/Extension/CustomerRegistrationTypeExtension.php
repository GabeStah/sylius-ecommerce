<?php

declare(strict_types=1);

namespace App\Form\Extension;

use Karser\Recaptcha3Bundle\Form\Recaptcha3Type;
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3;
use Sylius\Bundle\CoreBundle\Form\Type\Customer\CustomerRegistrationType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;

final class CustomerRegistrationTypeExtension extends AbstractTypeExtension
{
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $builder
      ->add('captcha', Recaptcha3Type::class, [
        'constraints' => new Recaptcha3(),
        'action_name' => 'homepage',
      ])
      ->add('firstName', TextType::class, [
        'constraints' => [new Length(['min' => 3, 'max' => '100'])],
        'label' => 'sylius.form.customer.first_name',
        'sanitize_html' => true,
        'trim' => true,
      ])
      ->add('lastName', TextType::class, [
        'constraints' => [new Length(['min' => 3, 'max' => '100'])],
        'label' => 'sylius.form.customer.last_name',
        'sanitize_html' => true,
        'trim' => true,
      ])
      ->add('phoneNumber', TextType::class, [
        'constraints' => [new Length(['min' => 3, 'max' => '100'])],
        'required' => false,
        'label' => 'sylius.form.customer.phone_number',
        'sanitize_html' => true,
        'trim' => true,
      ]);

    $transformer = new CallbackTransformer(
      function ($value) {
        return $value;
      },
      function ($value) {
        return $value ? strip_tags($value) : $value;
      }
    );

    $builder->get('firstName')->addModelTransformer($transformer);
    $builder->get('lastName')->addModelTransformer($transformer);
    $builder->get('phoneNumber')->addModelTransformer($transformer);
  }

  public function getBlockPrefix(): string
  {
    return 'sylius_customer_registration';
  }

  public static function getExtendedTypes(): iterable
  {
    return [CustomerRegistrationType::class];
  }
}
