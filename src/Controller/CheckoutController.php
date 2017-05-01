<?php
namespace App\Controller;

class CheckoutController extends BaseController
{

    public function showStepOnePage()
    {
        $this->setTemplate('checkout-1.twig');
        $this->render();
    }
}
