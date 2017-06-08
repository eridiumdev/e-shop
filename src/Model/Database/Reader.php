<?php
namespace App\Model\Database;

use App\Model\Data\User;

/**
 * Processes SELECT queries
 */
class Reader extends Connection
{
    /**
     * Returns existing user data
     * @param  string $username
     * @return User OR false
     */
    public function getUserByUsername(string $username)
    {
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$username]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            return false;
        }

        return new User(
            $row['id'],
            $row['username'],
            $row['email'],
            $row['password'],
            $row['type'],
            $row['registeredAt']
        );
    }

    public function getUserShipping(int $userId)
    {
        $sql = "SELECT * FROM user_shipping WHERE userId = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            return false;
        }

        return [
            $row['name'],
            $row['phone'],
            $row['address']
        ];
    }

    /**
     * Returns existing user data
     * @param  string $email
     * @return User OR false
     */
    public function getUserByEmail(string $email)
    {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            return false;
        }

        return new User(
            $row['id'],
            $row['username'],
            $row['email'],
            $row['password'],
            $row['type'],
            $row['registeredAt']
        );
    }

    /**
     * Returns existing user data
     * @param  int $userId
     * @return User OR false
     */
    public function getUserById(int $userId)
    {
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            return false;
        }

        return new User(
            $row['id'],
            $row['username'],
            $row['email'],
            $row['password'],
            $row['type'],
            $row['registeredAt']
        );
    }

    /**
     * Returns array with all users, full details
     * @return array of Users, empty or not
     */
    public function getAllUsers() : array
    {
        $sql = "SELECT * FROM users";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $users = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $users[] = new User(
                $row['id'],
                $row['username'],
                $row['email'],
                $row['password'],
                $row['type'],
                $row['registeredAt']
            );
        }

        return $users;
    }
}
