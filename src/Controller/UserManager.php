<?php
namespace App\Controller;

use App\Model\Database\Creator;
use App\Model\Database\Deleter;
use App\Model\Database\Reader;
use App\Model\Database\Updater;

use App\Controller\Uploader;

/**
 * Admin controller for managing user users
 */
class UserManager extends AdminController
{
    public function showUsersListPage()
    {
        try {
            $dbReader = new Reader();
            $users = $dbReader->getAllUsers();
        } catch (\Exception $e) {
            Logger::log('db', 'error', 'Failed to get all users', $e);
            $this->flash('danger', 'Database operation failed');
            $this->showDashboardPage();
        }

        $this->addTwigVar('users', $users);
        $this->setTemplate('admin/users.twig');
        $this->render();
    }

    public function showAddUserPage(
        string $email = null, string $type = null
    ) {
        $this->addTwigVar('email', $email);
        $this->addTwigVar('type', $type);
        $this->setTemplate('admin/users/add-user.twig');
        $this->render();
    }

    public function addUser(array $post)
    {
        if (!$this->isClean($post)) {
            $this->flash('danger', 'Some field contain invalid characters');
            Router::redirect('/admin/users/add');
        }

        $username = $post['username'];
        $email = $post['email'];
        $password = $post['password'];
        $type = $post['type'];

        try {
            $dbReader = new Reader();

            // Duplicate username
            if ($user = $dbReader->getUserByUsername($username)) {
                $this->flash('danger', "User '$username' is already registered");
                Router::redirect('/admin/users/add');
            }

            // Duplicate email
            if ($user = $dbReader->getUserByEmail($email)) {
                $this->flash('danger', "Email '$email' is already registered");
                Router::redirect('/admin/users/add');
            }

            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $dbCreator = new Creator();
            $user = $dbCreator->createUser($username, $email, $hashed, $type);

            $this->flash('success', "New user '$username' added successfully");
            Router::redirect('/admin/users');

        } catch (\Exception $e) {
            Logger::log('db', 'error', "Failed to create user '$username'", $e);
            $this->flash('danger', 'Database operation failed');
            Router::redirect('/admin/users/add');
        }
    }

    public function batchAddUsers(array $users)
    {
        try {
            $dbReader = new Reader();
            $dbCreator = new Creator();
        } catch (\Exception $e) {
            Logger::log('db', 'error', 'Failed to open connection', $e);
            $this->flash('danger', 'Database connection failed');
            return Router::redirect('/admin/users');
        }

        $good = [];
        $bad = [];

        foreach ($users as $user) {
            if (empty($user['email']) || empty($user['type'])) {
                $this->flash(
                    'danger',
                    'Wrong file selected! (or some required details are missing)'
                );
                return Router::redirect('/admin/users');
            }

            if (!$this->isClean($user)) {
                $bad['data'][] = $user;
                continue;
            }

            $username = $user['username'];
            $email = $user['email'];
            $type = $user['type'];

            try {
                $duplicate = $dbReader->getUserByEmail($email);

                if ($duplicate) {
                    $bad['duplicate'][] = $user;
                    continue;
                }

                $password = password_hash(BATCH_USER_PASSWORD, PASSWORD_DEFAULT);
                $newUser = $dbCreator->createUser($username, $email, $password, $type);

                $good[] = $newUser;

            } catch (\Exception $e) {
                Logger::log('db', 'error', 'Failed to create user (batch)', $e);
                $bad['db'][] = $user;
                continue;
            }
        }

        $this->prepareGoodBatchResults($good, $users, ['id', 'email', 'type']);
        $this->prepareBadBatchResults($bad, $users, ['email']);

        return Router::redirect('/admin/users');
    }

    public function showUserPage(int $userId)
    {
        try {
            $dbReader = new Reader();
            $user = $dbReader->getFullUserById($userId);
        } catch (\Exception $e) {
            Logger::log('db', 'error', 'Failed to find user by id', $e);
            $this->flash('danger', 'Database operation failed');
            Router::redirect('/admin/users');
        }

        if (empty($user)) {
            $this->flash('danger', 'User not found');
            Router::redirect('/admin/users');
        }

        $this->setTemplate('admin/users/view-user.twig');
        $this->addTwigVar('user', $user);
        $this->render();
    }

    public function updateUser(int $userId, array $post)
    {
        if (!$this->isClean($post)) {
            $this->flash('danger', 'Password contains invalid characters');
            Router::redirect("/admin/users/view/$userId");
        }

        $username = $post['username'];
        $email = $post['email'];
        $password = $post['password'];
        $type = $post['type'];

        $name = $post['name'];
        $phone = $post['phone'];
        $address = $post['address'];

        try {
            $dbReader = new Reader();
            $old = $dbReader->getFullUserById($userId);

            if (empty($old)) {
                $this->flash('danger', 'Some problem occurred, please try again');
                return $this->showUserPage($userId);
            }

            if ($old->getUsername() != $username) {
                // check for duplicate username, only if username is being updated
                $duplicate = $dbReader->getUserByUsername($username);
                if (!empty($duplicate)) {
                    $this->flash('danger', "User [$username] is already registered");
                    Router::redirect("/admin/users/view/$userId");
                }
            }

            if ($old->getEmail() != $email) {
                // check for duplicate email, only if email is being updated
                $duplicate = $dbReader->getUserByEmail($email);
                if (!empty($duplicate)) {
                    $this->flash('danger', "Email [$email] is already registered");
                    Router::redirect("/admin/users/view/$userId");
                }
            }

            $password = empty($password) ?
                $old->getPassword() :
                password_hash($password, PASSWORD_DEFAULT);

            $dbUpdater = new Updater();
            $dbUpdater->updateUser(
                $userId,
                $username,
                $email,
                $password,
                $type,
                $old->getRegisteredAt()
            );

            if (empty($old->getShipping()) &&
                (!empty($name) || !empty($phone) || !empty($address))
            ) {
                // Create new shipping for the user
                $dbCreator = new Creator();
                $dbCreator->createUserShipping($userId, $name, $phone, $address);
            } elseif (empty($name) && empty($phone) && empty($address)) {
                // Remove shipping from the user
                $dbDeleter = new Deleter();
                $dbDeleter->deleteUserShipping($userId);
            } else {
                // Update existing shipping
                $dbUpdater->updateUserShipping($userId, $name, $phone, $address);
            }

            $this->flash('success', "[$username] updated successfully");
            Router::redirect('/admin/users');

        } catch (\Exception $e) {
            Logger::log('db', 'error',
                'Failed to change user user', $e, [
                'user id' => $userId,
                'username' => $email,
            ]);
            $this->flash('danger', 'Database operation failed');
            Router::redirect("/admin/users/view/$userId");
        }
    }

    public function deleteUser(int $userId)
    {
        try {
            $dbDeleter = new Deleter();
            $deleted = $dbDeleter->deleteUser($userId);
            if (!$deleted) {
                $this->flash('danger',
                    "Could not delete user [$userId], try again"
                );
                return Router::redirect('/admin/users');
            }

            $email = $deleted->getEmail();

            $this->flash('success', "User [$email] deleted");
            return Router::redirect('/admin/users');

        } catch (\Exception $e) {
            Logger::log('db', 'error',
                "Failed to delete user (single)", $e,
                ['user id' => $userId]
            );
            $this->flash('danger', 'Database operation failed');
            return Router::redirect('/admin/users');
        }
    }

    public function deleteUsers(array $users)
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

        return Router::redirect('/admin/users');
    }
}
