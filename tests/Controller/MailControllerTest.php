<?php

namespace App\Tests\Controller;

use Swift_SmtpTransport;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MailControllerTest extends WebTestCase
{
  public function testBasic()
  {
    //    $client = static::createClient();

    // enables the profiler for the next request (it does nothing if the profiler is not available)
    //    $client->enableProfiler();

    $this->assertSame(123, 456);
  }

  public function testSendMessage()
  {
    $transport = (new Swift_SmtpTransport('mail.raritaneng.com', 465))
      ->setUsername('noreply@mail.raritaneng.com')
      ->setPassword('DWne0Mqb=CE270qv');

    //    public function sendEmail($name, \Swift_Mailer $mailer)
    //    {
    $message = (new \Swift_Message('Hello Email'))
      ->setFrom('test@raritaneng.com')
      ->setTo('gabe@solarixdigital.com', 'Gabe')
      ->setBody('You should see me from the profiler!');

    (new \Swift_Mailer($transport))->send($message);

    // ...
    //    }
  }

  public function testMailIsSentAndContentIsOk()
  {
    $client = static::createClient();

    // enables the profiler for the next request (it does nothing if the profiler is not available)
    $client->enableProfiler();

    $crawler = $client->request('POST', '/test/mail/send');

    $mailCollector = $client->getProfile()->getCollector('swiftmailer');

    // checks that an email was sent
    $this->assertSame(1, $mailCollector->getMessageCount());

    $collectedMessages = $mailCollector->getMessages();
    $message = $collectedMessages[0];

    // Asserting email data
    $this->assertInstanceOf('Swift_Message', $message);
    $this->assertSame('Hello Email', $message->getSubject());
    $this->assertSame('send@example.com', key($message->getFrom()));
    $this->assertSame('recipient@example.com', key($message->getTo()));
    $this->assertSame(
      'You should see me from the profiler!',
      $message->getBody()
    );
  }
}
