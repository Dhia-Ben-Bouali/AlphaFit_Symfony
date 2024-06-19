<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Bridge\Google\Transport\GmailSmtpTransport;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
class MailerController extends AbstractController
{
    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/email', name: 'email')]
    public function sendEmail(MailerInterface $mailer): Response
    {
        $user=$this->getUser();
        $email = (new Email())
            ->from('legaltechrec@gmail.com')
            ->to('ikramsegni28@gmail.com')
            ->subject('Test Email')
            ->html('Your HTML content here');

        $transport = new GmailSmtpTransport('legaltechrec@gmail.com', 'yxzcrivkamccneif');
        $mailer = new Mailer($transport);
        $mailer->send($email);
        return $this->render('registration/forgotpassword.html.twig');
    }
    // Controller code



}