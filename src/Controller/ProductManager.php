<?php
namespace App\Controller;

use App\Model\Database\Creator;
use App\Model\Database\Deleter;
use App\Model\Database\Reader;
use App\Model\Database\Updater;

/**
 * Admin controller for managing orders
 */
class ProductManager extends AdminController
{
    public function showProductsListPage()
    {
        try {
            $dbReader = new Reader();
            $products = $dbReader->getAllProducts();
        } catch (\Exception $e) {
            Logger::log('db', 'error', 'Failed to get all products', $e);
            $this->flash('danger', 'Database operation failed');
            $this->showDashboardPage();
        }

        $this->addTwigVar('products', $products);
        $this->setTemplate('admin/products.twig');
        $this->render();
    }

    public function showAddProductPage()
    {
        try {
            $dbReader = new Reader();
            $categories = $dbReader->getAllCategories();
            $specs = $dbReader->getAllSpecs();
        } catch (\Exception $e) {
            Logger::log('db', 'error', "Failed to get categories or specs", $e);
            $this->flash('danger', 'Database operation failed');
            $this->showProductsListPage();
        }

        $pics = Uploader::getFiles(PIC_DIRECTORY, ['png', 'jpg', 'jpeg']);

        $this->addTwigVar('pics', $pics);
        $this->addTwigVar('categories', $categories);
        $this->addTwigVar('specs', $specs);
        $this->setTemplate('admin/products/add-product.twig');
        $this->render();
    }

    public function addProduct(array $post)
    {
        if (!$this->isClean($post)) {
            $this->flash('danger', 'Bad input data');
            Router::redirect("/admin/products/add");
        }

        $name = $post['name'];
        $desc = $post['description'];
        $price = $post['price'];

        $catId = $post['category'];
        $discount = $post['discount'];
        $mainPic = PIC_DIRECTORY . $post['mainPic'];
        $specs = $post['specs'][$catId];
        $pics = $post['pics'];

        // if pics is empty, no pics are uploaded, check if any existing are selected
        if (empty($pics)) $pics = $post['selectedPics'];

        foreach ($pics as $key => $pic) {
            if (empty($pic)) {
                unset($pics[$key]); // remove nulls from array
            } else {
                $pics[$key] = PIC_DIRECTORY . $pic; // append full path
            }
        }

        foreach ($specs as $key => $spec) {
            if (empty($spec)) {
                unset($specs[$key]); // remove nulls from array
            }
        }

        try {
            $dbCreator = new Creator();
            $dbReader = new Reader();   // to check name duplicates

            $dup = $dbReader->getProductByName($name);
            if (!empty($dup)) {
                $this->flash('danger', "There is already a product named '$name'");
                Router::redirect("/admin/products/add");
            }

            $new = $dbCreator->createProduct(
                $name,
                $desc,
                $catId,
                $price,
                $mainPic
            );

            if (!$new) {
                $this->flash('danger', "Failed to create new product '$name'");
                Router::redirect('/admin/products/add');
            }
            $prodId = $new->getId();

            if (isset($discount) && $discount > 0.0) { // should be set unless something weird
                if (!$dbCreator->createProductDiscount($prodId, $discount)) {
                    $this->flash('warning', "Failed to add product discount '$discount'");
                }
            }

            foreach ($pics as $pic) {
                if (!$dbCreator->createProductPic($prodId, $pic)) {
                    $this->flash('warning', "Failed to add product pic '$pic'");
                }
            }

            if (!empty($specs)) {
                foreach ($specs as $specId => $specValue) {
                    if (!$dbCreator->addSpecToProduct($prodId, $specId, $specValue)) {
                        $this->flash(
                            'warning',
                            "Failed to add product spec '$specId', '$specValue'"
                        );
                    }
                }
            }

            $this->flash('success', "'$name' was added successfully");
            Router::redirect('/admin/products');

        } catch (\Exception $e) {
            Logger::log('db', 'error', "Failed to add product '$name'", $e);
            $this->flash('danger', 'Database operation failed');
            Router::redirect("/admin/products/add");
        }
    }

    public function showViewProductPage(int $prodId)
    {
        try {
            $dbReader = new Reader();
            $product = $dbReader->getFullProductById($prodId);
            $categories = $dbReader->getAllCategories();
            $specs = $dbReader->getAllSpecs();
        } catch (\Exception $e) {
            Logger::log('db', 'error', "Failed to get product, categories or specs", $e);
            $this->flash('danger', 'Database operation failed');
            $this->showProductsListPage();
        }

        $pics = Uploader::getFiles(PIC_DIRECTORY, ['png', 'jpg', 'jpeg']);

        $this->addTwigVar('pics', $pics);
        $this->addTwigVar('product', $product);
        $this->addTwigVar('categories', $categories);
        $this->addTwigVar('specs', $specs);
        $this->setTemplate('admin/products/view-product.twig');
        $this->render();
    }

