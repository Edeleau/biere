<?php

namespace App\Controller;

use App\Data\SearchData;
use App\Form\SearchType;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProductController extends AbstractController
{
    /**
     * @Route("/product/{classification}", name="product")
     */
    public function index(ProductRepository $productRepository, $classification, Request $request, PaginatorInterface $paginator): Response
    {
        $data = new SearchData();
        $form = $this->createForm(SearchType::class, $data, ['classification' => $classification]);
        $form->handleRequest($request);
        $donnees = $productRepository->findSearch($data, $classification);
        $products = $paginator->paginate(
            $donnees,
            $request->query->getInt('page', 1),
            10
        );
        if ($request->get('ajax')) {

            return new JsonResponse([
                'content' => $this->renderView('product/_product-cards.html.twig', ['products' => $products]),
                'pagination' => $this->renderView('product/_pagination.html.twig', ['products' => $products])
            ]);
        }


        return $this->render('product/index.html.twig', [
            'products' => $products,
            'classification' => $classification,
            'form' => $form->createView(),
            'formShow' => true

        ]);
    }

    /**
     * @Route("/search", name="search_product")
     */
    public function search(ProductRepository $productRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $search = $request->query->get("search");
        $donnees = $productRepository->search($search);
        $products = $paginator->paginate(
            $donnees,
            $request->query->getInt('page', 1),
            10
        );


        return $this->render('product/index.html.twig', [
            'products' => $products,
            'formShow' => false,
            'search' => $search

        ]);
    }

    /**
     * @Route("/product/show/{id}", name="product_show")
     */
    public function showProduct(ProductRepository $productRepository, UrlGeneratorInterface $urlGenerator, $id): Response
    {
        $product = $productRepository->findOneBy(['id' => $id]);
        $suggestion = $productRepository->findSuggestion($product->getCategory(), $product->getId());
        $form = $this->createForm(ProductType::class, $product, [
            'action' => $this->generateUrl('addPanier'),
            'method' => 'POST',
        ]);
        $url = $urlGenerator->generate('product_show', ['id' => $product->getId()], UrlGenerator::ABSOLUTE_URL);

        return $this->render('product/product_show.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
            'suggestion' => $suggestion,
            'description' => false,
            'url' => $url
        ]);
    }
}
