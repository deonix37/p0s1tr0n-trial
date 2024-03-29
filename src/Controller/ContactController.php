<?php

namespace App\Controller;

use App\Entity\ContactMessage;
use App\Form\ContactMessageType;
use App\Service\RecaptchaService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    public function __construct(
        private MailerInterface $mailer,
        private RecaptchaService $recaptchaService,
        private ParameterBagInterface $config
    )
    {
    }

    #[Route('/contact', name: 'contact')]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response
    {
        $form = $this->createForm(
            ContactMessageType::class,
            new ContactMessage()
        );
        $form->handleRequest($request);

        if (
            $form->isSubmitted()
            && $form->isValid()
            && $this->validateCaptcha($form, $request)
        ) {
            $message = $form->getData();

            $entityManager->persist($message);
            $entityManager->flush();

            $this->sendEmail($form, $request);
            $this->addFlash('message', 'Success!');

            return $this->redirect($request->getRequestUri());
        }

        return $this->render('contact.html.twig', [
            'form' => $form,
        ]);
    }

    private function validateCaptcha(Form $form, Request $request): bool
    {
        $response = $this->recaptchaService
            ->validateResponse($request);

        if (!$response['success']) {
            $error = $response['error-codes'][0] ?? 'Recaptcha error';
            $form->get('captcha')->addError(new FormError($error));
        }

        return $response['success'];
    }

    private function sendEmail(Form $form, Request $request): void
    {
        $sender = $this->config->get('app.contact_sender_email');
        $receiver = $this->config->get('app.contact_receiver_email');

        if (!$sender || !$receiver) {
            return;
        }

        $email = (new Email())
            ->from($sender)
            ->to($receiver)
            ->subject('New message from ' . $request->getHost())
            ->text('New contact message: ' . implode(' | ', [
                $form->get('email')->getData(),
                $form->get('message')->getData(),
            ]));

        $this->mailer->send($email);
    }
}
