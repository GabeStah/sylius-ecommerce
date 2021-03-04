<?php

namespace App\Controller\Admin;

use App\Entity\Product\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType as BuilderFileType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ProductFilesTabController extends AbstractController
{
  public function filesAction(Request $request, int $id): Response {
    /** @var Product $product */
    $product = $this->getDoctrine()
                    ->getRepository(Product::class)
                    ->find($id);

    $form = $this->createFormBuilder()
                 ->add('file', BuilderFileType::class, [
                   'label' => 'File',
                 ])
                 ->getForm();

    $form->handleRequest($request);

    return $this->render('Admin/Product/ProductFile/_list.html.twig', [
      'files' => $product->getFiles(),
    ]);
  }
}
