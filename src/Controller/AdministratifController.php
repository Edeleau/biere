<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
    public function contact(): Response
    {
        return $this->render('administratif/contact.html.twig');
    }
}
