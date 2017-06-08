<?php
namespace App\Controller;

class CatalogController extends BaseController
{
    public function getCategoryId(string $category)
    {
        switch ($category) {
            case 'keyboards' :
                $id = 1;
                break;
            case 'filtered' :
                $id = 2;
                break;
            default :
                $id = 0;
        }

        return $id;
    }

    public function showCatalogPage(string $category)
    {
        $this->setTemplate('catalog2.twig');
        $this->render();
    }

    public function showFilteredPage(string $category)
    {
        $this->setTemplate('filtered.twig');
        $this->render();
    }

    public function showProductPage(int $productId)
    {
        $this->setTemplate('catalog/product-page.twig');
        $this->render();
    }
}
