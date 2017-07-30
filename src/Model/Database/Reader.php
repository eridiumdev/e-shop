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

    public function getAllProducts() : array
    {
        $sql = "SELECT id FROM products";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $products = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $product = $this->getFullProductById($row['id']);
            if (!empty($product)) {
                $products[] = $product;
            }
        }

        return $products;
    }

    public function getFullProductById(int $prodId)
    {
        $sql = "SELECT * FROM products WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$prodId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            return false;
        }

        $product = new Product(
             $row['id'],
             $row['name'],
             $row['description'],
             $row['price']
        );

        $prodId = $row['id'];

        if ($category = $this->getCategoryById($row['catId'])) {
            $product->setCategory($category);
        }

        $product->setMainPic(new Picture($row['mainPic']));

        if ($discount = $this->getProductDiscount($prodId)) {
            $product->setDiscount($discount);
        }

        $product->setPics($this->getProductPics($prodId));
        $product->setSpecs($this->getProductSpecs($prodId));

        return $product;
    }

    public function getProductPics(int $prodId)
    {
        $sql = "SELECT * FROM product_pics WHERE prodId = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$prodId]);

        $pics = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $pic = new Picture($row['path']);
            $pics[] = $pic;
        }

        return $pics;
    }

    public function getProductSpecs(int $prodId)
    {
        $sql = "SELECT * FROM product_specs JOIN specs
                ON product_specs.specId = specs.id
                WHERE prodId = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$prodId]);

        $specs = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $spec = new Spec(
                $row['id'],
                $row['name'],
                $row['type'],
                $row['isRequired'],
                $row['value']
            );
            $specs[$row['id']] = $spec;
        }

        return $specs;
    }

    public function getProductsByCatId(int $catId)
    {
        $sql = "SELECT id FROM products WHERE catId = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$catId]);

        $products = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $product = $this->getFullProductById($row['id']);
            if (!empty($product)) {
                $products[] = $product;
            }
        }

        return $products;
    }

    public function getProductByName(string $name)
    {
        $sql = "SELECT id FROM products WHERE name = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$name]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            return false;
        } else {
            return $this->getFullProductById($row['id']);
        }
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

    public function getProductSpec(int $prodId, int $specId)
    {
        $sql = "SELECT * FROM product_specs JOIN specs
                ON product_specs.specId = specs.id
                WHERE prodId = ? AND specId = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$prodId, $specId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            return false;
        }

        $spec = new Spec(
            $row['id'],
            $row['name'],
            $row['type'],
            $row['isRequired'],
            $row['value']
        );

        return $spec;
    }

    public function getAllCategories()
    {
        $sql = "SELECT id FROM categories";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $categories = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            if ($category = $this->getFullCategoryById($row['id'])) {
                $categories[] = $category;
            }
        }

        return $categories;
    }

    public function getFullCategoryById(int $catId)
    {
        $sql = "SELECT * FROM categories WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$catId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            return false;
        }

        $category = new Category(
             $row['id'],
             $row['name'],
             $row['description'],
             $row['uri']
        );

        $catId = $row['id'];

        $products = $this->getCategoryProducts($catId);
        foreach ($products as $product) {
            $category->addProduct($product);
        }

        $specs = $this->getCategorySpecs($catId);
        foreach ($specs as $spec) {
            $category->addSpec($spec);
        }

        return $category;
    }

    public function getCategoryProducts(int $catId)
    {
        $sql = "SELECT * FROM products WHERE catId = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$catId]);

        $products = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $products[] = new Product(
                $row['id'],
                $row['name'],
                $row['description'],
                $row['price']
            );
        }

        return $products;
    }

    public function getCategorySpecs(int $catId)
    {
        $sql = "SELECT * FROM specs JOIN spec_cats
                ON specs.id = spec_cats.specId
                WHERE catId = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$catId]);

        $specs = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $specs[] = new Spec(
                $row['id'],
                $row['name'],
                $row['type'],
                $row['isRequired']
            );
        }

        return $specs;
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

    public function getCategoryByName(string $name)
    {
        $sql = "SELECT * FROM categories WHERE name = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$name]);
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

    public function getAllSpecs() : array
    {
        $sql = "SELECT * FROM specs";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $specs = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $specs[] = new Spec(
                $row['id'],
                $row['name'],
                $row['type'],
                $row['isRequired']
            );
        }

        return $specs;
    }
}
