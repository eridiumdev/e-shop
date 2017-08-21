<?php
namespace App\Controller;

use App\Model\Database\Reader;
use App\Model\Database\Creator;
use App\Model\Database\Updater;
use App\Model\Database\Deleter;

class AccountController extends BaseController
{
    protected $template = 'account.twig';

    /**
     * Shows account homepage OR login/register page
     * @param  string  $input - user email or username
     *                        (if some error occurred while logging in)
     */
    public function showAccountPage(string $input = null)
    {
        if ($input) $this->addTwigVar('input', $input);
        $this->render();
    }

    public function showRegisterPage(string $username = null, string $email = null)
    {
        if ($username) $this->addTwigVar('username', $username);
        if ($email) $this->addTwigVar('email', $email);

        $this->setTemplate('account/register.twig');
        $this->render();
    }

    public function showOrdersPage()
    {
        try {
            $dbReader = new Reader();
            if ($userId = $this->getUserId()) {
                $orders = $dbReader->getUserOrders($userId);
            }
            krsort($orders);
        } catch (\Exception $e) {
            Logger::log('db', 'error', 'Failed to get user orders', $e);
            $this->flash('danger', 'Database operation failed');
            Router::redirect('/account');
        }

        $this->addTwigVar('orders', $orders);
        $this->setTemplate('account/orders.twig');
        $this->render();
    }

    public function showShippingPage()
    {
        try {
            $dbReader = new Reader();
            if ($userId = $this->getUserId()) {
                $shipping = $dbReader->getUserShipping($userId);
            }
        } catch (\Exception $e) {
            Logger::log('db', 'error', "Failed to get user's '$userId' shipping", $e);
            $this->flash('danger', 'Database operation failed');
            Router::redirect('/account');
        }

        $this->addTwigVar('shipping', $shipping);
        $this->setTemplate('account/shipping.twig');
        $this->render();
    }

    public function updateUserShipping(array $post)
    {
        if (!$this->isClean($post)) {
            $this->flash('danger', 'Some field contains invalid characters');
            Router::redirect('/account/shipping');
        }

        $name = $post['name'];
        $phone = $post['phone'];
        $address = $post['address'];

        try {
            $dbCreator = new Creator();
            $dbDeleter = new Deleter();

            if (!empty($userId = $this->getUserId())) {
                // Replace old shipping with new shipping
                $dbDeleter->deleteUserShipping($userId);
                $dbCreator->createUserShipping($userId, $name, $phone, $address);

                $this->flash('success', 'Shipping details updated successfully');
                Router::redirect('/account/shipping');
            } else {
                throw new \Exception;
            }
        } catch (\Exception $e) {
            Logger::log('db', 'error', "Failed to update user '$userId' shipping", $e);
            $this->flash('danger', 'Database operation failed');
            Router::redirect("/account/shipping");
        }
    }

    public function showDetailsPage()
    {
        try {
            $dbReader = new Reader();
            if ($userId = $this->getUserId()) {
                $user = $dbReader->getUserById($userId);
            }
        } catch (\Exception $e) {
            Logger::log('db', 'error', "Failed to get user '$userId'", $e);
            $this->flash('danger', 'Database operation failed');
            Router::redirect('/account');
        }

        $this->addTwigVar('user', $user);
        $this->setTemplate('account/details.twig');
        $this->render();
    }

