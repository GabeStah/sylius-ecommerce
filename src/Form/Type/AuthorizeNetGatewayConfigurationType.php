<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class AuthorizeNetGatewayConfigurationType extends AbstractType
{
  /**
   * {@inheritdoc}
   */
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $builder
      ->add('login_id', TextType::class, [
        'label' => 'Login ID',
        'constraints' => [
          new NotBlank([
            'message' => 'Login ID must be specified',
            'groups' => 'sylius',
          ]),
        ],
      ])
      ->add('gateway_id', TextType::class, [
        'label' => 'Gateway ID',
        'constraints' => [
          new NotBlank([
            'message' => 'Gateway ID must be specified',
            'groups' => 'sylius',
          ]),
        ],
      ])
      ->add('transaction_key', TextType::class, [
        'label' => 'Transaction ID',
        'constraints' => [
          new NotBlank([
            'message' => 'Transaction ID must be specified',
            'groups' => 'sylius',
          ]),
        ],
      ])
      //      ->add('auto_capture', ChoiceType::class, [
      //        'label' =>
      //          'Auto Capture',
      //        'choices' => [
      //          'solarix_sylius_authorize_net.form.gateway_configuration.authorize_net.no' => 0,
      //          'solarix_sylius_authorize_net.form.gateway_configuration.authorize_net.yes' => 1,
      //        ],
      //      ])
      ->add('use_authorize', HiddenType::class, [
        'data' => true,
      ]);
  }
}
