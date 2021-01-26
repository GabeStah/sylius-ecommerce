<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class MailController extends AbstractController
{
  public function sendEmail($name, \Swift_Mailer $mailer)
  {
    $message = (new \Swift_Message('Hello Email'))
      ->setFrom('test@raritaneng.com')
      ->setTo('gabe@solarixdigital.com', $name)
      ->setBody('You should see me from the profiler!');

    $mailer->send($message);

    // ...
  }
}
