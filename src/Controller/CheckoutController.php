<?php
namespace App\Controller;

use App\Model\Database\Reader;
use App\Model\Database\Creator;
use App\Model\Database\Deleter;

use App\Model\Data\Shipping;

class CheckoutController extends BaseController
{
    public function showStepOnePage()
    {
        try {
            $dbReader = new Reader();
            $userId = Security::getUserId();

            if (!empty($userId)) {
                // user is not a guest
                if ($shipping = $dbReader->getUserShipping($userId)) {
                    $this->addTwigVar('shipping', $shipping);
                }
            } else {
                // user is a guest
                if (!empty($shippingCookie = Router::getCookie('shipping'))) {
                    $shipping = new Shipping(
                        $shippingCookie['name'],
                        $shippingCookie['phone'],
                        $shippingCookie['address']
                    );
                    $this->addTwigVar('shipping', $shipping);
                }
            }
        } catch (\Exception $e) {
            $this->flash('danger', "Something went wrong, try again later");
            Logger::log('db', 'error', "Failed to get user '$userId' shipping'", $e);
            Router::redirect('/');
        }

        $this->addTwigVar('step', $step = 1);
        $this->setTemplate('checkout/checkout-1.twig');
        $this->render();
    }

    public function addShipping(array $post)
    {
        if (!$this->isClean($post)) {
            $this->flash('danger', 'Invalid input data');
            Router::redirect("/checkout/step-one");
        }

        $name = $post['name'];
        $phone = $post['phone'];
        $address = $post['address'];

        $userId = Security::getUserId();
        if (!empty($userId)) {
            // user is not a guest
            try {
                $dbCreator = new Creator();
                $dbDeleter = new Deleter();

                // Replace old shipping with new shipping
                $dbDeleter = deleteUserShipping($userId);
                $dbCreator = createUserShipping($userId, $name, $phone, $address);

            } catch (\Exception $e) {
                Logger::log('db', 'error', "Failed to replace user '$userId' shipping", $e);
                $this->flash('danger', 'Database operation failed');
                Router::redirect("/checkout/step-one");
            }
        } else {
            // user is a guest
            Router::addCookie('shipping', ['key' => 'name', 'val' => $name]);
            Router::addCookie('shipping', ['key' => 'phone', 'val' => $phone]);
            Router::addCookie('shipping', ['key' => 'address', 'val' => $address]);
        }

        Router::redirect('/checkout/step-two');
    }

    public function showStepTwoPage()
    {
        try {
            $dbReader = new Reader();
            $deliveries = $dbReader->getAllDeliveries();
            $payments = $dbReader->getAllPayments();

        } catch (\Exception $e) {
            Logger::log('db', 'error', "Failed to get deliveries or payments", $e);
            $this->flash('danger', 'Database operation failed');
            Router::redirect("/checkout/step-one");
        }

        if (!empty($deliveryCookie = Router::getCookie('delivery'))) {
            $selectedDelivery = $deliveryCookie['id'];
            $this->addTwigVar('selectedDelivery', $selectedDelivery);
        }

        if (!empty($paymentCookie = Router::getCookie('payment'))) {
            $selectedPayment = $paymentCookie['id'];
            $this->addTwigVar('selectedPayment', $selectedPayment);
        }

        $this->addTwigVar('step', $step = 2);
        $this->addTwigVar('deliveries', $deliveries);
        $this->addTwigVar('payments', $payments);

        $this->setTemplate('checkout/checkout-2.twig');
        $this->render();
    }

    public function prepareOrder(array $post)
    {
        if (!$this->isClean($post)) {
            $this->flash('danger', 'Invalid input data');
            Router::redirect("/checkout/step-one");
        }

        $delivery = $post['delivery'];
        $payment = $post['payment'];

        Router::addCookie('delivery', ['key' => 'id', 'val' => $delivery]);
        Router::addCookie('payment', ['key' => 'id', 'val' => $payment]);

        Router::redirect('/checkout/step-three');
    }

    public function showStepThreePage()
    {
        try {
            $dbReader = new Reader();
            $userId = Security::getUserId();

            if (!empty($userId)) {
                // user is not a guest
                if ($shipping = $dbReader->getUserShipping($userId)) {
                    $this->addTwigVar('shipping', $shipping);
                } else {
                    Router::redirect('/checkout/step-one');
                }
            } else {
                // user is a guest
                if (!empty($shippingCookie = Router::getCookie('shipping'))) {
                    $shipping = new Shipping(
                        $shippingCookie['name'],
                        $shippingCookie['phone'],
                        $shippingCookie['address']
                    );
                    $this->addTwigVar('shipping', $shipping);
                } else {
                    Router::redirect('/checkout/step-one');
                }
            }

            if (!empty($deliveryCookie = Router::getCookie('delivery'))) {
                $delivery = $dbReader->getDeliveryById($deliveryCookie['id']);
            }

            if (!empty($paymentCookie = Router::getCookie('payment'))) {
                $payment = $dbReader->getPaymentById($paymentCookie['id']);
            }

            if (empty($delivery) || empty($payment)) {
                Router::redirect('/checkout/step-two');
            } else {
                $this->addTwigVar('delivery', $delivery);
                $this->addTwigVar('payment', $payment);
            }

        } catch (\Exception $e) {
            $this->flash('danger', "Something went wrong, try again later");
            Logger::log('db', 'error', "Failed to get user '$userId' shipping", $e);
            Router::redirect('/');
        }

        $this->addTwigVar('step', $step = 3);
        $this->setTemplate('checkout/checkout-3.twig');
        $this->render();
    }
}
