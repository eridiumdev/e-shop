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
        string  $username = null,
        string  $email = null,
        string  $password = null,
                $type = 'guest',
        string  $registeredAt = null
    ) {
        if ($password == null) {
            $password = password_hash(BATCH_USER_PASSWORD, PASSWORD_DEFAULT);
        }

        if ($registeredAt == null) {
            $registeredAt = date("Y-m-d H:i:s");
        }

        $sql = "INSERT INTO
                users(username, email, password, type, registeredAt)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$username, $email, $password, $type, $registeredAt]);

        return (new Reader())->getUserById($this->db->lastInsertId());
    }

    public function createUserShipping(
        string  $userId,
        string  $name,
        string  $phone,
        string  $address
    ) {
        if ($shipping = $this->createShipping($name, $phone, $address)) {
            $sql = "INSERT INTO user_shipping(shipId, userId)
                    VALUES (?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$shipping->getId(), $userId]);

            return $shipping;
        } else {
            return false;
        }
    }

    public function createShipping(string $name, string $phone, string $address)
    {
        $sql = "INSERT INTO shipping(name, phone, address)
                VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$name, $phone, $address]);

        return (new Reader())->getShippingById($this->db->lastInsertId());
    }

    public function createProduct(
        string   $name,
        string   $description,
        int      $catId,
        float    $price,
        string   $mainPic
    ) {
        $sql = "INSERT INTO
                products(name, description, catId, price, mainPic)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$name, $description, $catId, $price, $mainPic]);

        return (new Reader())->getProductByName($name);
    }

    public function createProductDiscount(int $prodId, float $amount)
    {
        $sql = "INSERT INTO product_discount(prodId, amount) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute([$prodId, $amount])) {
            return new Discount($amount);
        } else {
            return false;
        }
    }

    public function createProductPic(int $prodId, string $path)
    {
        $sql = "INSERT INTO product_pics(prodId, path) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute([$prodId, $path])) {
            return new Picture($path);
        } else {
            return false;
        }
    }

    public function addSpecToProduct(int $prodId, int $specId, string $value)
    {
        $sql = "INSERT INTO product_specs(prodId, specId, value)
                VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute([$prodId, $specId, $value])) {
            return (new Reader())->getProductSpec($prodId, $specId);
        } else {
            return false;
        }
    }

    public function createCategory(
        string  $name,
        string  $description,
        string  $uri
    ) {
        $sql = "INSERT INTO categories(name, description, uri)
                VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$name, $description, $uri]);

        if ($newId = $this->db->lastInsertId()) {
            return (new Reader())->getCategoryById($newId);
        } else {
            return false;
        }
    }

    public function createSpec(
        string  $name,
        string  $type,
        bool    $isRequired,
        bool    $isFilter
    ) {
        $sql = "INSERT INTO specs(name, type, isRequired, isFilter)
                VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$name, $type, $isRequired, $isFilter]);

        if ($newId = $this->db->lastInsertId()) {
            return (new Reader())->getSpecById($newId);
        } else {
            return false;
        }
    }

    public function addCategoryToSpec(int $specId, int $catId)
    {
        $sql = "INSERT INTO spec_cats(specId, catId)
                VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute([$specId, $catId])) {
            return true;
        } else {
            return false;
        }
    }

    public function addCartItem(int $userId, int $prodId, int $qty)
    {
        $sql = "INSERT INTO cart(userId, prodId, qty) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute([$userId, $prodId, $qty])) {
            return true;
        } else {
            return false;
        }
    }

    public function createOrder(
        int     $userId,
        int     $shipId,
        int     $deliveryId,
        int     $paymentId,
        array   $items,
        string  $date = null,
        $statusId = null
    ) {
        // Either all items and order itself are generated
        // or none of the actions take place
        $this->db->beginTransaction();

        if ($date == null) {
            $date = date("Y-m-d H:i:s");
        }

        if (!isset($statusId)) {
            $statusId = DEFAULT_STATUS;
        }

        try {
            $sql = "INSERT INTO
                    orders(userId, shipId, date, statusId, deliveryId, paymentId)
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $userId,
                $shipId,
                $date,
                $statusId,
                $deliveryId,
                $paymentId
            ]);

            $orderId = $this->db->lastInsertId();

            foreach ($items as $item) {
                $this->addOrderItem($orderId, $item->getId(), $item->getQty());
            }

            $this->db->commit();
            return $orderId;

        } catch (\Exception $e) {
            Logger::log('db', 'error', "Failed to create order", $e);
            $this->db->rollBack();
            return false;
        }
    }

    public function addOrderItem(int $orderId, int $prodId, int $qty)
    {
        $sql = "INSERT INTO order_items(orderId, prodId, qty)
                VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute([$orderId, $prodId, $qty])) {
            return true;
        } else {
            return false;
        }
    }
}
