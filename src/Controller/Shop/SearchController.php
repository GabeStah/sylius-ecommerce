<?php

namespace App\Controller\Shop;

use App\Repository\ProductRepository;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class SearchController extends AbstractController
{
  /**
   * @var LocaleContextInterface
   */
  private $locale;
  /**
   * @var ChannelContextInterface
   */
  private $channelContext;
  /**
   * @var ProductRepository
   */
  private $productRepository;

  public function __construct(
    LocaleContextInterface $locale,
    ChannelContextInterface $channelContext,
    ProductRepository $productRepository
  ) {
    $this->locale = $locale;
    $this->channelContext = $channelContext;
    $this->productRepository = $productRepository;
  }

  /**
   * @Route("/search", name="product_search_results")
   * @param Request $request
   *
   * @return Response
   */
  public function searchAction(Request $request): Response
  {
    $searchTerm = $request->query->get('q');

    $channel = $this->channelContext->getChannel();
    $localeCode = $this->locale->getLocaleCode();

    $products = $this->productRepository->findByTerm(
      $channel,
      $localeCode,
      $searchTerm
    );

    $pagerfanta = new Pagerfanta(new ArrayAdapter($products));

    $pagerfanta->setMaxPerPage(12);

    if (isset($_GET['page'])) {
      $pagerfanta->setCurrentPage($_GET['page']);
    }

    return $this->render(
      '/bundles/SyliusShopBundle/Search/productSearchResults.html.twig',
      [
        'products' => $pagerfanta,
        'count' => $pagerfanta->getNbResults(),
      ]
    );
  }
}
