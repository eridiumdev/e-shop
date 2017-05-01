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
            default :
                $id = 0;
        }

        return $id;
    }

    public function showCatalogPage(string $category)
    {
        $this->setTemplate('catalog.twig');
        $this->render();
    }

    public function showProductPage(int $productId)
    {
        $this->setTemplate('catalog/product-page.twig');
        $this->render();
    }
}
