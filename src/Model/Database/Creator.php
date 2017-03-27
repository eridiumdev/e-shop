<?php
namespace App\Model\Database;

use App\Model\Data\User;
use App\Controller\Logger;

/**
 * Processes INSERT queries
 */
class Creator extends Connection
{
    /**
     * Creates a new user and returns back complete user details
     * @param  string  $email
     * @param  string  $password      - already hashed
     * @param  integer $type          - user or admin
     * @param  string  $registeredAt  - date of registration
     * @return User OR false
     */
    public function createUser(
        string  $email,
        string  $password = null,
        string  $type = null,
        string  $registeredAt = null
    ) {
        if ($password == null) {
            $password = password_hash(BATCH_USER_PASSWORD, PASSWORD_DEFAULT);
        }

        // if ($createdAt == null) {
        //     $createdAt = date("Y-m-d H:i:s");
        // }

        $sql = "INSERT INTO
            users(email, password, type, registeredAt)
            VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email, $password, $type, $registeredAt]);

        return (new Reader())->findUserByEmail($email);
    }
}
