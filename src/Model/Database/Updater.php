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
        string  $username,
        string  $email,
        string  $password,
        string  $type,
        string  $registeredAt
    ) {
        $sql = "UPDATE users
        		SET username = ?,
                    email = ?,
                    password = ?,
                    type = ?,
                    registeredAt = ?
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$username, $email, $password, $type, $registeredAt, $id]);
    }

    public function updateUserShipping(
        int     $userId,
        string  $name,
        string  $phone,
        string  $address
    ) {
        $sql = "UPDATE shipping
        		SET name = ?,
                    phone = ?,
                    address = ?
                WHERE shipping.id = (
                    SELECT shipId FROM user_shipping WHERE userId = ?
                )";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$name, $phone, $address, $userId]);
    }

    public function updateProduct(
        int     $prodId,
        string  $name,
        string  $desc,
        int     $catId,
        float   $price,
        string  $mainPic
    ) {
        $sql = "UPDATE products
                SET name = ?,
                    description = ?,
                    catId = ?,
                    price = ?,
                    mainPic = ?
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$name, $desc, $catId, $price, $mainPic, $prodId]);
    }

    public function updateProductDiscount(int $prodId, float $amount)
    {
        $sql = "UPDATE product_discount SET amount = ? WHERE prodId = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$amount, $prodId]);
    }

    public function updateProductSpec(int $prodId, int $specId, string $value)
    {
        $sql = "UPDATE product_specs SET value = ?
                WHERE prodId = ? AND specId = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$value, $prodId, $specId]);
    }

    public function updateCategory(
        int     $catId,
        string  $name,
        string  $description,
        string  $uri
    ) {
        $sql = "UPDATE categories SET name = ?, description = ?, uri = ?
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$name, $description, $uri, $catId]);
    }

    public function updateSection(
        int     $sectId,
        string  $name,
        string  $uri,
        string  $description,
        int     $maxProducts
    ) {
        $sql = "UPDATE sections
                SET name = ?, uri = ?, description = ?, maxProducts = ?
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$name, $uri, $description, $maxProducts, $sectId]);
    }

    public function updateSpec(
        int     $specId,
        string  $name,
        string  $type,
        bool    $isRequired,
        bool    $isFilter
    ) {
        $sql = "UPDATE specs
                SET name = ?, type = ?, isRequired = ?, isFilter = ?
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$name, $type, $isRequired, $isFilter, $specId]);
    }

    public function updateCartItem(int $userId, int $prodId, int $qty) {
        $sql = "UPDATE cart SET qty = ? WHERE userId = ? AND prodId = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$qty, $userId, $prodId]);
    }

    public function updateOrderStatus(int $orderId, int $statusId) {
        $sql = "UPDATE orders SET statusId = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$statusId, $orderId]);
    }
}
