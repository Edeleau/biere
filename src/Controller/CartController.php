<?php

namespace App\Controller;


use App\Repository\UserRepository;
use App\Service\CartService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class CartController extends AbstractController
{
    /**
     * @Route("/cart", name="cart")
     */
    public function index(SessionInterface $session, CartService $cartService): Response
    {
        $cart = $session->get('cart', []);

        $products = $cartService->getProducts($cart);
        $prices = $cartService->getPrices($products);

        return $this->render('cart/index.html.twig', [
            'products' => $products,
            'prices' => $prices
        ]);
    }
    /**
     * @Route("/cart/add", name="addPanier")
     * @param SessionInterface $session
     * @return string
     */
    public function addCart(SessionInterface $session, CartService $cartService, Request $request)
    {
        $cart = $cartService->addProductCart($request, $session);

        $session->set('cart', $cart);

        return $this->redirectToRoute('cart');
    }

    /**
     * @Route("/cart/update", name="updatePanier")
     * @param SessionInterface $session
     * @return string
     */
    public function updateCart(SessionInterface $session, CartService $cartService, Request $request)
    {
        $cart = $cartService->updateProductCart($request, $session);

        $session->set('cart', $cart);

        return $this->redirectToRoute('cart');
    }

    /**
     * @Route("/cart/save", name="saveCart")
     */
    public function saveCart(SessionInterface $session, CartService $cartService, UserRepository $userRepository): Response
    {
        if ($this->getUser() === null) {
            return $this->redirectToRoute('app_login');
        }
        $entityManager = $this->getDoctrine()->getManager();
        $cart = $session->get('cart', []);
        $products = $cartService->getProducts($cart);
        $prices = $cartService->getPrices($products);
        $user = $userRepository->findOneBy(['id' => $this->getUser()->getId()]);


        if ($cart !== []) {
            $order = $cartService->setOrder($user, $prices);
            foreach ($products as $product) {
                $orderDetails = $cartService->setOrderDetails($product, $order);
                $order->addOrderDetail($orderDetails);
                $entityManager->persist($orderDetails);
            }
            $entityManager->persist($order);
            $entityManager->flush();
            $session->remove('cart');
        }


        return $this->redirectToRoute('cart');
    }
}
