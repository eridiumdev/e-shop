<?php
namespace App\Model\Database;

/**
 * Processes UPDATE queries
 */
class Updater extends Connection
{
    /**
     * Changes user password based on user id
     * @param  int $userId
     * @param  string $password - already hashed
     * @return void
     */
    public function changePassword(int $userId, string $password) : void
    {
        $sql = "UPDATE users SET password = ?
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$password, $userId]);
    }

    /**
     * Rewrites user details based on id
     * @param  int    $id       - not updated
     * @param  string $email
     * @param  string $password
     * @param  int    $type
     * @param  string $registeredAt
     * @return bool
     */
    public function updateUser(
        int     $id,
        string  $email,
        string  $password,
        string  $type,
        string  $registeredAt
    ) : bool
    {
        $sql = "UPDATE users
        		SET email = ?,
                    password = ?,
                    type = ?,
                    registeredAt = ?
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$email, $password, $type, $registeredAt, $id]);
    }
}
