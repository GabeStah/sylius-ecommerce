<?php

namespace App\Controller\Shop;

use App\Entity\Dealer;
use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class DealerController extends AbstractController
{
  private function getTypeName(int $type): ?string
  {
    switch ($type) {
      case 1:
        return 'Servicing Dealer';
      case 3:
        return 'International Distributor';
      case 0:
      case 2:
      default:
        return 'Domestic Distributor';
    }
  }

  private function getGeoData()
  {
    $ip = $this->container
      ->get('request_stack')
      ->getCurrentRequest()
      ->getClientIp();

    $ip = '50.53.174.237';

    $client = new Client([
      'timeout' => 5.0,
    ]);

    $response = $client->get('http://www.geoplugin.net/php.gp', [
      'query' => ['ip' => $ip],
    ]);

    return unserialize($response->getBody()->getContents());
  }

  private function getLocalDealers($geoData = null)
  {
    if (!$geoData) {
      return null;
    }

    return $this->getDoctrine()
      ->getRepository(Dealer::class)
      ->findByDistanceFromPoint(
        $geoData['geoplugin_latitude'],
        $geoData['geoplugin_longitude']
      );
  }

  public function index(): Response
  {
    $dealers = $this->getDoctrine()
      ->getRepository(Dealer::class)
      ->findAll();

    $geoData = $this->getGeoData();
    $localDealers = $this->getLocalDealers($geoData);

    return $this->render('bundles/SyliusShopBundle/Dealer/index.html.twig', [
      'dealers' => $dealers,
      'localDealers' => $localDealers,
      'geoData' => $geoData,
      'title' => 'All Dealers',
    ]);
  }

  /**
   * @Route("/dealers/{slug}/{id}", name="sylius_shop_dealer_show")
   * @param string $slug
   * @param int    $id
   *
   * @return Response
   */
  public function show(string $slug, int $id): Response
  {
    $dealer = $this->getDoctrine()
      ->getRepository(Dealer::class)
      ->find($id);

    return $this->render('bundles/SyliusShopBundle/Dealer/show.html.twig', [
      'dealer' => $dealer,
    ]);
  }

  /**
   * @Route("/dealers/type/{type}", name="sylius_shop_dealer_list_by_type")
   * @param int $type
   *
   * @return Response
   */
  public function listByType(int $type): Response
  {
    $dealers = $this->getDoctrine()
      ->getRepository(Dealer::class)
      ->findBy(['type' => $type]);

    $geoData = $this->getGeoData();
    $localDealers = $this->getLocalDealers($geoData);

    return $this->render('bundles/SyliusShopBundle/Dealer/index.html.twig', [
      'dealers' => $dealers,
      'geoData' => $geoData,
      'localDealers' => $localDealers,
      // pluralize
      'title' => $this->getTypeName($type) . 's',
      'typeName' => $this->getTypeName($type),
    ]);
  }
}
