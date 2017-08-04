<?php
namespace App\Model\Database;

use App\Model\Data\Category;
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
            $product = $this->getProductById($row['id']);
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

    public function getProductById(int $prodId)
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
                $row['isFilter'],
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
            $product = $this->getProductById($row['id']);
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
            $row['isFilter'],
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

    public function getFullCategoryById(int $id)
    {
        $sql = "SELECT * FROM categories WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);

        if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $category = new Category(
                $row['id'],
                $row['name'],
                $row['description'],
                $row['uri']
            );

            if ($products = $this->getCategoryProducts($row['id'])) {
                foreach ($products as $product) {
                    $category->addProduct($product);
                }
            }

            if ($specs = $this->getCategorySpecs($row['id'])) {
                foreach ($specs as $spec) {
                    $category->addSpec($spec);
                }
            }

            return $category;
        } else {
            return false;
        }
    }

    public function getCategoryById(int $id)
    {
        $sql = "SELECT * FROM categories WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);

        if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $category = new Category(
                $row['id'],
                $row['name'],
                $row['description'],
                $row['uri']
            );

            return $category;
        } else {
            return false;
        }
    }

    public function getCategoryByName(string $name)
    {
        $sql = "SELECT id FROM categories WHERE name = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$name]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            return $this->getFullCategoryById($row['id']);
        } else {
            return false;
        }
    }

    public function getCategoryByUri(string $uri)
    {
        $sql = "SELECT id FROM categories WHERE uri = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$uri]);

        if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            return $this->getFullCategoryById($row['id']);
        } else {
            return false;
        }
    }

    public function getCategoryProducts(int $catId)
    {
        $sql = "SELECT id FROM products WHERE catId = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$catId]);

        $products = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            if ($product = $this->getProductById($row['id'])) {
                $products[] = $product;
            }
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
            if ($spec = $this->getSpecById($row['id'])) {
                $specs[] = $spec;
            }
        }

        return $specs;
    }

    public function getAllSections()
    {
        $sql = "SELECT id FROM sections";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $sections = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            if ($section = $this->getFullSectionById($row['id'])) {
                $sections[] = $section;
            }
        }

        return $sections;
    }

    public function getFullSectionById(int $sectId)
    {
        $sql = "SELECT * FROM sections WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$sectId]);

        if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $section = new Section(
                $row['id'],
                $row['name'],
                $row['uri'],
                $row['description'],
                $row['maxProducts']
            );

            if ($param = $this->getSectionParam($row['id'])) {
                $section->setParam($param);
            }

            if ($products = $this->getSectionProducts(
                $param->getName(), $row['maxProducts'])
            ) {
                foreach ($products as $product) {
                    $section->addProduct($product);
                }
            }

            return $section;
        } else {
            return false;
        }
    }

    public function getSectionById(int $sectId)
    {
        $sql = "SELECT * FROM sections WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$sectId]);

        if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $section = new Section(
                $row['id'],
                $row['name'],
                $row['uri'],
                $row['description'],
                $row['maxProducts']
            );

            if ($param = $this->getSectionParam($row['id'])) {
                $section->setParam($param);
            }

            return $section;
        } else {
            return false;
        }
    }

    public function getSectionParam(int $sectId)
    {
        $sql = "SELECT * FROM section_params WHERE sectId = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$sectId]);

        if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            return new Param($row['paramId'], $row['paramName']);
        } else {
            return false;
        }
    }

    public function getSectionByName(string $name)
    {
        $sql = "SELECT * FROM sections WHERE name = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$name]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            return false;
        }

        return $this->getFullSectionById($row['id']);
    }

    public function getSectionByUri(string $uri)
    {
        $sql = "SELECT * FROM sections WHERE uri = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$uri]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            return false;
        }

        return $this->getFullSectionById($row['id']);
    }

    public function getSectionProducts(string $paramName, int $maxProducts)
    {
        $sql = '';
        switch ($paramName) {
            case 'featured' :
                $sql .= "SELECT prodId as id FROM featured_products
                         ORDER BY rand()
                         LIMIT ?";
                break;
            case 'sale' :
                $sql .= "SELECT id FROM products JOIN product_discount
                         ON products.id = product_discount.prodId
                         ORDER BY rand()
                         LIMIT ?";
                break;
            case 'best' :
                $sql .= "SELECT prodId as id, sum(qty) FROM order_items
                         GROUP BY id
                         ORDER BY sum(qty) DESC
                         LIMIT ?";
                break;
            case 'new' :
                $sql .= "SELECT id FROM products
                         ORDER BY id DESC
                         LIMIT ?";
                break;
            default:
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(1, $maxProducts, \PDO::PARAM_INT);
        $stmt->execute();

        $products = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            if ($product = $this->getProductById($row['id'])) {
                $products[] = $product;
            }
        }

        return $products;
    }

    public function getAllSpecs() : array
    {
        $sql = "SELECT id FROM specs";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $specs = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            if ($spec = $this->getFullSpecById($row['id'])) {
                $specs[] = $spec;
            }
        }

        return $specs;
    }

    public function getFullSpecById(int $specId)
    {
        $sql = "SELECT * FROM specs WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$specId]);

        if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $spec = new Spec(
                $row['id'],
                $row['name'],
                $row['type'],
                $row['isRequired'],
                $row['isFilter']
            );

            if ($categories = $this->getSpecCategories($specId)) {
                foreach ($categories as $category) {
                    $spec->addCategory($category);
                }
            }

            if ($values = $this->getSpecValues($specId)) {
                $spec->setValues($values);
            }

            return $spec;
        } else {
            return false;
        }
    }

    public function getSpecById(int $specId)
    {
        $sql = "SELECT * FROM specs WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$specId]);

        if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $spec = new Spec(
                $row['id'],
                $row['name'],
                $row['type'],
                $row['isRequired'],
                $row['isFilter']
            );

            if ($values = $this->getSpecValues($specId)) {
                $spec->setValues($values);
            }

            return $spec;
        } else {
            return false;
        }
    }

    public function getSpecCategories(int $specId)
    {
        $sql = "SELECT catId FROM spec_cats WHERE specId = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$specId]);

        $categories = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            if ($category = $this->getCategoryById($row['catId'])) {
                $categories[] = $category;
            }
        }

        return $categories;
    }

    public function getSpecValues(int $specId)
    {
        $sql = "SELECT distinct(value) FROM product_specs WHERE specId = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$specId]);

        $values = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $values[] = $row['value'];
        }

        return $values;
    }
}