    public function updateUserDetails(array $post)
    {
        if (!$this->isClean($post)) {
            $this->flash('danger', 'Some field contains invalid characters');
            Router::redirect('/account/details');
        }

        $userId = $post['id'];
        $username = $post['username'];
        $email = $post['email'];
        $oldPassword = $post['oldPassword'];
        $newPassword = $post['newPassword'];
        $confirmPassword = $post['confirmPassword'];

        try {
            $dbReader = new Reader();
            $user = $dbReader->getUserById($userId);

            if (empty($user)) {
                $this->flash('danger', 'Something went wrong. Please re-login and try again');
                Router::redirect('/account/details');
            }

            if (!empty($oldPassword)) {
                if ($newPassword != $confirmPassword) {
                    $this->flash('danger', "Passwords do not match");
                    Router::redirect('/account/details');
                }

                if (!password_verify($oldPassword, $user->getPassword())) {
                    $this->flash('danger', 'Incorrect current password');
                    Router::redirect('/account/details');
                }

                $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            } else {
                $hashed = $user->getPassword();
            }

            $type = $user->getType();
            $registeredAt = $user->getRegisteredAt();

            $dbUpdater = new Updater();
            $dbUpdater->updateUser(
                $userId,
                $username,
                $email,
                $hashed,
                $type,
                $registeredAt
            );

            $this->flash('success', 'Details updated successfully');
            Router::redirect('/account/details');

        } catch (\Exception $e) {
            Logger::log('db', 'error', 'Failed to change user password', $e);
            $this->flash('danger', 'Operation failed, please try again');
            Router::redirect('/account/details');
        }
    }

    /**
     * Verifies login info, logs in user and redirects to account homepage
     * OR stays on login page and displays errors
     * @param  array  $post - [input, password]
     */
    public function login(array $post)
    {
        if (!$this->isClean($post)) {
            $this->flash('danger', 'Some field contains invalid characters');
            return $this->showAccountPage($post['input']);
        }

        $input = $post['input'];
        $password = $post['password'];

        try {
            $dbReader = new Reader();
            if (filter_var($input, FILTER_VALIDATE_EMAIL)) {
                $user = $dbReader->getUserByEmail($input);
            } else {
                $user = $dbReader->getUserByUsername($input);
            }
        } catch (\Exception $e) {
            Logger::log('db', 'error', 'Failed to find user by username or email', $e);
            $this->flash('danger', 'Login failed, please try again');
            return $this->showAccountPage($input);
        }

        if (empty($user)) {
            $this->flash('danger', 'Username or email not found');
            return $this->showAccountPage($input);
        }

        if (!password_verify($password, $user->getPassword())) {
            $this->flash('danger', 'Password is incorrect');
            return $this->showAccountPage($input);
        }

        $this->flash('success', "Welcome back, $input");
        $authToken = Security::encodeToken($user->getId(), $user->getType());
        $redirect = ($user->getType() == 'admin')? '/admin' : '/account';
        return Router::redirect($redirect, $authToken);
    }

    /**
     * Verifies inputs, registers user and redirects to account homepage
     * OR stays on registration page and displays errors
     * @param  array  $post - [username, email, password, confirm_password]
     */
    public function register(array $post)
    {
        if (!$this->isClean($post)) {
            $this->flash('danger', 'Some field(s) contains invalid characters');
            return $this->showRegisterPage($post['username'], $post['email']);
        }

        $username = $post['username'];
        $email = $post['email'];
        $password = $post['password'];
        $confirmPassword = $post['confirmPassword'];

        if ($password != $confirmPassword) {
            $this->flash('danger', 'Passwords do not match');
            return $this->showRegisterPage($username, $email);
        }

        try {
            $dbReader = new Reader();

            $user = $dbReader->getUserByUsername($username);
            if (!empty($user)) {
                $this->flash('danger', 'This username is already registered');
                return $this->showRegisterPage($username, $email);
            }

            $user = $dbReader->getUserByEmail($email);
            if (!empty($user)) {
                $this->flash('danger', 'This email is already registered');
                return $this->showRegisterPage($username, $email);
            }

            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $dbCreator = new Creator();
            $user = $dbCreator->createUser($username, $email, $hashed);

            $this->flash('success', 'Registration complete');
            $authToken = Security::encodeToken($user->getId(), $user->getType());
            return Router::redirect('/account', $authToken);

        } catch (\Exception $e) {
            Logger::log('db', 'error', 'Failed to register new user' . $username, $e);
            $this->flash('danger', 'Registration failed, please try again');
            return $this->showRegisterPage($username, $email);
        }
    }
}
