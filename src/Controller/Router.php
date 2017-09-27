<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Processes http request and sends http response
 */
class Router
{
    public static $route;

    public static $get;
    public static $post;
    public static $files;

    public static $cookies = [];
    public static $clearedCookies = [];

    /**
     * Finds appropriate controller based on request uri
     * and decides what method to call based on get&post data
     */
    public static function run()
    {
        global $req;
        global $session;

        $get = $req->query->all();
        $post = $req->request->all();
        $files = $req->files->all();

        self::$get = $get;
        self::$post = $post;
        self::$files = $files;

        $route = self::getRoute();
        self::$route = $route;

        switch ($route['base']) {
            case '' :

                $controller = new BaseController();
                $controller->showHomepage();
                break;

            case 'admin' :

                self::routeAdmin();
                break;

            case 'account' :

                self::routeAccount();
                break;

            case 'logout' :

                Security::requireAuth();
                Security::logout();
                break;

            case 'catalog' :

                $controller = new CatalogController();

                if (!empty($route['section'])) {
                    if (is_numeric($route['page']) && !empty($route['action'])) {
                        $controller->showProductPage(
                            $route['page'], $route['section']
                        );
                    } else {
                        $controller->showCatalogPage($route['section'], $post);
                    }
                } else {
                    self::redirect('/');
                }

                break;

            case 'cart':

                $controller = new CartController();

                if (!is_numeric($route['page']) &&
                    in_array($route['section'], ['add', 'remove'])
                ) {
                    self::redirect('/cart');
                } else {
                    $prodId = $route['page'];
                }

                switch ($route['section']) {
                    case 'add' :
                        $controller->addToCart($prodId);
                        break;

                    case 'remove' :
                        $controller->removeFromCart($prodId);
                        break;

                    case 'update' :
                        $controller->updateCart($post);
                        break;

                    case '' :
                    default :
                        $controller->showCartPage();
                }
                break;

            case 'checkout' :

                $controller = new CheckoutController();

                if (!empty($userId = $controller->getUserId())) {
                    $curStep = Router::getCookie('checkout-step')[$userId] ?? 1;
                } else {
                    $curStep = Router::getCookie('checkout-step')['guest'] ?? 1;
                }

                switch($route['section']) {
                    case 'step-one' :
                        if ($controller->getCart()->isEmpty()) {
                            self::redirect('/cart');
                        }
                        $controller->showStepOnePage($curStep);
                        break;

                    case 'add-shipping' :
                        if (!empty($post)) {
                            $controller->addShipping($post);
                        } else {
                            self::redirect('/checkout/step-one');
                        }
                        break;

                    case 'step-two' :
                        if ($curStep < 2) {
                            self::redirect('/checkout/step-one');
                        }
                        $controller->showStepTwoPage($curStep);
                        break;

                    case 'prepare-order' :
                        if ($curStep < 2) {
                            self::redirect('/checkout/step-one');
                        }
                        if (!empty($post)) {
                            $controller->prepareOrder($post);
                        } else {
                            self::redirect('/checkout/step-two');
                        }
                        break;

                    case 'step-three' :
                        if ($curStep < 3) {
                            self::redirect('/checkout/step-two');
                        }
                        $controller->showStepThreePage($curStep);
                        break;

                    case 'submit-order' :
                        if ($curStep < 3) {
                            self::redirect('/checkout/step-two');
                        }
                        $controller->submitOrder($post);
                        break;

                    default :
                        self::redirect('/checkout/step-one');
                }
                break;

            default:
                self::redirect('/');
                // 404 NOT_FOUND
        }
    }

    /**
     * Extracts route from the request uri
     * @return array - processed route
     */
    public static function getRoute()
    {
        global $req;

        $route = [];
        $uri = trim($req->getPathInfo(), '/');
        $uri = explode('/', $uri);

        $route['base'] = $uri[0];
        $route['section'] = empty($uri[1]) ? '' : $uri[1];
        $route['action'] = empty($uri[2]) ? '' : $uri[2];
        $route['page'] = $uri[count($uri)-1];

        return $route;
    }

