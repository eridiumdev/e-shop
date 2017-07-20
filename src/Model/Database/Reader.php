<?php
namespace App\Model\Database;

use App\Model\Data\Category;
use App\Model\Data\Delivery;
use App\Model\Data\Discount;
use App\Model\Data\Order;
use App\Model\Data\OrderItem;
use App\Model\Data\Payment;
use App\Model\Data\Picture;
use App\Model\Data\Product;
use App\Model\Data\Section;
use App\Model\Data\Shipping;
use App\Model\Data\Spec;
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

    public function getProductsByCatId(int $catId)
    {
        $sql = "SELECT * FROM products WHERE catId = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$catId]);

        $products = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $product = new Product(
                 $row['id'],
                 $row['name'],
                 $row['description'],
                 $row['price']
            );

            $product->setCategory($this->getCategoryById($row['catId']));
            $product->setMainPic(new Picture($row['mainPic']));

            $discount = $this->getProductDiscount($row['id']);
            if (!empty($discount)) {
                $product->setDiscount($discount);
            }

            $products[] = $product;
        }

        return $products;
    }

    public function getProductDiscount(int $prodId)
    {
        $sql = "SELECT amount FROM product_discount WHERE prodId = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$prodId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            return false;
        }

        return new Discount($row['amount']);
    }

    public function getAllCategories()
    {
        $sql = "SELECT * FROM categories";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $categories = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $categories[] = new Category(
                $row['id'],
                $row['name'],
                $row['description'],
                $row['uri']
            );
        }

        return $categories;
    }

    public function getCategoryById(int $id)
    {
        $sql = "SELECT * FROM categories WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            return false;
        }

        return new Category(
            $row['id'],
            $row['name'],
            $row['description'],
            $row['uri']
        );
    }

    public function getCategoryByUri(string $uri)
    {
        $sql = "SELECT * FROM categories WHERE uri = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$uri]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            return false;
        }

        return new Category(
            $row['id'],
            $row['name'],
            $row['description'],
            $row['uri']
        );
    }
}
