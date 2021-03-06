<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(ProductRepository $productRepository): Response
    {
        $lastProducts = $productRepository->findLastProduct();
        $popProducts = $productRepository->findPopProduct(4);
        return $this->render('home/index.html.twig', [
            'controller_name' => 'Acceuil',
            'lastProducts' => $lastProducts,
            'popProducts' => $popProducts
        ]);
    }
}
