<?php
namespace App\Model\Database;

use App\Model\Data\Category;
use App\Model\Data\Cart;
use App\Model\Data\CartItem;
use App\Model\Data\Delivery;
use App\Model\Data\Discount;
use App\Model\Data\Order;
use App\Model\Data\OrderItem;
use App\Model\Data\Payment;
use App\Model\Data\Param;
use App\Model\Data\Picture;
use App\Model\Data\Product;
use App\Model\Data\Section;
use App\Model\Data\Shipping;
use App\Model\Data\Spec;
use App\Model\Data\User;

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

        $this->db->beginTransaction();

        try {
            // Delete shipping
            $this->deleteUserShipping($userId);

            // Delete user
            $sql = "DELETE FROM users WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);

            if ($stmt->rowCount() == 0) {
                return false;
            }

            // Commit transaction
            $this->db->commit();
            return $old;

        } catch (\Exception $e) {
            Logger::log(
                'db',
                'error',
                "Failed to delete user '$userId', rolled back transaction",
                $e
            );
            $this->db->rollBack();
            return false;
        }
    }

    public function deleteUserShipping(int $userId)
    {
        $sql = "DELETE FROM shipping WHERE userId = ?";
        $stmt = $this->db->prepare($sql);
        return ($stmt->execute([$userId]));
    }

    public function deleteProduct(int $prodId)
    {
        $old = (new Reader())->getFullProductById($prodId);
        if (!$old) return false;

        // Turn autocommit off
        $this->db->beginTransaction();

        try {
            // Delete discount
            $this->deleteProductDiscount($prodId);

            // Remove all pics (not deleted physically)
            $this->deleteProductPics($prodId);

            // Remove all specs (associated values)
            $this->deleteProductSpecs($prodId);

            // In the end delete product entry
            $sql = "DELETE FROM products WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$prodId]);

            // Commit transaction
            $this->db->commit();
            return $old;

        } catch (\Exception $e) {
            Logger::log(
                'db',
                'error',
                "Failed to delete product '$prodId', rolled back transaction",
                $e
            );
            $this->db->rollBack();
            return false;
        }
    }

    public function deleteProductDiscount(int $prodId)
    {
        $sql = "DELETE FROM product_discount WHERE prodId = ?";
        $stmt = $this->db->prepare($sql);
        return ($stmt->execute([$prodId]));
    }

    public function deleteProductPics(int $prodId)
    {
        $sql = "DELETE FROM product_pics WHERE prodId = ?";
        $stmt = $this->db->prepare($sql);
        return ($stmt->execute([$prodId]));
    }

    public function deleteProductSpecs(int $prodId)
    {
        $sql = "DELETE FROM product_specs WHERE prodId = ?";
        $stmt = $this->db->prepare($sql);
        return ($stmt->execute([$prodId]));
    }

    public function deleteCategory(int $catId)
    {
        $old = (new Reader())->getCategoryById($catId);

        $sql = "DELETE FROM categories WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$catId]);

        if ($stmt->rowCount() == 0) {
            return false;
        }

        return $old;
    }

    public function deleteSpec(int $specId)
    {
        $old = (new Reader())->getSpecById($specId);

        $this->db->beginTransaction();

        try {
            $this->deleteSpecCategories($specId);

            $sql = "DELETE FROM specs WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$specId]);

            if ($stmt->rowCount() == 0) {
                return false;
            }

            $this->db->commit();
            return $old;

        } catch (\Exception $e) {
            Logger::log(
                'db',
                'error',
                "Failed to delete spec '$specId', rolled back transaction",
                $e
            );
            $this->db->rollBack();
            return false;
        }
    }

    public function deleteSpecCategories(int $specId)
    {
        $sql = "DELETE FROM spec_cats WHERE specId = ?";
        $stmt = $this->db->prepare($sql);
        return ($stmt->execute([$specId]));
    }

    public function removeCartItem(int $userId, int $prodId)
    {
        $sql = "DELETE FROM cart WHERE userId = ? AND prodId = ?";
        $stmt = $this->db->prepare($sql);
        return ($stmt->execute([$userId, $prodId]));
    }
}
