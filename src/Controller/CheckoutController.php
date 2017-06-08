<?php
namespace App\Controller;

class CheckoutController extends BaseController
{
    public function showStepOnePage()
    {
        $this->addTwigVar('cart', 1);
        $this->setTemplate('checkout/checkout-1.twig');
        $this->render();
    }

    public function showStepTwoPage()
    {
        $this->addTwigVar('cart', 1);
        $delivery = "Deliver your goods using regular post service.";
        $this->addTwigVar('delivery', $delivery);
        $this->addTwigVar('payment', "Pay using your Wechat account.");
        $this->setTemplate('checkout/checkout-2.twig');
        $this->render();
    }

    public function showStepThreePage()
    {
        $this->addTwigVar('cart', 1);
        $this->setTemplate('checkout/checkout-3.twig');
        $this->render();
    }
}
