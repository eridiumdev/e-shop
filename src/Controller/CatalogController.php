<?php
namespace App\Controller;

use App\Model\Database\Reader;

class CatalogController extends BaseController
{
    public function showCatalogMainPage()
    {
        $this->setTemplate('home.twig');    // catalog.twig
        $this->render();
    }

    // Uri may be a category uri or a section uri
    public function showCatalogPage(string $uri, array $post)
    {
        $filters = [];
        if (!empty($post)) {
            if (!$this->isClean($post)) {
                $this->flash('warning', 'Some filters have invalid values');
                // Router::redirect("/catalog");
            }

            $filters = $post;
            $this->addTwigVar("pageIsFiltered", true);

            if (isset($post['groupBy'])) {
                $this->addTwigVar('groupBy', $post['groupBy']);
            }
            if (isset($post['sortBy'])) {
                $this->addTwigVar('sortBy', $post['sortBy']);
            }
            if (isset($post['priceMin'])) {
                $this->addTwigVar('priceMin', $post['priceMin']);
            }
            if (isset($post['priceMax'])) {
                $this->addTwigVar('priceMax', $post['priceMax']);
            }
            if (isset($post['specVals'])) {
                $this->addTwigVar("specVals", $post['specVals']);
            }
        }

        try {
            $dbReader = new Reader();
            $category = $dbReader->getCategoryByUri($uri);

            if (empty($category)) {
                $section = $dbReader->getSectionByUri($uri);

                if (empty($section)) {
                    $this->flash('danger', "URI '$uri' does not exist");
                    Router::redirect('/catalog');
                } else {
                    $products = $section->getProducts($filters);
                    $specs = $dbReader->getAllSpecs();

                    $this->addTwigVar('section', $section);
                    $this->setTemplate('catalog/section.twig');
                }
            } else {
                $products = $category->getProducts($filters);
                $specs = $category->getSpecs();

                $this->addTwigVar('category', $category);
                $this->setTemplate('catalog/category.twig');
            }

            if (!empty($post['groupBy'])) {     // == specId to group by
                $specId = $post['groupBy'];
                $groups = $dbReader->getSpecValues($specId);

                $groupedProducts = [];
                foreach ($groups as $group) {
                    $groupedProducts[$group] = [];
                }
                $groupedProducts['Other'] = [];

                foreach ($products as $product) {
                    if ($product->hasSpec($specId)) {
                        $group = $product->getSpec($specId)->getValue();
                        if (in_array($group, array_keys($groupedProducts))) {
                            $groupedProducts[$group][] = $product;
                        }
                    } else {
                        if (!in_array($product, $groupedProducts['Other']))
                        {
                            $groupedProducts['Other'][] = $product;
                        }
                    }
                }

                // Make 'Other' products be in the end
                uksort($groupedProducts, function($a, $b) {
                    if ($a == 'Other') {
                        return 1;
                    } elseif ($b == 'Other') {
                        return -1;
                    } else {
                        return strcmp($a, $b);
                    }
                });

                $products = $groupedProducts;
            }

        } catch (\Exception $e) {
            Logger::log('db', 'error', 'Failed to get products for the catalog', $e);
            $this->flash('danger', 'Database operation failed');
            $this->showCatalogMainPage();
        }

        $this->addTwigVar('products', $products);
        $this->addTwigVar('specs', $specs);
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
