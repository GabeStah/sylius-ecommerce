<?php

declare(strict_types=1);

namespace App\Form\Type\Admin;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\FileType as BuilderFileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;

final class FileType extends AbstractResourceType
{
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $builder
      ->add('file', BuilderFileType::class, [
        'help' => 'Click here to upload a file directly.',
        'help_attr' => ['class' => 'ui pointing label'],
        'label' => 'File',
        'label_attr' => ['class' => 'ui ribbon label'],
        'required' => false,
      ])
      ->add('url', UrlType::class, [
        'help' => 'Alternatively, link to a remote resource or URL.',
        'help_attr' => ['class' => 'ui pointing label'],
        'label_attr' => ['class' => 'ui ribbon label'],
        'required' => false,
      ])
      //      ->add('download', CheckboxType::class, [
      //        'allow_extra_fields' => true,
      //        // Ignores when reading/writing object
      //        'mapped' => false,
      //        'required' => false,
      //      ])
      ->add('title', TextType::class, [
        'help' => 'Add an optional title to display instead of the file name.',
        'help_attr' => ['class' => 'ui pointing label'],
        'label_attr' => ['class' => 'ui ribbon label'],
        'required' => false,
      ]);
  }

  public function getName(): string
  {
    return $this->getBlockPrefix();
  }

  public function getBlockPrefix(): string
  {
    return 'app_admin_file_type';
  }
}
