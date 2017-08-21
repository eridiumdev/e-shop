<?php
namespace App\Controller;

use App\Model\Database\Reader;
use App\Model\Database\Creator;
use App\Model\Database\Updater;
use App\Model\Database\Deleter;

class CartController extends BaseController
{
    public function showCartPage()
    {
        $this->setTemplate('cart.twig');
        $this->render();
    }

    public function addToCart(int $prodId, $qty = 1)
    {
        if ($userId = $this->getUserId()) {
            // Working with database

            try {
                // Check if item is already in the cart
                $dbReader = new Reader();
                $item = $dbReader->getCartItem($userId, $prodId);

                if (!empty($item)) {
                    // Update the qty
                    $dbUpdater = new Updater();
                    $dbUpdater->updateCartItem(
                        $userId,
                        $prodId,
                        $item->getQty() + $qty
                    );
                } else {
                    $dbCreator = new Creator();
                    $dbCreator->addCartItem($userId, $prodId, $qty);
                }
            } catch (\Exception $e) {
                $this->flash('danger', "Something went wrong, try again later");
                Logger::log(
                    'db', 'error',
                    "Failed to update item '$prodId' in user's '$userId' cart",
                    $e
                );
                Router::redirect('/cart');
            }
        } else {
            // Working with cookies
            if ($cookie = Router::getCookie('cart')) {
                if (isset($cookie[$prodId])) {
                    $qty += $cookie[$prodId];
                }
            }
            Router::addCookie('cart', ['key' => $prodId, 'val' => $qty]);
        }

        Router::redirect('/cart');
    }

    public function removeFromCart(int $prodId)
    {
        if ($userId = $this->getUserId()) {
            // Working with database
            try {
                $dbDeleter = new Deleter();
                $dbDeleter->removeCartItem($userId, $prodId);

            } catch (\Exception $e) {
                $this->flash('danger', "Something went wrong, try again later");
                Logger::log(
                    'db', 'error',
                    "Failed to remove item '$prodId' from user's '$userId' cart",
                    $e
                );
                // Router::redirect('/cart');
            }
        } else {
            Router::deleteCookie('cart', ['key' => $prodId]);
        }

        Router::redirect('/cart');
    }

    public function updateCart(array $post)
    {
        $array = $post['qty'] ?? [];
        $userId;

        if ($userId = $this->getUserId()) {
            try {
                // Database init
                $dbUpdater = new Updater();
                $dbDeleter = new Deleter();
            } catch (\Exception $e) {
                $this->flash('danger', "Something went wrong, try again later");
                Logger::log('db', 'error', "Failed to initialize the database", $e);
                Router::redirect('/cart');
            }
        }

        foreach ($array as $prodId => $qty) {
            if ($qty > 0) {
                if (!empty($userId)) {
                    // Update user's cart in database
                    try {
                        $dbUpdater->updateCartItem($userId, $prodId, $qty);

                    } catch (\Exception $e) {
                        $this->flash('danger', "Something went wrong, try again later");
                        Logger::log(
                            'db', 'error',
                            "Failed to update item '$prodId' in user's '$userId' cart",
                            $e
                        );
                        Router::redirect('/cart');
                    }
                } else {
                    // Update guest's cart cookie
                    Router::updateCookie(
                        'cart',
                        ['key' => $prodId, 'val' => $qty]
                    );
                }
            } else {
                if (!empty($userId)) {
                    // Delete from user's cart in database
                    try {
                        $dbDeleter->removeCartItem($userId, $prodId);

                    } catch (\Exception $e) {
                        $this->flash('danger', "Something went wrong, try again later");
                        Logger::log(
                            'db', 'error',
                            "Failed to delete item '$prodId' from user's '$userId' cart",
                            $e
                        );
                        Router::redirect('/cart');
                    }
                } else {
                    // Delete from guest's cart cookie
                    Router::deleteCookie('cart', ['key' => $prodId]);
                }
            }
        }
        Router::redirect('/cart');
    }
}
