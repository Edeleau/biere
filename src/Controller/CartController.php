<?php

namespace App\Controller;

use App\Form\PromoType;
use App\Repository\ProductRepository;
use App\Repository\PromoCodeRepository;
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
    public function index(SessionInterface $session, CartService $cartService, Request $request, PromoCodeRepository $promoCodeRepository): Response
    {
        $percentReduction = null;
        $cart = $session->get('cart', []);
        $form = $this->createForm(PromoType::class, null, [
            'action' => $this->generateUrl('cart'),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $code = $form->getData()->getCode();
            $promoCode = $promoCodeRepository->findOneBy(['code' => $code]);
            if ($promoCode !== null) {
                $percentReduction = $promoCode->getReduction();
            }
            $session->set('promoCode', $code);
        }
        $products = $cartService->getProducts($cart);
        $prices = $cartService->getPrices($products, $percentReduction);

        return $this->render('cart/index.html.twig', [
            'products' => $products,
            'prices' => $prices,
            'form' => $form->createView(),
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
    public function updateCart(SessionInterface $session, CartService $cartService, Request $request, ProductRepository $productRepository)
    {
        $productStock = $productRepository->findOneBy(['id' => intval($request->query->get('id'))])->getStock();
        $cart = $cartService->updateProductCart($request, $session, $productStock);

        $session->set('cart', $cart);

        return $this->redirectToRoute('cart');
    }

    /**
     * @Route("/cart/save", name="saveCart")
     */
    public function saveCart(SessionInterface $session, CartService $cartService, UserRepository $userRepository, PromoCodeRepository $promoCodeRepository): Response
    {
        $errors = [];
        if ($this->getUser() === null) {
            return $this->redirectToRoute('app_login');
        }
        $code = $session->get('promoCode');
        $promoCode = $promoCodeRepository->findOneBy(['code' => $code]);
        if ($promoCode !== null) {
            $percentReduction = $promoCode->getReduction();
        } else {
            $percentReduction = null;
        }
        $entityManager = $this->getDoctrine()->getManager();
        $cart = $session->get('cart', []);
        $products = $cartService->getProducts($cart);
        $prices = $cartService->getPrices($products, $percentReduction);
        $user = $userRepository->findOneBy(['id' => $this->getUser()->getId()]);

        if ($user->getAddress() === null || $user->getCp() === null || $user->getCity() === null) {
            $errors[] = "Veuillez entrer votre adresse complète avant de sauvegarder votre panier.";
        }
        if (!empty($cart) && empty($errors)) {
            $order = $cartService->setOrder($user, $prices);
            foreach ($products as $product) {
                //dd($product['product']->getStock());
                $orderDetails = $cartService->setOrderDetails($product, $order);
                $order->addOrderDetail($orderDetails);
                $product['product']->setStock($product['product']->getStock() - $product['quantite']);
                $entityManager->persist($orderDetails);
                $entityManager->persist($product['product']);
                //$entityManager->flush();
            }
            $entityManager->persist($order);
            $entityManager->flush();
            $session->remove('cart');
            $this->addFlash('success', 'Commande validé !');
        }
        foreach ($errors as $error => $message) {
            $this->addFlash('error', $message);
        }

        return $this->redirectToRoute('cart');
    }
}
