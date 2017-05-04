<?php
namespace App\Controller;

use App\Model\Database\Creator;
use App\Model\Database\Deleter;
use App\Model\Database\Reader;
use App\Model\Database\Updater;

/**
 * Admin controller for managing orders
 */
class OrderManager extends AdminController
{
    public function showOrdersListPage()
    {
        $this->setTemplate('admin/orders.twig');
        $this->render();
    }

    public function showChangeAccountPage(int $userId)
    {
        try {
            $dbReader = new Reader();
            $user = $dbReader->getUserById($userId);
        } catch (\Exception $e) {
            Logger::log('db', 'error', 'Failed to find user by id', $e);
            $this->flash('danger', 'Database operation failed');
            Router::redirect('/admin/accounts');
        }

        if (empty($user)) {
            $this->flash('danger', 'User not found');
            Router::redirect('/admin/accounts');
        }

        $this->setTemplate('admin/inc/account.twig');
        $this->addTwigVar('user', $user);
        $this->render();
    }

    public function changeAccount(int $userId, array $post)
    {
        if (!$this->isClean($post)) {
            $this->flash('danger', 'Password contains invalid characters');
            return $this->showChangeAccountPage($userId);
        }

        $email = $post['email'];
        $password = $post['password'];
        $type = $post['type'];

        try {
            $dbReader = new Reader();
            $old = $dbReader->getUserById($userId);

            if (empty($old)) {
                $this->flash('danger', 'Some problem occurred, please try again');
                return $this->showChangeAccountPage($userId);
            }

            if ($old->getEmail() != $email) {
                // check for duplicate email
                $duplicate = $dbReader->getUserByEmail($email);
                if (!empty($duplicate)) {
                    $this->flash('danger', "Email [$email] is already registered");
                    return $this->showChangeAccountPage($userId);
                }
            }

            $password = empty($password) ?
                $old->getPassword() :
                password_hash($password, PASSWORD_DEFAULT);

            $dbUpdater = new Updater();
            $dbUpdater->updateUser(
                $userId,
                $email,
                $password,
                $type,
                $old->getRegisteredAt()
            );

            $this->flash('success', "[$email] updated successfully");
            return Router::redirect('/admin/accounts');

        } catch (\Exception $e) {
            Logger::log('db', 'error',
                'Failed to change user account', $e, [
                'user id' => $userId,
                'username' => $email,
            ]);
            $this->flash('danger', 'Database operation failed');
            return $this->showChangeAccountPage($userId);
        }
    }

    public function deleteAccount(int $userId)
    {
        try {
            $dbDeleter = new Deleter();
            $deleted = $dbDeleter->deleteUser($userId);
            if (!$deleted) {
                $this->flash('danger',
                    "Could not delete user [$userId], try again"
                );
                return Router::redirect('/admin/accounts');
            }

            $email = $deleted->getEmail();

            $this->flash('success', "User [$email] deleted");
            return Router::redirect('/admin/accounts');

        } catch (\Exception $e) {
            Logger::log('db', 'error',
                "Failed to delete user (single)", $e,
                ['user id' => $userId]
            );
            $this->flash('danger', 'Database operation failed');
            return Router::redirect('/admin/accounts');
        }
    }

    public function deleteAccounts(array $users)
    {
        $good = [];
        $bad = [];

        foreach ($users as $userId) {
            try {
                $dbDeleter = new Deleter();
                $deleted = $dbDeleter->deleteUser($userId);

                if (!$deleted) {
                    $bad['db'][] = $userId;
                } else {
                    $good[] = $deleted;
                }
            } catch (\Exception $e) {
                Logger::log('db', 'error',
                    "Failed to delete user (batch)", $e,
                    ['user id' => $userId]
                );
                $bad['db'][] = $userId;
            }
        }

        $this->prepareGoodBatchResults($good, $users, ['id', 'email']);
        $this->prepareBadBatchResults($bad, $users, ['id']);

        return Router::redirect('/admin/accounts');
    }
}
