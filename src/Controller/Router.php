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
        // pe($route);

        switch ($route['base']) {
            case '' :

                $controller = new BaseController();
                $controller->showHomepage();
                break;

            case 'account' :

                self::routeAccount();
                break;

            case 'logout' :

                Security::requireAuth();
                Security::logout();
                break;

            case 'account' :

                Security::requireAuth();
                $controller = new AccountController();

                if (empty($post)) {
                    $controller->showChangePasswordPage();
                } else {
                    $controller->changePassword($post);
                }
                break;

            case 'cart':

                $controller = new CartController();
                $controller->showCartPage();
                break;

            case 'checkout' :

                $controller = new CheckoutController();
                switch($route['section']) {
                    case 'step-one' :
                        $controller->showStepOnePage();
                        break;
                    case 'step-two' :
                        $controller->showStepTwoPage();
                        break;
                    case 'step-three' :
                        $controller->showStepThreePage();
                        break;
                    default :
                        self::redirect('/checkout/step-one');
                }
                break;

            case 'admin' :

                self::routeAdmin();
                break;

            case 'catalog' :

                $controller = new CatalogController();
                if (!empty($route['section'])) {
                    if (is_numeric($route['page'])) {
                        $controller->showProductPage($route['page']);
                    } else {
                        if (!empty($post)) {
                            $controller->showFilteredPage('filtered');
                        } else {
                            $controller->showCategoryPage($route['section']);
                        }
                    }
                } else {
                    $controller->showCatalogPage();
                }
                break;

            case 'keyboards' :

                $controller = new CatalogController();

                if (is_numeric($route['page'])) {
                    $controller->showProductPage($route['page']);
                } else {
                    if (!empty($post)) {
                        $controller->showFilteredPage('filtered');
                    } else {
                        $controller->showCatalogPage('keyboards');
                    }
                }
                break;

            case 'mice' :

                $controller = new CatalogController();

                if (is_numeric($route['page'])) {
                    $controller->showProductPage($route['page']);
                } else {
                    if (!empty($post)) {
                        $controller->showFilteredPage('filtered');
                    } else {
                        $controller->showCatalogPage('mice');
                    }
                }
                break;

            default:
                self::redirect('/');
                // 404 NOT_FOUND
        }
    }

    /**
     *               uri                   action              tepmlate
     * -------------------------------------------------------------------------
     * /                                homepage            homepage.twig
     * /login                           login               login.twig
     * /catalog/1                       show product 1      product.twig
     * /catalog?s=                      search              catalog.twig
     * /admin/products                  manage products     admin_prod.twig
     * /admin/products/1                view product 1      view_prod.twig
     * /admin/products/change/1         change product 1    change_prod.twig
     *
     */

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

    /**
     * Adds cookie to put in the response
     * @param  string $name
     * @param  any $value
     * @return void
     */
    public static function addCookie(string $name, $value)
    {
        self::$cookies[] = [
            'name'  => $name,
            'value' => $value
        ];
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

        $response->headers->clearCookie('file_to_overwrite');

        if ($authToken) {
            $response->headers->setCookie($authToken);
        }

        foreach (self::$cookies as $cookie) {
            $newCookie = new \Symfony\Component\HttpFoundation\Cookie(
                $cookie['name'], $cookie['value']
            );

            $response->headers->setCookie($newCookie);
        }

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

            case 'details' :
                Security::requireAuth();
                $controller->showDetailsPage();
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
        $controller = new AdminController();

        switch ($route['section']) {
            case '' :

                $controller->showDashboardPage();
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

                    case 'add-single' :
                        if (empty($post)) {
                            $controller->showAddUserPage();
                        } else {
                            $controller->addUser($post);
                        }
                        break;

                    case 'add-many' :
                        $accs = Uploader::readYml(
                            $controller, $post['yml'], '/admin/users'
                        );
                        $controller->batchAddUsers($accs);
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

                // Check if user id in uri is valid
                if (in_array($route['action'], ['view']) && (
                    empty($route['page']) ||
                    !is_numeric($route['page'])
                )) {
                    self::redirect('/admin/orders');
                }

                switch ($route['action']) {
                    case '' :
                        $controller->showOrdersListPage();
                        break;

                    case 'view' :
                        if (empty($post)) {
                            $controller->showChangeAccountPage($route['page']);
                        } else {
                            $controller->changeAccount($route['page'], $post);
                        }
                        break;

                    case 'delete' :
                        if (empty($post['id'])) {
                            $controller->showAccountListPage();
                        } elseif (count($post['id']) == 1){
                            $controller->deleteAccount($post['id'][0]);
                        } else {
                            $controller->deleteAccounts($post['id']);
                        }
                        break;

                    default :
                        self::redirect('/admin/accounts');
                }
                break;

            case 'categories' :

                $controller = new CategoryManager();

                // Check if user id in uri is valid
                if (in_array($route['action'], ['view']) && (
                    empty($route['page']) ||
                    !is_numeric($route['page'])
                )) {
                    self::redirect('/admin/categories');
                }

                switch ($route['action']) {
                    case '' :
                        $controller->showCatsListPage();
                        break;

                    case 'view' :
                        if (empty($post)) {
                            $controller->showChangeAccountPage($route['page']);
                        } else {
                            $controller->changeAccount($route['page'], $post);
                        }
                        break;

                    case 'delete' :
                        if (empty($post['id'])) {
                            $controller->showAccountListPage();
                        } elseif (count($post['id']) == 1){
                            $controller->deleteAccount($post['id'][0]);
                        } else {
                            $controller->deleteAccounts($post['id']);
                        }
                        break;

                    default :
                        self::redirect('/admin/accounts');
                }
                break;

            case 'sections' :

                $controller = new SectionManager();

                // Check if user id in uri is valid
                if (in_array($route['action'], ['view']) && (
                    empty($route['page']) ||
                    !is_numeric($route['page'])
                )) {
                    self::redirect('/admin/categories');
                }

                switch ($route['action']) {
                    case '' :
                        $controller->showSectionsListPage();
                        break;

                    case 'view' :
                        if (empty($post)) {
                            $controller->showChangeAccountPage($route['page']);
                        } else {
                            $controller->changeAccount($route['page'], $post);
                        }
                        break;

                    case 'delete' :
                        if (empty($post['id'])) {
                            $controller->showAccountListPage();
                        } elseif (count($post['id']) == 1){
                            $controller->deleteAccount($post['id'][0]);
                        } else {
                            $controller->deleteAccounts($post['id']);
                        }
                        break;

                    default :
                        self::redirect('/admin/accounts');
                }
                break;

            case 'products' :

                $controller = new ProductManager();

                // Check if user id in uri is valid
                if (in_array($route['action'], ['view']) && (
                    empty($route['page']) ||
                    !is_numeric($route['page'])
                )) {
                    self::redirect('/admin/categories');
                }

                switch ($route['action']) {
                    case '' :
                        $controller->showProductsListPage();
                        break;

                    case 'view' :
                        if (empty($post)) {
                            $controller->showChangeAccountPage($route['page']);
                        } else {
                            $controller->changeAccount($route['page'], $post);
                        }
                        break;

                    case 'delete' :
                        if (empty($post['id'])) {
                            $controller->showAccountListPage();
                        } elseif (count($post['id']) == 1){
                            $controller->deleteAccount($post['id'][0]);
                        } else {
                            $controller->deleteAccounts($post['id']);
                        }
                        break;

                    default :
                        self::redirect('/admin/accounts');
                }
                break;

            case 'specs' :

                $controller = new SpecManager();

                // Check if user id in uri is valid
                if (in_array($route['action'], ['view']) && (
                    empty($route['page']) ||
                    !is_numeric($route['page'])
                )) {
                    self::redirect('/admin/categories');
                }

                switch ($route['action']) {
                    case '' :
                        $controller->showSpecsListPage();
                        break;

                    case 'view' :
                        if (empty($post)) {
                            $controller->showChangeAccountPage($route['page']);
                        } else {
                            $controller->changeAccount($route['page'], $post);
                        }
                        break;

                    case 'delete' :
                        if (empty($post['id'])) {
                            $controller->showAccountListPage();
                        } elseif (count($post['id']) == 1){
                            $controller->deleteAccount($post['id'][0]);
                        } else {
                            $controller->deleteAccounts($post['id']);
                        }
                        break;

                    default :
                        self::redirect('/admin/accounts');
                }
                break;

            case 'deliveries' :

                $controller = new DeliveryManager();

                // Check if user id in uri is valid
                if (in_array($route['action'], ['view']) && (
                    empty($route['page']) ||
                    !is_numeric($route['page'])
                )) {
                    self::redirect('/admin/categories');
                }

                switch ($route['action']) {
                    case '' :
                        $controller->showDeliveriesListPage();
                        break;

                    case 'view' :
                        if (empty($post)) {
                            $controller->showChangeAccountPage($route['page']);
                        } else {
                            $controller->changeAccount($route['page'], $post);
                        }
                        break;

                    case 'delete' :
                        if (empty($post['id'])) {
                            $controller->showAccountListPage();
                        } elseif (count($post['id']) == 1){
                            $controller->deleteAccount($post['id'][0]);
                        } else {
                            $controller->deleteAccounts($post['id']);
                        }
                        break;

                    default :
                        self::redirect('/admin/accounts');
                }
                break;

            case 'payments' :

                $controller = new PaymentManager();

                // Check if user id in uri is valid
                if (in_array($route['action'], ['view']) && (
                    empty($route['page']) ||
                    !is_numeric($route['page'])
                )) {
                    self::redirect('/admin/categories');
                }

                switch ($route['action']) {
                    case '' :
                        $controller->showPaymentsListPage();
                        break;

                    case 'view' :
                        if (empty($post)) {
                            $controller->showChangeAccountPage($route['page']);
                        } else {
                            $controller->changeAccount($route['page'], $post);
                        }
                        break;

                    case 'delete' :
                        if (empty($post['id'])) {
                            $controller->showAccountListPage();
                        } elseif (count($post['id']) == 1){
                            $controller->deleteAccount($post['id'][0]);
                        } else {
                            $controller->deleteAccounts($post['id']);
                        }
                        break;

                    default :
                        self::redirect('/admin/accounts');
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
