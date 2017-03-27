<?php
namespace App\Controller;

use App\Model\Database\Reader;
use App\Model\Database\Creator;

/**
 * Processes user login, registration
 */
class AuthController extends BaseController
{
    public function showLoginPage(string $email = null)
    {
        if ($email) $this->addTwigVar('email', $email);
        $this->setTemplate('login.twig');
        $this->render();
    }

    public function showRegistrationPage(string $email = null)
    {
        if ($email) $this->addTwigVar('email', $email);
        $this->setTemplate('registration.twig');
        $this->render();
    }

    /**
     * Verifies login info, logs in user and redirects to homepage
     * OR stays on login page and displays errors
     * @param  array  $post - [email, password]
     */
    public function login(array $post)
    {
        if (!$this->isClean($post)) {
            $this->flash('danger', 'Password contains invalid characters');
            return $this->showLoginPage($post['email']);
        }

        $email = $post['email'];
        $password = $post['password'];

        try {
            $dbReader = new Reader();
            $user = $dbReader->findUserByEmail($email);
        } catch (\Exception $e) {
            Logger::log('db', 'error', 'Failed to find user by email', $e);
            $this->flash('danger', 'Login failed, please try again');
            return $this->showLoginPage($email);
        }

        if (empty($user)) {
            $this->flash('danger', 'This email was not found');
            return $this->showLoginPage($email);
        }

        if (!password_verify($password, $user->getPassword())) {
            $this->flash('danger', 'Password is incorrect');
            return $this->showLoginPage($email);
        }

        $this->flash('success', "Welcome back, $email");
        $authToken = Security::encodeToken($user->getId(), $user->getType());
        return Router::redirect('/', $authToken);
    }

    /**
     * Verifies inputs, registers user and redirects to homepage
     * OR stays on registration page and displays errors
     * @param  array  $post - [username, password, confirm_password]
     */
    public function register(array $post)
    {
        if (!$this->isClean($post)) {
            $this->flash('danger', 'Password contains invalid characters');
            return $this->showRegistrationPage($post['email']);
        }

        $email = $post['email'];
        $password = $post['password'];
        $confirmPassword = $post['confirmPassword'];

        if ($password != $confirmPassword) {
            $this->flash('danger', 'Passwords do not match');
            return $this->showRegistrationPage($post['email']);
        }

        try {
            $dbReader = new Reader();
            $user = $dbReader->findUserByEmail($email);

            if (!empty($user)) {
                $this->flash('danger', 'This email is already registered');
                return $this->showRegistrationPage($email);
            }

            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $dbCreator = new Creator();
            $user = $dbCreator->createUser($email, $hashed);

            $this->flash('success', 'Registration complete');
            $authToken = Security::encodeToken($user->getId(), $user->getType());
            return Router::redirect('/', $authToken);

        } catch (\Exception $e) {
            Logger::log('db', 'error', 'Failed to register new user', $e);
            $this->flash('danger', 'Registration failed, please try again');
            return $this->showRegistrationPage($email);
        }
    }
}