    public static function addCookie(string $cookieName, $cookieData)
    {
        // e.g.: $cookieName = 'cart'
        //       $cookieData = ['key' => prodId, 'val' => qty]

        if ($cookie = self::getCookie($cookieName)) {
            // if cookie (e.g. 'cart') already exists
            foreach ($cookie as $key => $val) {
                // copy it to pass to response
                self::$cookies[$cookieName][$key] = $val;
            }
        }

        if (is_array($cookieData)) {
            // add a new key-val pair to the cookie
            // if (isset(self::$cookies[$cookieName][$cookieData['key']]) &&
            //     is_numeric(self::$cookies[$cookieName][$cookieData['key']])
            // ) {
            //     self::$cookies[$cookieName][$cookieData['key']] = $cookieData['val'];
            // }
            self::$cookies[$cookieName][$cookieData['key']] = $cookieData['val'];
        } else {
            // add a string val to the end of the cookie?
            self::$cookies[$cookieName][] = $cookieData;
        }
    }

    public static function deleteCookie(string $cookieName, $cookieData = null)
    {
        // e.g.: $cookieName = 'cart'
        //       $cookieData = ['key' => prodId]
        if ($cookie = self::getCookie($cookieName)) {
            if (isset($cookieData)) {
                foreach ($cookie as $key => $val) {
                    // rewrite everything, except for the key we want to delete
                    if ($cookieData['key'] != $key) {
                        self::$cookies[$cookieName][$key] = $val;
                    }
                }
            }
            self::$clearedCookies[] = $cookieName;
        }
    }

    public static function updateCookie(string $cookieName, $cookieData)
    {
        // e.g.: $cookieName = 'cart'
        //       $cookieData = ['key' => prodId, 'val' => qty]

        if ($cookie = self::getCookie($cookieName)) {
            foreach ($cookie as $key => $val) {
                // rewrite everything, except for the key we want to update
                if ($cookieData['key'] != $key) {
                    self::$cookies[$cookieName][$key] = $val;
                } else {
                    self::$cookies[$cookieName][$key] = $cookieData['val'];
                }
            }
            self::$clearedCookies[] = $cookieName;
        }
    }

    public static function getCookie(string $name)
    {
        global $req;

        if (isset(self::$cookies[$name])) {
            // When adding/updating multiple cookies,
            // this should already be initialized
            // and $decoded would be outdated
            return self::$cookies[$name];
        } elseif ($decoded = json_decode($req->cookies->get($name), true)) {
            return $decoded;
        } else {
            return false;
        }
    }

    /**
     * Prepares and sends http response and exits script
     * @param  [type] $html     - rendered twig output
     * @param  int $httpCode    - HTTP_FOUND, HTTP_NOT_FOUND, etc
     * @param  array $headers   - optional http headers
     * @param  array $authToken - optional authentication token
     * @return void
     */
    public static function sendResponse(
        $html,
        $httpCode = Response::HTTP_FOUND,
        array $headers = [],
        $authToken = null
    ) {
        $response = new Response($html, $httpCode, $headers);

        if ($authToken) {
            $response->headers->setCookie($authToken);
        }

        // Replace old cookies
        foreach (self::$clearedCookies as $name) {
            $response->headers->clearCookie($name);
        }

        $expireTime = time() + 24 * 60 * 60;    // one day
        foreach (self::$cookies as $name => $value) {
            if (is_array($value)) {
                $encoded = json_encode($value);
            } else {
                $encoded = $value;
            }

            $newCookie = new \Symfony\Component\HttpFoundation\Cookie(
                $name, $encoded, $expireTime
            );

            $response->headers->setCookie($newCookie);
        }

        // Disable cache for security reasons (temporary solution)
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->headers->addCacheControlDirective('public', true);
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('no-store', true);
        $response->headers->addCacheControlDirective('max-age', 0);
        $response->headers->addCacheControlDirective('post-check', 0);
        $response->headers->addCacheControlDirective('pre-check', 0);

        $response->send();
        exit;   // close current script execution after redirect
    }

