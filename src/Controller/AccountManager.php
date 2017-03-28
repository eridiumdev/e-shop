<?php
namespace App\Controller;

use App\Model\Database\Creator;
use App\Model\Database\Deleter;
use App\Model\Database\Reader;
use App\Model\Database\Updater;

use App\Controller\Uploader;

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

        $ymls = Uploader::getFiles(YML_DIRECTORY, ['yml']);
        $this->addTwigVar('files', $ymls);

        $this->setTemplate('admin/accounts.twig');
        $this->addTwigVar('users', $users);
        $this->render();
    }

    public function showAddAccountPage(
        string $email = null, string $type = null
    ) {
        $this->setTemplate('admin/inc/add-account.twig');
        $this->addTwigVar('email', $email);
        $this->addTwigVar('type', $type);
        $this->render();
    }

    public function addAccount(array $post)
    {
        if (!$this->isClean($post)) {
            $this->flash('danger', 'Password contains invalid characters');
            return $this->showAddAccountPage($post['email'], $post['type']);
        }

        $email = $post['email'];
        $password = $post['password'];
        $type = $post['type'];

        try {
            $dbReader = new Reader();
            $user = $dbReader->getUserByEmail($email);

            if (!empty($user)) {
                $this->flash('danger', "Email [$email] is already registered");
                return $this->showAddAccountPage($email, $type);
            }

            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $dbCreator = new Creator();
            $user = $dbCreator->createUser($email, $hashed, $type);

            $this->flash('success', "New user [$email] added successfully");
            return Router::redirect('/admin/accounts');

        } catch (\Exception $e) {
            Logger::log('db', 'error', 'Failed to create user (single)', $e);
            $this->flash('danger', 'Database operation failed');
            return $this->showAddAccountPage($email, $type);
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
            if (empty($user['email']) || empty($user['type'])) {
                $this->flash(
                    'danger',
                    'Wrong file selected! (or some required details are missing)'
                );
                return Router::redirect('/admin/accounts');
            }

            if (!$this->isClean($user)) {
                $bad['data'][] = $user;
                continue;
            }

            $email = $user['email'];
            $type = $user['type'];

            try {
                $duplicate = $dbReader->getUserByEmail($email);

                if ($duplicate) {
                    $bad['duplicate'][] = $user;
                    continue;
                }

                $password = password_hash(BATCH_USER_PASSWORD, PASSWORD_DEFAULT);
                $newUser = $dbCreator->createUser($email, $password, $type);

                $good[] = $newUser;

            } catch (\Exception $e) {
                Logger::log('db', 'error', 'Failed to create user (batch)', $e);
                $bad['db'][] = $user;
                continue;
            }
        }

        $this->prepareGoodBatchResults($good, $users, ['id', 'email', 'type']);
        $this->prepareBadBatchResults($bad, $users, ['email']);

        return Router::redirect('/admin/accounts');
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

        $this->setTemplate('admin/inc/change-account.twig');
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