    public function updateProduct(int $prodId, array $post)
    {
        if (!$this->isClean($post)) {
            $this->flash('danger', 'Bad input data');
            Router::redirect("/admin/products/view/$prodId");
        }

        $name = $post['name'];
        $desc = $post['description'];
        $price = $post['price'];

        $catId = $post['category'];
        $discount = $post['discount'];
        $mainPic = PIC_DIRECTORY . $post['mainPic'];
        $specs = $post['specs'][$catId];
        $pics = $post['pics'];

        // if pics is empty, no pics are uploaded, check if any existing are selected
        if (empty($pics)) $pics = $post['selectedPics'];

        foreach ($pics as $key => $pic) {
            if (empty($pic)) {
                unset($pics[$key]); // remove nulls from array
            } else {
                $pics[$key] = PIC_DIRECTORY . $pic; // append full path
            }
        }

        try {
            $dbReader = new Reader();
            $dbUpdater = new Updater();
            $dbCreator = new Creator(); // to insert new pics/specs/discount
            $dbDeleter = new Deleter(); // to replace all old pics

            // need to get old data to resolve conflicting uniques and nulls
            $old = $dbReader->getFullProductById($prodId);

            if (empty($old)) {
                $this->flash('danger', 'Some problem occurred, please try again');
                Router::redirect("/admin/products/view/$prodId");
            }

            if ($old->getName() != $name) {
                // check for duplicate name
                $duplicate = $dbReader->getProductByName($name);
                if (!empty($duplicate)) {
                    $this->flash('danger', "There is already a product named '$name'");
                    Router::redirect("/admin/products/view/$prodId");
                }
            }

            // replace values, overwriting old ones, regardless same or not
            $dbUpdater->updateProduct(
                $prodId,
                $name,
                $desc,
                $catId,
                $price,
                $mainPic
            );

            if (isset($discount)) {     // should be set unless something weird
                $oldDiscount = $old->getDiscount()->getAmount();
                if ($oldDiscount > 0.0) {
                    if ($discount == 0.0) {
                        // remove completely
                        $dbDeleter->deleteProductDiscount($prodId);
                    } elseif ($discount != $oldDiscount) {
                        // update to the new value
                        $dbUpdater->updateProductDiscount($prodId, $discount);
                    }
                } elseif ($discount > 0.0) {
                    // otherwise no need to add
                    $dbCreator->createProductDiscount($prodId, $discount);
                }
            }

            if (!empty($old->getPics())) {
                // simply delete and add again all pics
                if ($dbDeleter->deleteProductPics($prodId)) {
                    foreach ($pics as $pic) {
                        $dbCreator->createProductPic($prodId, $pic);
                    }
                }
            } else {
                foreach ($pics as $pic) {
                    $dbCreator->createProductPic($prodId, $pic);
                }
            }

            $oldSpecs = $old->getSpecs();
            if (!empty($specs)) {
                foreach ($specs as $specId => $specValue) {
                    if (!empty($oldSpecs[$specId])) {
                        // update only if old value with this id already exists
                        $dbUpdater->updateProductSpec($prodId, $specId, $specValue);
                    } else {
                        $dbCreator->addSpecToProduct($prodId, $specId, $specValue);
                    }
                }
            }

            $this->flash('success', "'$name' updated successfully");
            Router::redirect('/admin/products');

        } catch (\Exception $e) {
            Logger::log('db', 'error', "Failed to update product '$prodId'", $e);
            $this->flash('danger', 'Database operation failed');
            Router::redirect("/admin/products/view/$prodId");
        }
    }

    public function deleteProduct(int $prodId)
    {
        try {
            $dbDeleter = new Deleter();
            $deleted = $dbDeleter->deleteProduct($prodId);
            if (!$deleted) {
                $this->flash('danger', "Could not delete product '$prodId'");
                return Router::redirect('/admin/products');
            }

            $name = $deleted->getName();

            $this->flash('success', "Product '$name' was deleted successfully");
            Router::redirect('/admin/products');

        } catch (\Exception $e) {
            Logger::log('db', 'error', "Failed to delete product '$prodId'", $e);
            $this->flash('danger', 'Database operation failed');
            Router::redirect('/admin/products');
        }
    }

    public function deleteProducts(array $products)
    {
        $good = [];
        $bad = [];

        foreach ($products as $prodId) {
            try {
                if (!isset($dbDeleter)) {
                    // this is to instantiate deleter only once
                    $dbDeleter = new Deleter();
                }

                $deleted = $dbDeleter->deleteProduct($prodId);

                if (!$deleted) {
                    $bad['db'][] = $prodId;
                } else {
                    $good[] = $deleted;
                }

            } catch (\Exception $e) {
                Logger::log(
                    'db',
                    'error',
                    "Failed to delete product '$prodId' (batch)",
                    $e
                );
                $bad['db'][] = $prodId;
            }
        }

        $this->prepareGoodBatchResults($good, $products, ['id', 'name']);
        $this->prepareBadBatchResults($bad, $products, ['id']);

        Router::redirect('/admin/products');
    }
}
