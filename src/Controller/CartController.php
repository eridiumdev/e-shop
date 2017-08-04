<?php
namespace App\Controller;

class CartController extends BaseController
{
    public function showCartPage()
    {
        $this->setTemplate('cart.twig');
        $this->render();
    }

    public function addToCart(int $prodId)
    {
        Router::addCookie('cart', ['key' => $prodId, 'val' => 1]);
        Router::redirect('/cart');
    }

    public function removeFromCart(int $prodId)
    {
        Router::deleteCookie('cart', ['key' => $prodId]);
        Router::redirect('/cart');
    }

    public function updateCart(array $post)
    {
        $array = $post['qty'] ?? [];
        foreach ($array as $prodId => $qty) {
            if ($qty > 0) {
                Router::updateCookie(
                    'cart',
                    ['key' => $prodId, 'val' => $qty]
                );
            } else {
                Router::deleteCookie('cart', ['key' => $prodId]);
            }
        }
        Router::redirect('/cart');
    }
}
