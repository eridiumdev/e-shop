<?php
namespace App\Controller;

use App\Model\Database\Reader;
use App\Model\Database\Creator;
use App\Model\Database\Deleter;
use App\Model\Database\Updater;

use App\Model\Data\Shipping;

class CheckoutController extends BaseController
{
    private $deliveryId;
    private $paymentId;

    public function __construct()
    {
        parent::__construct();

        if (!empty($userId = $this->getUserId())) {
            $this->deliveryId = Router::getCookie('delivery')[$userId] ?? 1;
            $this->paymentId = Router::getCookie('payment')[$userId] ?? 1;
        } else {
            $this->deliveryId = Router::getCookie('delivery')['guest'] ?? 1;
            $this->paymentId = Router::getCookie('payment')['guest'] ?? 1;
        }
    }

    public function showStepOnePage($curStep = 1)
    {
        try {
            $dbReader = new Reader();
            $userId = $this->getUserId();

            if (!empty($userId)) {
                // user is not a guest
                if ($shipping = $dbReader->getUserShipping($userId)) {
                    $this->addTwigVar('shipping', $shipping);
                }
            } else {
                // user is a guest
                if (!empty($shippingCookie = Router::getCookie('shipping'))) {
                    $shipping = new Shipping(
                        -1,     // shipping does not have a real id
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

        $this->addTwigVar('step', $curStep);
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

        $userId = $this->getUserId();
        if (!empty($userId)) {
            // user is not a guest
            Router::addCookie('checkout-step', ['key' => $userId, 'val' => 2]);

            try {
                $dbCreator = new Creator();
                $dbDeleter = new Deleter();

                // Replace old shipping with new shipping
                $dbDeleter->deleteUserShipping($userId);
                $dbCreator->createUserShipping($userId, $name, $phone, $address);

            } catch (\Exception $e) {
                Logger::log('db', 'error', "Failed to replace user '$userId' shipping", $e);
                $this->flash('danger', 'Database operation failed');
                Router::redirect("/checkout/step-one");
            }
        } else {
            // user is a guest
            Router::addCookie('checkout-step', ['key' => 'guest', 'val' => 2]);
            Router::addCookie('shipping', ['key' => 'name', 'val' => $name]);
            Router::addCookie('shipping', ['key' => 'phone', 'val' => $phone]);
            Router::addCookie('shipping', ['key' => 'address', 'val' => $address]);
        }

        Router::redirect('/checkout/step-two');
    }

    public function showStepTwoPage($curStep = 2)
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

        $this->addTwigVar('selectedDelivery', $this->deliveryId);
        $this->addTwigVar('selectedPayment', $this->paymentId);

        $this->addTwigVar('step', $curStep);
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

        if (!empty($userId = $this->getUserId())) {
            Router::addCookie('checkout-step', ['key' => $userId, 'val' => 3]);
            Router::addCookie('delivery', ['key' => $userId, 'val' => $delivery]);
            Router::addCookie('payment', ['key' => $userId, 'val' => $payment]);
        } else {
            Router::addCookie('checkout-step', ['key' => 'guest', 'val' => 3]);
            Router::addCookie('delivery', ['key' => 'guest', 'val' => $delivery]);
            Router::addCookie('payment', ['key' => 'guest', 'val' => $payment]);
        }

        Router::redirect('/checkout/step-three');
    }

    public function showStepThreePage($curStep = 3)
    {
        try {
            $dbReader = new Reader();
            $userId = $this->getUserId();

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
                        -1,     // shipping does not have a real id
                        $shippingCookie['name'],
                        $shippingCookie['phone'],
                        $shippingCookie['address']
                    );
                    $this->addTwigVar('shipping', $shipping);
                } else {
                    Router::redirect('/checkout/step-one');
                }
            }

            $delivery = $dbReader->getDeliveryById($this->deliveryId);
            $payment = $dbReader->getPaymentById($this->paymentId);

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

        $this->addTwigVar('step', $curStep);
        $this->setTemplate('checkout/checkout-3.twig');
        $this->render();
    }

    public function submitOrder()
    {
        try {
            $dbReader = new Reader();
            $dbCreator = new Creator();

            if (!empty($userId = $this->getUserId())) {
                $shipping = $dbReader->getUserShipping($userId);
            } else {
                // Little anti-ddos check
                if (empty($userId = Router::getCookie('guest')['id'])) {
                    $guestUser = $dbCreator->createUser();
                    $userId = $guestUser->getId();
                }

                if (!empty($userId)) {
                    Router::addCookie('guest', ['key' => 'id', 'val' => $userId]);
                } else {
                    throw new \Exception;
                }

                if (!empty($shippingCookie = Router::getCookie('shipping'))) {
                    $dup = $dbReader->getExactUserShipping(
                        $userId,
                        $shippingCookie['name'],
                        $shippingCookie['phone'],
                        $shippingCookie['address']
                    );

                    // Again, check against duplicates
                    if (!$dup) {
                        $shipping = $dbCreator->createUserShipping(
                            $userId,
                            $shippingCookie['name'],
                            $shippingCookie['phone'],
                            $shippingCookie['address']
                        );
                    } else {
                        $shipping = $dup;
                    }
                }
            }

            if (!$shipping || !$this->deliveryId ||
                !$this->paymentId || !$this->getCart())
            {
                throw new \Exception;
            }

            $dbCreator->createOrder(
                $userId,
                $shipping->getId(),
                $this->deliveryId,
                $this->paymentId,
                $this->getCart()->getItems(),
                date("Y-m-d H:i:s")
            );

            $this->flash('success', 'Your order was submitted!');

            $this->clearCart();

            if ($this->getUserId()) {
                Router::redirect("/account/orders");
                Router::addCookie('checkout-step', ['key' => $userId, 'val' => 1]);
            } else {
                Router::redirect("/");
                Router::addCookie('checkout-step', ['key' => 'guest', 'val' => 1]);
            }
        } catch (\Exception $e) {
            Logger::log('db', 'error', "Failed to submit order", $e);
            $this->flash('danger', 'Database operation failed');
            Router::redirect("/checkout/step-three");
        }
    }

    public function clearCart()
    {
        if ($userId = $this->getUserId()) {
            // Working with database
            try {
                $dbDeleter = new Deleter();
                $dbDeleter->clearCart($userId);

            } catch (\Exception $e) {
                $this->flash('danger', "Could not clear your cart");
                Logger::log('db', 'error', "Failed to clear user's '$userId' cart", $e);
            }
        } else {
            Router::deleteCookie('cart');
        }
    }
}