    /**
     * Internal redirect to process request at another Location
     * @param  string $uri
     * @param  Cookie Object $authToken
     * @return void
     */
    public static function redirect(string $uri, $authToken = null)
    {
        self::sendResponse(
            null,
            Response::HTTP_FOUND,
            ['location' => $uri],
            $authToken
        );
    }

    public static function routeAccount()
    {
        $route = self::$route;
        $get = self::$get;
        $post = self::$post;
        $files = self::$files;

        $controller = new AccountController();

        switch ($route['section']) {
            case '' :
                $controller->showAccountPage();
                break;

            case 'login' :
                if (empty($post)) {
                    $controller->showAccountPage();
                } else {
                    $controller->login($post);
                }
                break;

            case 'register' :
                if (empty($post)) {
                    $controller->showRegisterPage();
                } else {
                    $controller->register($post);
                }
                break;

            case 'orders' :
                Security::requireAuth();
                $controller->showOrdersPage();
                break;

            case 'shipping' :
                Security::requireAuth();
                $controller->showShippingPage();
                break;

            case 'update-shipping' :
                Security::requireAuth();
                if (!empty($post)) {
                    $controller->updateUserShipping($post);
                } else {
                    self::redirect('/account/shipping');
                }
                break;

            case 'details' :
                Security::requireAuth();
                $controller->showDetailsPage();
                break;

            case 'update-details' :
                Security::requireAuth();
                if (!empty($post)) {
                    $controller->updateUserDetails($post);
                } else {
                    self::redirect('/account/details');
                }
                break;

            default :
                $controller->showAccountPage();
        }
    }

