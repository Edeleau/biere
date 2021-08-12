<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdministratifController extends AbstractController
{
    /**
     * @Route("/cgv", name="cgv")
     */
    public function cgv(): Response
    {
        return $this->render('administratif/cgv.html.twig');
    }
    /**
     * @Route("/whoweare", name="whoweare")
     */
    public function whoweare(): Response
    {
        return $this->render('administratif/whoweare.html.twig');
    }
    /**
     * @Route("/contact", name="contact")
     */
    public function contact(Request $request): Response
    {
        $form = $this->createForm(ContactFormType::class, null, [
            'action' => $this->generateUrl('contact'),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $message = $form->getData();
            $contact = new Contact;
            $contact->setMail($message->getMail());
            $contact->setName($message->getName());
            $contact->setMessage($message->getMessage());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($contact);
            $entityManager->flush();
            $this->addFlash('success', 'Message envoyé !');
            unset($form);
            $form = $this->createForm(ContactFormType::class, null, [
                'action' => $this->generateUrl('contact'),
                'method' => 'POST',
            ]);
        }
        return $this->render(
            'administratif/contact.html.twig',
            ['form' => $form->createView()]
        );
    }
}
