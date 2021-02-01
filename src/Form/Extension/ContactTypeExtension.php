<?php

declare(strict_types=1);

namespace App\Form\Extension;

use Karser\Recaptcha3Bundle\Form\Recaptcha3Type;
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3;
use Sylius\Bundle\CoreBundle\Form\Type\ContactType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

final class ContactTypeExtension extends AbstractTypeExtension
{
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $builder
      ->add('captcha', Recaptcha3Type::class, [
        'constraints' => new Recaptcha3(),
        'action_name' => 'homepage',
        //        'script_nonce_csp' => $nonceCSP,
      ])
      ->add('department', ChoiceType::class, [
        'label' => 'Department',
        'choices' => [
          'Sales' => 'sales',
          'Technical Support' => 'support',
        ],
        'expanded' => true,
        'multiple' => false,
        'required' => true,
      ])
      ->add('full_name', TextType::class, [
        'label' => 'Full Name',
        'constraints' => [
          new NotBlank([
            'message' => 'Name required',
          ]),
        ],
        'required' => true,
      ])
      ->add('phone', TelType::class, [
        'label' => 'Phone',
        'required' => false,
      ])
      ->add('subject', TextType::class, [
        'label' => 'Subject',
        'required' => false,
      ]);
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([
      'department' => 'sales',
    ]);
  }

  public function getBlockPrefix(): string
  {
    return 'sylius_contact';
  }

  public static function getExtendedTypes(): iterable
  {
    return [ContactType::class];
  }
}
