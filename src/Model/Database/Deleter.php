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
        $old = (new Reader())->getUserById($userId);

        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);

        if ($stmt->rowCount() == 0) {
            return false;
        }

        return $old;
    }
}
