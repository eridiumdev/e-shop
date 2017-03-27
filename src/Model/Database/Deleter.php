<?php
namespace App\Model\Database;

/**
 * Processes DELETE queries
 */
class Deleter extends Connection
{
    /**
     * Deletes user based on id
     * @param  int   $userId
     * @return deleted user OR false
     */
    public function deleteUser(int $userId)
    {
        $oldUser = (new Reader())->findUserById($userId);

        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);

        if ($stmt->rowCount() == 0) {
            return false;
        }

        return $oldUser;
    }
}
