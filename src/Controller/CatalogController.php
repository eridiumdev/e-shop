<?php
namespace App\Controller;

use App\Model\Database\Reader;

class CatalogController extends BaseController
{
    public function showCatalogPage()
    {
        $this->setTemplate('catalog.twig');
        $this->render();
    }

    public function showCategoryPage(string $uri)
    {
        try {
            $dbReader = new Reader();
            $category = $dbReader->getCategoryByUri($uri);

            if (empty($category)) {
                $this->flash('danger', "Category '$uri' does not exist");
                Router::redirect('/catalog');
            }

            $products = $dbReader->getProductsByCatId($category->getId());

        } catch (\Exception $e) {
            Logger::log('db', 'error', 'Failed to get products by catId', $e);
            $this->flash('danger', 'Database operation failed');
            $this->showCatalogPage();
        }

        $this->addTwigVar('category', $category);
        $this->addTwigVar('products', $products);

        $this->setTemplate('catalog/category.twig');
        $this->render();
    }

    public function showFilteredPage(string $category)
    {
        $this->setTemplate('filtered.twig');
        $this->render();
    }

    public function showProductPage(int $prodId, string $catUri)
    {
        try {
            $dbReader = new Reader();
            $product = $dbReader->getFullProductById($prodId);

            if (empty($product)) {
                $this->flash('danger', "Product #$prodId not found");
                Router::redirect("/catalog/$catUri");
            }
        } catch (\Exception $e) {
            Logger::log('db', 'error', 'Failed to get full product by ID', $e);
            $this->flash('danger', 'Database operation failed');
            Router::redirect("/catalog/$catUri");
        }

        $this->addTwigVar('product', $product);
        $this->setTemplate('catalog/product-page.twig');
        $this->render();
    }
}
