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

        switch ($route['base']) {
            case '' :

                $controller = new BaseController();
                $controller->showHomepage();
                break;

            case 'account' :

                $controller = new AccountController();

                if (empty($post)) {
                    $controller->showLoginPage();
                } else {
                    $controller->login($post);
                }
                break;

            case 'registration' :

                $controller = new AuthController();

                if (empty($post)) {
                    $controller->showRegistrationPage();
                } else {
                    $controller->register($post);
                }
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

            case 'admin' :

                self::routeAdmin();
                break;

            case 'catalog' :

                $controller = new CatalogController();

                if (is_numeric($route['page'])) {
                    // TODO $controller->showProductPage($route['page']);
                } else {
                    $controller->showCatalogPage();
                }
                break;

            default:
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
        $get = self::$get;
        $post = self::$post;
        $files = self::$files;

        Security::requireAdmin();
        $controller = new AdminController();

        switch ($route['section']) {
            case '' :

                $controller->showDashboardPage();
                break;

            case 'accounts' :

                $controller = new AccountManager();

                // Check if user id in uri is valid
                if (in_array($route['action'], ['change'])
                    && (empty($route['page'])
                    || !is_numeric($route['page']))) {
                    self::redirect('/admin/accounts');
                }

                switch ($route['action']) {
                    case '' :
                        $controller->showAccountListPage();
                        break;

                    case 'add-single' :
                        if (empty($post)) {
                            $controller->showAddAccountPage();
                        } else {
                            $controller->addAccount($post);
                        }

                    case 'add-many' :
                        $accs = Uploader::readAccountsFromYml($controller, $files);
                        $controller->batchAddAccounts($accs);
                        break;

                    case 'change' :
                        if (empty($post)) {
                            $controller->showChangeAccountPage($route['page']);
                        } else {
                            $controller->changeAccount($route['page'], $post);
                        }
                        break;

                    case 'delete' :
                        // print_r($post);exit;
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

                $controller = new IllnessManager();

                // Check if illness id in uri is valid
                if (in_array($route['action'], ['view', 'change'])
                    && (empty($route['page'])
                    || !is_numeric($route['page']))) {
                    self::redirect('/admin/products');
                }

                switch ($route['action']) {
                    case '' :
                        $controller->showIllnessListPage();
                        break;

                    case 'view' :
                        $controller->showIllnessPage($route['page']);
                        break;

                    case 'add-single' :
                        // TODO
                        Router::redirect('/admin/products');
                        break;

                    case 'add-many' :
                        $ills = Uploader::readIllnessesFromYml($controller, $files);
                        $controller->batchAddIllnesses($ills);
                        break;

                    case 'change' :
                        // TODO
                        Router::redirect('/admin/products');
                        break;

                    case 'delete' :
                        // pe($post);
                        if (empty($post['id'])) {
                            $controller->showIllnessListPage();
                        } elseif (count($post['id']) == 1){
                            $controller->deleteIllness($post['id'][0]);
                        } else {
                            $controller->deleteIllnesses($post['id']);
                        }
                        break;

                    default :
                        self::redirect('/admin/products');
                }
                break;

            case 'drugs' :

            case 'hospitalization' :

            case 'payments' :

            case 'uploads' :

                // $controller = new UploadManager();
                // $controller->showUploadsPage();
                // break;

            default :
                self::redirect('/admin');
        }
    }
}
