<?php
namespace App\Controller;

use App\Model\Database\Creator;
use App\Model\Database\Deleter;
use App\Model\Database\Reader;
use App\Model\Database\Updater;

/**
 * Admin controller for managing user accounts
 */
class AccountManager extends AdminController
{
    public function showAccountListPage()
    {
        try {
            $dbReader = new Reader();
            $users = $dbReader->getAllUsers();
        } catch (\Exception $e) {
            Logger::log('db', 'error', 'Failed to get all users', $e);
            $this->flash('danger', 'Database operation failed');
            $this->showDashboardPage();
        }

        $this->setTemplate('admin_accs.twig');
        $this->addTwigVar('users', $users);
        $this->render();
    }

    public function showAddAccountPage(
        string $username = null, int $roleId = null
    ) {
        $this->setTemplate('add_account.twig');
        $this->addTwigVar('username', $username);
        $this->addTwigVar('role', $roleId);
        $this->render();
    }

    public function addAccount(array $post)
    {
        if (!$this->isClean($post)) {
            $this->flash('danger', 'Username or password contain invalid characters');
            return $this->showAddAccountPage($post['username'], $post['role_id']);
        }

        $username = $post['username'];
        $password = $post['password'];
        $roleId = $post['role_id'] ?? 2;

        try {
            $dbReader = new Reader();
            $user = $dbReader->findUserByUsername($username);

            if (!empty($user)) {
                $this->flash('danger', 'This username is already registered');
                return $this->showAddAccountPage($username, $roleId);
            }

            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $dbCreator = new Creator();
            $user = $dbCreator->createUser($username, $hashed, $roleId);

            $this->flash('success', 'Added successfully');
            return Router::redirect('/admin/accounts');

        } catch (\Exception $e) {
            Logger::log('db', 'error', 'Failed to create user (single)', $e);
            $this->flash('danger', 'Database operation failed');
            return $this->showAddAccountPage($username, $roleId);
        }
    }

    public function batchAddAccounts(array $users)
    {
        try {
            $dbReader = new Reader();
            $dbCreator = new Creator();
        } catch (\Exception $e) {
            Logger::log('db', 'error', 'Failed to open connection', $e);
            $this->flash('danger', 'Database connection failed');
            return Router::redirect('/admin/accounts');
        }

        $good = [];
        $bad = [];

        foreach ($users as $user) {
            if (!$this->isClean($user)) {
                $bad['data'][] = $user;
                continue;
            }

            try {
                $duplicate = $dbReader->findUserByUsername($user['username']);

                if ($duplicate) {
                    $bad['duplicate'][] = $user;
                    continue;
                }

                $password = password_hash($user['password'], PASSWORD_DEFAULT);
                $newUser = $dbCreator->createUser(
                    $user['username'], $password, $user['roleId']
                );

                $good[] = $newUser;

            } catch (\Exception $e) {
                Logger::log(
                    'db', 'error', 'Failed to create user (batch)', $e
                );
                $bad['db'][] = $user;
                continue;
            }
        }

        $this->prepareGoodBatchResults($good, $users, ['id', 'username']);
        $this->prepareBadBatchResults($bad, $users, ['username']);

        return Router::redirect('/admin/accounts');
    }

    public function showChangeAccountPage(int $userId)
    {
        try {
            $dbReader = new Reader();
            $user = $dbReader->findUserById($userId);
        } catch (\Exception $e) {
            Logger::log('db', 'error', 'Failed to find user by id', $e);
            $this->flash('danger', 'Database operation failed');
            Router::redirect('/admin/accounts');
        }

        if (empty($user)) {
            $this->flash('danger', 'User not found');
            Router::redirect('/admin/accounts');
        }

        $this->setTemplate('change_account.twig');
        $this->addTwigVar('user', $user);
        $this->render();
    }

    public function changeAccount(int $userId, array $post)
    {
        if (!$this->isClean($post)) {
            $this->flash('danger', 'Username or password contain invalid characters');
            return $this->showChangeAccountPage($userId);
        }

        $username = $post['username'];
        $roleId = $post['role_id'];
        $password = $post['password'];

        try {
            $dbReader = new Reader();

            $duplicateUser = $dbReader->findUserByUsername($username);
            if (!empty($duplicateUser)
                && $duplicateUser->getId() != $userId) {
                $this->flash('danger', 'This username is already registered');
                return $this->showChangeAccountPage($userId);
            }

            $oldUser = $dbReader->findUserById($userId);
            if (empty($oldUser)) {
                $this->flash('danger', 'Some problem occurred, please try again');
                return $this->showChangeAccountPage($userId);
            }

            $password = empty($password)
                ? $oldUser->getPassword()
                : password_hash($password, PASSWORD_DEFAULT);

            $dbUpdater = new Updater();
            $dbUpdater->updateUser(
                $userId,
                $username,
                $password,
                $roleId,
                $oldUser->getCreatedAt()
            );

            $this->flash('success', 'Account Updated');
            return Router::redirect('/admin/accounts');

        } catch (\Exception $e) {
            Logger::log('db', 'error',
                'Failed to change user account', $e, [
                'user id' => $userId,
                'username' => $username,
            ]);
            $this->flash('danger', 'Database operation failed');
            return $this->showChangeAccountPage($userId);
        }
    }

    public function deleteAccount(int $userId)
    {
        try {
            $dbDeleter = new Deleter();
            $deletedUser = $dbDeleter->deleteUser($userId);
            if (!$deletedUser) {
                $this->flash('danger',
                    "Could not delete user <b>$userId</b>, try again"
                );
                return Router::redirect('/admin/accounts');
            }

            $name = $deletedUser->getUsername();

            $this->flash('success', "User <b>$name</b> deleted");
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
                $deletedUser = $dbDeleter->deleteUser($userId);

                if (!$deletedUser) {
                    $bad['db'][] = $userId;
                } else {
                    $good[] = $deletedUser;
                }
            } catch (\Exception $e) {
                Logger::log('db', 'error',
                    "Failed to delete user (batch)", $e,
                    ['user id' => $userId]
                );
                $bad['db'][] = $userId;
            }
        }

        $this->prepareGoodBatchResults($good, $users, ['id', 'username']);
        $this->prepareBadBatchResults($bad, $users, ['id']);

        return Router::redirect('/admin/accounts');
    }
}
