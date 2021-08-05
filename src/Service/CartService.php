<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\Orderline;
use App\Entity\OrderDetails;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class CartService
{
    const TVA = 20;

    protected ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function addProductCart(Request $request, Session $session)
    {

        $requestData = $request->request->all()['product'];

        $productId = intVal($requestData['id']);
        //Vérification que la quantité max envoyé est de 20 et que le min est de 0.
        $quantite = (intVal($requestData['quantite']) > 20) ? 20 : ((intVal($requestData['quantite']) < 0) ? 0 : intVal($requestData['quantite']));
        $cart = $session->get('cart', []);
        //Cas où le panier est vide on rentre directement le produit
        if (empty($cart)) {
            $cart[$productId] = ['product' => $productId, 'quantite' => $quantite];
        } else {
            //Cas ou le panier n'est pas vide , on parcours le panier voir si le produit n'est
            //pas déjà existant , si c'est le cas on update seulement la quantité
            if (array_key_exists($productId, $cart)) {
                $qteCart = $cart[$productId]['quantite'];
                //Vérification que la quantité ne dépasse pas la quantité max de 5
                $cart[$productId]['quantite'] = (($qteCart += $quantite) > 20) ? 20 : $qteCart += $quantite;
            } else {
                $cart[$productId] = ['product' => $productId, 'quantite' => $quantite];
            }
        }
        return $cart;
    }

    public function updateProductCart(Request $request, Session $session)
    {
        $productId = intval($request->query->get('id'));
        $action = $request->query->get('action');
        $cart = $session->get('cart', []);

        //Vérification que le produit existe bien dans le panier
        if (array_key_exists($productId, $cart)) {
            $qteCart = $cart[$productId]['quantite'];

            switch ($action) {
                case 'plus':
                    $cart[$productId]['quantite'] = (($qteCart + 1) > 20) ? 20 : $qteCart += 1;
                    break;
                case 'minus':
                    $cart[$productId]['quantite'] = (($qteCart - 1) < 0) ? 0 : $qteCart -= 1;
                    break;
                case 'unset':
                    $cart[$productId]['quantite'] = 0;
                    break;
                default:
                    break;
            }
            //Si la quantité tombe à 0, on supprime le produit du panier
            if ($cart[$productId]['quantite'] <= 0) {
                unset($cart[$productId]);
            }
        }

        return $cart;
    }
    public function getPrices($products)
    {
        $priceBeer = 0;
        $priceGoodies = 0;
        foreach ($products as $product) {
            switch ($product['product']->getClassification()) {
                case Product::CLASSIFICATION_BEER:
                    $priceBeer += ($product['product']->getPrice() * $product['quantite']);
                    break;
                case Product::CLASSIFICATION_GOODIES:
                    $priceGoodies += ($product['product']->getPrice() * $product['quantite']);
                    break;
                default:
                    break;
            }
        }
        $priceBeerWithoutTVA = $this->getPriceWithoutTVA($priceBeer);
        $priceGoodiesWithoutTVA = $this->getPriceWithoutTVA($priceGoodies);
        $totalPrice = $priceGoodies + $priceBeer;
        $totalPriceWithoutTVA = $priceBeerWithoutTVA + $priceGoodiesWithoutTVA;

        return [
            'priceBeer' => $priceBeer,
            'priceBeerWithoutTVA' => $priceBeerWithoutTVA,
            'priceGoodies' => $priceGoodies,
            'priceGoodiesWithoutTVA' => $priceGoodiesWithoutTVA,
            'totalPrice' => $totalPrice,
            'totalPriceWithoutTVA' => $totalPriceWithoutTVA,
        ];
    }
    public function getPriceWithoutTVA($price)
    {
        $priceWithoutTVA = round(($price * 100) / (100 + self::TVA), 2);
        return ($price - $priceWithoutTVA);
    }

    public function getProducts($cart)
    {
        $products = [];
        foreach ($cart as $productId => $value) {
            $products[] = ['product' => $this->productRepository->findOneBy(['id' =>  $productId]), 'quantite' => $value['quantite']];
        }
        return $products;
    }

    public function setOrder(User $user, $prices)
    {
        $order = new Order;
        $order->setUser($user);
        $order->setPrice($prices['totalPrice']);
        $order->setStatus(Order::PAYMENT_PENDING);

        return $order;
    }

    public function setOrderDetails($product, Order $order)
    {
        $orderDetails = new OrderDetails;
        $orderDetails->setProduct($product['product']);
        $orderDetails->setQuantity($product['quantite']);
        $orderDetails->setPrice($product['product']->getPrice() * $product['quantite']);
        $orderDetails->setOrdered($order);

        return $orderDetails;
    }
}
