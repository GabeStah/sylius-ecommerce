<?php

declare(strict_types=1);

namespace App\Form\Type\Admin\Product;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\FileType as BuilderFileType;
use Symfony\Component\Form\FormBuilderInterface;

final class ProductFileType extends AbstractResourceType
{
  public function buildForm(FormBuilderInterface $builder, array $options): void {
    $builder->add('file', BuilderFileType::class, [
      'label' => 'File',
    ]);
  }

  public function getName(): string {
    return $this->getBlockPrefix();
  }

  public function getBlockPrefix(): string {
    return 'app_admin_product_file_type';
  }
}
