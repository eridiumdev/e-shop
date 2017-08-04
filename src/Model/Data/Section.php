<?php
namespace App\Model\Data;

class Section
{
    private $id;
    private $name;
    private $uri;
    private $description;
    private $maxProducts;

    private $param;
    private $products = [];

    public function __construct(
        $id             = -1,
        $name           = '',
        $uri            = '',
        $description    = '',
        $maxProducts    = 10
    ) {
        $this->setId($id);
        $this->setName($name);
        $this->setUri($uri);
        $this->setDescription($description);
        $this->setMaxProducts($maxProducts);

        $this->setParam(new Param(-1, 'not_defined'));
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getUri() : string
    {
        return $this->uri;
    }

    public function setUri(string $uri)
    {
        $this->uri = $uri;
    }

    public function getDescription() : string
    {
    	return $this->description;
    }

    public function setDescription(string $description)
    {
    	$this->description = $description;
    }

    public function getMaxProducts() : int
    {
        return $this->maxProducts;
    }

    public function setMaxProducts(int $maxProducts)
    {
        $this->maxProducts = $maxProducts;
    }

    public function getParam() : Param
    {
        return $this->param;
    }

    public function setParam(Param $param)
    {
        $this->param = $param;
    }

    public function getProducts($filters = []) : array
    {
        // pe($filters);
        if (empty($filters)) {
            return $this->products;
        }

        $filtered = [];

        $priceMin = $filters['priceMin'];
        $priceMax = $filters['priceMax'];
        $sortBy = $filters['sortBy'] ?? 'id'; // priceAsc or priceDesc
        // $groupBy = $filters['groupBy'] ?? '';   // spec id
        $specVals = $filters['specVals'] ?? []; // specId => value

        foreach ($this->products as $prodId => $product) {
            if ($product->getDiscountedPrice() >= $priceMin &&
                $product->getDiscountedPrice() <= $priceMax)
            {
                if (!empty($specVals)) {
                    foreach ($specVals as $specId => $values) {
                        if ($product->hasSpec($specId) &&
                            in_array($product->getSpec($specId)->getValue(), $values)
                        ) {
                            $filtered[$prodId] = $product;
                            break;
                        }
                    }
                } else {
                    $filtered[$prodId] = $product;
                }
            }
        }

        switch ($sortBy) {
            case 'priceAsc' :
                $sortFunc = 'sortByPriceAsc';
                break;

            case 'priceDesc' :
                $sortFunc = 'sortByPriceDesc';
                break;

            default:
                $sortFunc = null;
        }

        if (isset($sortFunc)) {
            usort($filtered, [$this, $sortFunc]);
        }

        return $filtered;
    }

    private function sortByPriceAsc($a, $b)
    {
        if ($a->getDiscountedPrice() == $b->getDiscountedPrice()) {
            // same priority, no difference if a or b comes first in the array
            return 0;
        } elseif ($a->getDiscountedPrice() < $b->getDiscountedPrice()) {
            // a is less expensive, should come first (before b)
            return -1;
        } else {
            // a is more expensive, should come second (after b)
            return 1;
        }
    }

    private function sortByPriceDesc(Product $a, Product $b)
    {
        if ($a->getDiscountedPrice() == $b->getDiscountedPrice()) {
            // same priority, no difference if a or b comes first in the array
            return 0;
        } elseif ($a->getDiscountedPrice() < $b->getDiscountedPrice()) {
            // a is less expensive, should come second (after b)
            return 1;
        } else {
            // a is more expensive, should come first (before b)
            return -1;
        }
    }

    public function setProducts(array $products)
    {
        $this->products = $products;
    }

    public function addProduct(Product $product)
    {
        $this->products[$product->getId()] = $product;
    }
}