    /**
     * Handles routing inside Admin Dashboard
     * @param  array  $route
     * @param  array  $get   - GET data
     * @param  array  $post  - POST data
     * @param  array  $files - file uploads
     * @return void
     */
    public static function routeAdmin()
    {
        $route = self::$route;
        $get = self::$get;
        $post = self::$post;
        $files = self::$files;

        Security::requireAdmin();

        switch ($route['section']) {
            case '' :

                $controller = new AdminController();
                $controller->showDashboardPage();
                break;

            case 'reset' :

                $controller = new AdminController();
                // $controller->resetDatabase();
                self::redirect('/admin');
                break;

            case 'products' :

                $controller = new ProductManager();
                if (!empty($route['page']) && is_numeric($route['page'])) {
                    $prodId = $route['page'];
                }

                // Check if prodId in uri is valid (when it is required)
                if (in_array($route['action'], ['view', 'update'])
                    && !isset($prodId)
                ) {
                    self::redirect('/admin/products');
                }

                switch ($route['action']) {
                    case '' :
                        $controller->showProductsListPage();
                        break;

                    case 'add' :
                        $controller->showAddProductPage();
                        break;

                    case 'add-single' :
                        if (empty($post)) {
                            self::redirect('/admin/products/add');
                        } else {
                            if (!empty($files['uploadedMainPic'])) {
                                $mainPic = Uploader::uploadPicture(
                                    PIC_DIRECTORY,
                                    $controller,
                                    $files['uploadedMainPic'],
                                    "/admin/products/add"
                                );
                                if ($mainPic) {
                                    $post['mainPic'] = $mainPic;
                                }
                            }
                            if (!empty($files['uploadedPics'][0])) {
                                $pics = [];
                                foreach ($files['uploadedPics'] as $tempPic) {
                                    $pic = Uploader::uploadPicture(
                                        PIC_DIRECTORY,
                                        $controller,
                                        $tempPic,
                                        "/admin/products/add"
                                    );
                                    if ($pic) {
                                        $pics[] = $pic;
                                    }
                                }
                                $post['pics'] = $pics;
                            } else {
                                $post['pics'] = [];
                            }
                            $controller->addProduct($post);
                        }
                        break;

                    case 'view' :
                        $controller->showViewProductPage($prodId);
                        break;

                    case 'update' :
                        if (empty($post)) {
                            self::redirect('/admin/products');
                        } else {
                            if (!empty($files['uploadedMainPic'])) {
                                $mainPic = Uploader::uploadPicture(
                                    PIC_DIRECTORY,
                                    $controller,
                                    $files['uploadedMainPic'],
                                    "/admin/products/view/$prodId"
                                );
                                if ($mainPic) {
                                    $post['mainPic'] = $mainPic;
                                }
                            }
                            if (!empty($files['uploadedPics'][0])) {
                                $pics = [];
                                foreach ($files['uploadedPics'] as $tempPic) {
                                    $pic = Uploader::uploadPicture(
                                        PIC_DIRECTORY,
                                        $controller,
                                        $tempPic,
                                        "/admin/products/view/$prodId"
                                    );
                                    if ($pic) {
                                        $pics[] = $pic;
                                    }
                                }
                                $post['pics'] = $pics;
                            } else {
                                $post['pics'] = [];
                            }
                            $controller->updateProduct($prodId, $post);
                        }
                        break;

                    case 'delete' :
                        if (empty($post['id'])) {
                            self::redirect('/admin/products');
                        } elseif (count($post['id']) == 1) {
                            $controller->deleteProduct($post['id'][0]);
                        } else {
                            $controller->deleteProducts($post['id']);
                        }
                        break;

                    default :
                        self::redirect('/admin/products');
                }
                break;

            case 'categories' :

                $controller = new CategoryManager();
                if (!empty($route['page']) &&
                    is_numeric($route['page']) &&
                    in_array($route['action'], ['view', 'delete'])
                ) {
                    $catId = $route['page'];
                }

                switch ($route['action']) {
                    case '' :
                        $controller->showCategoriesListPage();
                        break;

                    case 'add' :
                        $controller->showAddCategoryPage();
                        break;

                    case 'add-single' :
                        if (empty($post)) {
                            self::redirect('/admin/categories/add');
                        } else {
                            $controller->addCategory($post);
                        }
                        break;

                    case 'view' :
                        if (!isset($catId)) {
                            self::redirect('/admin/categories');
                        }
                        $controller->showViewCategoryPage($catId);
                        break;

                    case 'update' :
                        if (empty($post)) {
                            self::redirect('/admin/categories');
                        } else {
                            // using post['id'] here is safer (readonly input)
                            $controller->updateCategory($post['id'], $post);
                        }
                        break;

                    case 'delete' :
                        if (empty($post['id'])) {
                            self::redirect('/admin/categories');
                        } else {
                            $controller->deleteCategory($post['id'][0]);
                        }
                        break;

                    default :
                        self::redirect('/admin/categories');
                }
                break;

            case 'sections' :

                $controller = new SectionManager();

                if (!empty($route['page']) &&
                    is_numeric($route['page']) &&
                    in_array($route['action'], ['view', 'delete'])
                ) {
                    $sectId = $route['page'];
                }

                switch ($route['action']) {
                    case '' :
                        $controller->showSectionsListPage();
                        break;

                    case 'view' :
                        if (!isset($sectId)) {
                            self::redirect('/admin/sections');
                        }
                        $controller->showViewSectionPage($sectId);
                        break;

                    case 'update' :
                        if (empty($post)) {
                            self::redirect('/admin/sections');
                        } else {
                            $controller->updateSection($post);
                        }
                        break;

                    default :
                        self::redirect('/admin/sections');
                }
                break;

            case 'specs' :

                $controller = new SpecManager();

                if (!empty($route['page']) &&
                    is_numeric($route['page']) &&
                    in_array($route['action'], ['view', 'delete'])
                ) {
                    $specId = $route['page'];
                }

                switch ($route['action']) {
                    case '' :
                        $controller->showSpecsListPage();
                        break;

                    case 'add' :
                        $controller->showAddSpecPage();
                        break;

                    case 'add-single' :
                        if (empty($post)) {
                            self::redirect('/admin/specs/add');
                        } else {
                            $controller->addSpec($post);
                        }
                        break;

                    case 'view' :
                        if (!isset($specId)) {
                            self::redirect('/admin/specs');
                        }
                        $controller->showViewSpecPage($specId);
                        break;

                    case 'update' :
                        if (empty($post)) {
                            self::redirect('/admin/specs');
                        } else {
                            $controller->updateSpec($post['id'], $post);
                        }
                        break;

                    case 'delete' :
                        if (empty($post['id'])) {
                            self::redirect('/admin/specs');
                        } else {
                            $controller->deleteSpec($post['id'][0]);
                        }
                        break;

                    default :
                        self::redirect('/admin/specs');
                }
                break;

            case 'users' :

                $controller = new UserManager();

                // Check if user id in uri is valid
                if (in_array($route['action'], ['view', 'update'])) {
                    if (empty($route['page']) || !is_numeric($route['page'])) {
                        self::redirect('/admin/users');
                    } else {
                        $userId = $route['page'];
                    }
                }

                switch ($route['action']) {
                    case '' :
                        $controller->showUsersListPage();
                        break;

                    case 'add' :
                        $controller->showAddUserPage();
                        break;

                    case 'add-single' :
                        if (empty($post)) {
                            self::redirect('/admin/users/add');
                        } else {
                            $controller->addUser($post);
                        }
                        break;

                    case 'view' :
                        $controller->showUserPage($userId);
                        break;

                    case 'update' :
                        if (empty($post)) {
                            self::redirect("/admin/users/view/$userId");
                        } else {
                            $controller->updateUser($userId, $post);
                        }

                    case 'delete' :
                        if (empty($post['id'])) {
                            $controller->showUsersListPage();
                        } elseif (count($post['id']) == 1){
                            $controller->deleteUser($post['id'][0]);
                        } else {
                            $controller->deleteUsers($post['id']);
                        }
                        break;

                    default :
                        self::redirect('/admin/users');
                }
                break;

            case 'orders' :

                $controller = new OrderManager();

                if (!empty($route['page']) &&
                    is_numeric($route['page']) &&
                    in_array($route['action'], ['view', 'delete'])
                ) {
                    $orderId = $route['page'];
                }

                switch ($route['action']) {
                    case '' :
                        $controller->showOrdersListPage();
                        break;

                    case 'add' :
                        $controller->showAddOrderPage();
                        break;

                    case 'change-status' :
                        if (!empty($post)) {
                            $controller->changeStatus($post);
                        } else {
                            self::redirect('/admin/orders');
                            break;
                        }
                        break;

                    case 'view' :
                        if (!isset($orderId)) {
                            self::redirect('/admin/orders');
                        }
                        $controller->showViewOrderPage($orderId);
                        break;
                    //
                    // case 'update' :
                    //     if (empty($post)) {
                    //         self::redirect('/admin/orders');
                    //     } else {
                    //         $controller->updateOrder($post['id'], $post);
                    //     }
                    //     break;
                    //
                    // case 'delete' :
                    //     if (empty($post['id'])) {
                    //         self::redirect('/admin/orders');
                    //     } else {
                    //         $controller->deleteOrder($post['id'][0]);
                    //     }
                    //     break;

                    default :
                        self::redirect('/admin/orders');
                }
                break;

            case 'deliveries' :

                $controller = new DeliveryManager();

                if (!empty($route['page']) &&
                    is_numeric($route['page']) &&
                    in_array($route['action'], ['view', 'delete'])
                ) {
                    $deliveryId = $route['page'];
                }

                switch ($route['action']) {
                    case '' :
                        $controller->showDeliveriesListPage();
                        break;

                    case 'add' :
                        $controller->showAddDeliveryPage();
                        break;

                    // case 'add-single' :
                    //     if (empty($post)) {
                    //         self::redirect('/admin/deliveries/add');
                    //     } else {
                    //         $controller->addDelivery($post);
                    //     }
                    //     break;
                    //
                    // case 'view' :
                    //     if (!isset($deliveryId)) {
                    //         self::redirect('/admin/deliveries');
                    //     }
                    //     $controller->showViewDeliveryPage($deliveryId);
                    //     break;
                    //
                    // case 'update' :
                    //     if (empty($post)) {
                    //         self::redirect('/admin/deliveries');
                    //     } else {
                    //         $controller->updateDelivery($post['id'], $post);
                    //     }
                    //     break;
                    //
                    // case 'delete' :
                    //     if (empty($post['id'])) {
                    //         self::redirect('/admin/deliveries');
                    //     } else {
                    //         $controller->deleteDelivery($post['id'][0]);
                    //     }
                    //     break;

                    default :
                        self::redirect('/admin/deliveries');
                }
                break;

            case 'payments' :

                $controller = new PaymentManager();

                if (!empty($route['page']) &&
                    is_numeric($route['page']) &&
                    in_array($route['action'], ['view', 'delete'])
                ) {
                    $paymentId = $route['page'];
                }

                switch ($route['action']) {
                    case '' :
                        $controller->showPaymentsListPage();
                        break;

                    case 'add' :
                        $controller->showAddPaymentPage();
                        break;

                    // case 'add-single' :
                    //     if (empty($post)) {
                    //         self::redirect('/admin/payments/add');
                    //     } else {
                    //         $controller->addPayment($post);
                    //     }
                    //     break;
                    //
                    // case 'view' :
                    //     if (!isset($paymentId)) {
                    //         self::redirect('/admin/payments');
                    //     }
                    //     $controller->showViewPaymentPage($paymentId);
                    //     break;
                    //
                    // case 'update' :
                    //     if (empty($post)) {
                    //         self::redirect('/admin/payments');
                    //     } else {
                    //         $controller->updatePayment($post['id'], $post);
                    //     }
                    //     break;
                    //
                    // case 'delete' :
                    //     if (empty($post['id'])) {
                    //         self::redirect('/admin/payments');
                    //     } else {
                    //         $controller->deletePayment($post['id'][0]);
                    //     }
                    //     break;

                    default :
                        self::redirect('/admin/payments');
                }
                break;

            case 'pictures' :

                $controller = new PictureManager();

                switch ($route['action']) {

                    case '' :
                        switch ($route['page']) {
                            case 'add' :
                                if (!empty($post)) {
                                    $controller->addPicturesToIllness($post);
                                } else {
                                    $controller->showUploadsPage();
                                }
                                break;

                            case 'delete' :
                                if (!empty($post['pics'])) {
                                    $controller->deletePictures(
                                        PIC_DIRECTORY,
                                        $post['pics'],
                                        '/admin/uploads/pictures'
                                    );
                                } else {
                                    $controller->showUploadsPage();
                                }
                                break;

                            case 'upload' :
                                if (!empty($files['uploads'])) {
                                    Uploader::uploadPictures(
                                        PIC_DIRECTORY,
                                        $controller,
                                        $files['uploads'],
                                        '/admin/uploads/pictures'
                                    );
                                } else {
                                    $controller->showUploadsPage();
                                }
                                break;

                            default :
                                if (!empty($post['pics_per_page'])) {
                                    $controller->showPicturesPage(
                                        $post['pics_per_page']
                                    );
                                } else {
                                    $controller->showPicturesPage();
                                }
                        }
                        break;

                }
                break;

            default :
                self::redirect('/admin');
        }
    }
}
