<?php
namespace App\Model\Data;

class Category
{
    private $id;
    private $name;
    private $description;
    private $uri;

    private $specs = [];
    private $products = [];

    public function __construct(
        $id             = -1,
        $name           = '',
        $description    = '',
        $uri            = 'new'
    ) {
        $this->setId($id);
        $this->setName($name);
        $this->setDescription($description);
        $this->setUri($uri);
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

    public function getDescription() : string
    {
    	return $this->description;
    }

    public function setDescription(string $description)
    {
    	$this->description = $description;
    }

    public function getUri() : string
    {
    	return $this->uri;
    }

    public function setUri(string $uri)
    {
    	$this->uri = $uri;
    }

    public function getSpecs() : array
    {
        return $this->specs;
    }

    public function setSpecs(array $specs)
    {
        $this->specs = $specs;
    }

    public function addSpec(Spec $spec)
    {
        $this->specs[$spec->getId()] = $spec;
    }

    public function getProducts() : array
    {
        return $this->products;
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
