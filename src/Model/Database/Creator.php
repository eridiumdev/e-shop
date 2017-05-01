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
        string  $username,
        string  $email,
        string  $password = null,
                $type = 'user',
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

        return (new Reader())->getUserByEmail($email);
    }

    /**
     * Creates and returns full product, rolls back transaction in case of failure
     * @param  string $name
     * @param  string $description
     * @param  float  $price
     * @param  int    $catId       category id
     * @param  string $mainPic
     * @param  float  $discount    can be null
     * @param  array  $pics
     * @param  array  $specs
     * @return Product
     */
    public function createFullProduct(
        string   $name,
        string   $description,
        float    $price,
        int      $catId,
        string   $mainPic,
        float    $discount,
        array    $pics,
        array    $specs
    ) {
        // Turn autocommit off
        $this->db->beginTransaction();

        try {
            // Init Reader to get category details and pictures
            $dbReader = new Reader();

            // Init product with basic details
            $newProduct = $this->createProduct(
                $name, $description, $price, $catId, $mainPic
            );
            $prodId = $newProduct->getId();

            // Set complete category
            $category = $dbReader->getCategoryById($catId);
            if (empty($category)) {
                throw new \Exception;   // category is invalid
            } else {
                $newProduct->setCategory($category);
            }

            // Set main picture
            if (!empty($mainPic)) {
                $newProduct->setMainPic($mainPic);
            }

            // If product is on sale, set discount
            if (!empty($discount)) {
                $newDiscount = $this->createDiscount($prodId, $discount);
                $newProduct->setDiscount($newDiscount);
            }

            // Add all pictures
            foreach ($pics as $picPath) {
                $pic = $dbReader->getPicture($picPath);
                if (!$pic) {
                    $pic = $this->createPicture($prodId, $picPath);
                }
                $newProduct->addPicture($pic);
            }

            // Add all specs
            foreach ($specs as $specId => $specVal) {
                $spec = $dbReader->getSpecById($specId);
                $spec->setValue($specVal);
                $newProduct->addSpec($spec);
            }

            // Commit transaction
            $this->db->commit();
            return $newProduct;

        } catch (\Exception $e) {
            Logger::log(
                'db', 'error',
                "Failed to create full product $name, rolled back transaction",
                $e
            );
            $this->db->rollBack();
            return false;
        }
    }
}
