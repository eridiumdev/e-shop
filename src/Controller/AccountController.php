<?php
namespace App\Controller;

use App\Model\Database\Reader;
use App\Model\Database\Creator;
use App\Model\Database\Updater;

class AccountController extends BaseController
{
    protected $template = 'account.twig';

    /**
     * Shows account homepage OR login/register page
     * @param  string  $email - previous user input (if some error occurred)
     */
    public function showAccountPage(string $email = null)
    {
        if ($email) $this->addTwigVar('email', $email);
        $this->render();
    }

    public function showRegisterPage(string $email = null)
    {
        if ($email) $this->addTwigVar('email', $email);

        $this->setTemplate('account/register.twig');
        $this->render();
    }

    /**
     * Verifies login info, logs in user and redirects to account homepage
     * OR stays on login page and displays errors
     * @param  array  $post - [email, password]
     */
    public function login(array $post)
    {
        if (!$this->isClean($post)) {
            $this->flash('danger', 'Password contains invalid characters');
            return $this->showAccountPage($post['email']);
        }

        $email = $post['email'];
        $password = $post['password'];

        try {
            $dbReader = new Reader();
            $user = $dbReader->findUserByEmail($email);
        } catch (\Exception $e) {
            Logger::log('db', 'error', 'Failed to find user by email', $e);
            $this->flash('danger', 'Login failed, please try again');
            return $this->showAccountPage($email);
        }

        if (empty($user)) {
            $this->flash('danger', 'Email was not found');
            return $this->showAccountPage($email);
        }

        if (!password_verify($password, $user->getPassword())) {
            $this->flash('danger', 'Password is incorrect');
            return $this->showAccountPage($email);
        }

        $this->flash('success', "Welcome back, $email");
        $authToken = Security::encodeToken($user->getId(), $user->getType());
        return Router::redirect('/account', $authToken);
    }

    /**
     * Verifies inputs, registers user and redirects to account homepage
     * OR stays on registration page and displays errors
     * @param  array  $post - [email, password, confirm_password]
     */
    public function register(array $post)
    {
        if (!$this->isClean($post)) {
            $this->flash('danger', 'Password contains invalid characters');
            return $this->showAccountPage($post['email']);
        }

        $email = $post['email'];
        $password = $post['password'];
        $confirmPassword = $post['confirmPassword'];

        if ($password != $confirmPassword) {
            $this->flash('danger', 'Passwords do not match');
            return $this->showRegisterPage($email);
        }

        try {
            $dbReader = new Reader();
            $user = $dbReader->findUserByEmail($email);

            if (!empty($user)) {
                $this->flash('danger', 'This email is already registered');
                return $this->showRegisterPage($email);
            }

            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $dbCreator = new Creator();
            $user = $dbCreator->createUser($email, $hashed);

            $this->flash('success', 'Registration complete');
            $authToken = Security::encodeToken($user->getId(), $user->getType());
            return Router::redirect('/account', $authToken);

        } catch (\Exception $e) {
            Logger::log('db', 'error', 'Failed to register new user', $e);
            $this->flash('danger', 'Registration failed, please try again');
            return $this->showRegisterPage($email);
        }
    }

    public function showChangePasswordPage()
    {
        $this->render();
    }

    public function changePassword(array $post)
    {
        if (!$this->isClean($post)) {
            $this->flash('danger', 'Invalid password, try another');
            return $this->showChangePasswordPage();
        }

        $curPassword = $post['curPassword'];
        $newPassword = $post['newPassword'];
        $confirmPassword = $post['confirmPassword'];

        if ($newPassword != $confirmPassword) {
            $this->flash('danger', "Passwords do not match");
            return $this->showChangePasswordPage();
        }

        $userId = Security::getUserId();

        if ($userId === false) {
            $this->flash(
                'danger', 'Something went wrong. Please re-login and try again'
            );
            return $this->showChangePasswordPage();
        }

        try {
            $dbReader = new Reader();
            $user = $dbReader->findUserById($userId);

            if (empty($user)) {
                $this->flash(
                    'danger', 'Something went wrong. Please re-login and try again'
                );
                return $this->showChangePasswordPage();
            }

            if (!password_verify($curPassword, $user->getPassword())) {
                $this->flash('danger', 'Incorrect current password');
                return $this->showChangePasswordPage();
            }

            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);

            $dbUpdater = new Updater();
            $dbUpdater->changePassword($userId, $hashed);

            $this->flash('success', 'Password changed successfully');
            return Router::redirect('/account');

        } catch (\Exception $e) {
            Logger::log('db', 'error', 'Failed to change user password', $e);
            $this->flash('danger', 'Operation failed, please try again');
            return $this->showChangePasswordPage();
        }
    }
}
