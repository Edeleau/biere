<?php

namespace App\Controller;

use App\Data\SearchData;
use App\Entity\Avis;
use App\Form\AvisType;
use App\Form\SearchType;
use App\Form\ProductType;
use App\Repository\AvisRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
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
     * @Route("/bestSeller", name="bestSeller")
     */
    public function bestSeller(ProductRepository $productRepository, Request $request, PaginatorInterface $paginator): Response
    {

        $products = $productRepository->findPopProduct(10);
        $title = 'Nos 10 produits phare du moment !';
        $products = $paginator->paginate(
            $products,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'formShow' => false,
            'title' => $title
        ]);
    }
    /**
     * @Route("/product/show/{id}", name="product_show")
     */
    public function showProduct(ProductRepository $productRepository, UrlGeneratorInterface $urlGenerator, AvisRepository $avisRepository, $id): Response
    {
        $avis = new Avis;
        $product = $productRepository->findOneBy(['id' => $id]);
        if ($product === null || $product->getStock() === 0) {
            return $this->redirectToRoute('home');
        }
        $suggestion = $productRepository->findSuggestion($product->getCategory(), $product->getId());
        $form = $this->createForm(ProductType::class, $product, [
            'action' => $this->generateUrl('addPanier'),
            'method' => 'POST',
        ]);
        $formAvis = $this->createForm(AvisType::class, $avis, [
            'product' => $id,
            'action'  => $this->generateUrl('addAvis'),
            'method'  => 'POST',
        ]);
        $url = $urlGenerator->generate('product_show', ['id' => $product->getId()], UrlGenerator::ABSOLUTE_URL);
        $recentAvis = $avisRepository->findBy(['product' => $product], ['id' => 'ASC'], 5);
        return $this->render('product/product_show.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
            'formAvis' => $formAvis->createView(),
            'suggestion' => $suggestion,
            'description' => false,
            'url' => $url,
            'recentAvis' => $recentAvis
        ]);
    }

    /**
     * @Route("/addAvis", name="addAvis")
     */
    public function addAvis(ProductRepository $productRepository, Request $request, UserRepository $userRepository): Response
    {
        $data = $request->request;
        if ($data->get('avis')['message'] === '' || $data->get('stars') === null) {
            return $this->redirectToRoute('product_show', ['id' => $data->get('avis')['product']]);
        }
        if ($this->getUser() === null) {
            return $this->redirectToRoute('app_login');
        }
        $avis = new Avis;
        $product = $productRepository->findOneBy(['id' => $data->get('avis')['product']]);
        $user = $userRepository->findOneBy(['id' => $this->getUser()->getId()]);
        $avis->setUser($user);
        $avis->setProduct($product);
        $avis->setMessage($data->get('avis')['message']);
        $avis->setNote($data->get('stars'));

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($avis);
        $entityManager->flush();

        return $this->redirectToRoute('product_show', ['id' => $data->get('avis')['product']]);
    }
}
