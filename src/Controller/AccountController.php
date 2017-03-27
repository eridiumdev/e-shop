<?php
namespace App\Controller;

use App\Model\Database\Reader;
use App\Model\Database\Updater;

class AccountController extends BaseController
{
    protected $template = 'account.twig';

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
