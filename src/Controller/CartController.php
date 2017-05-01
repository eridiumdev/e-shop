<?php
namespace App\Controller;

class CartController extends BaseController
{

    public function showCartPage()
    {
        $this->setTemplate('cart.twig');
        $this->render();
    }
}
