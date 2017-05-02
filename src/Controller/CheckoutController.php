<?php
namespace App\Controller;

class CheckoutController extends BaseController
{
    public function showStepOnePage()
    {
        $this->setTemplate('checkout/checkout-1.twig');
        $this->render();
    }

    public function showStepTwoPage()
    {
        $delivery = "delvieifisidfisi";
        $this->addTwigVar('delivery', $delivery);
        $this->addTwigVar('payment', "payyenyenememes!");
        $this->setTemplate('checkout/checkout-2.twig');
        $this->render();
    }

    public function showStepThreePage()
    {
        $this->setTemplate('checkout/checkout-3.twig');
        $this->render();
    }
}
