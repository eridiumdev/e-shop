<?php
namespace App\Model\Data;

class Section
{
    private $id;
    private $name;
    private $description;
    private $maxProducts;
    private $param;

    public function __construct(
        $id             = -1,
        $name           = '',
        $description    = '',
        $maxProducts    = 10,
        $param          = 'sale'
    ) {
        $this->setId($id);
        $this->setName($name);
        $this->setDescription($description);
        $this->setMaxProducts($maxProducts);
        $this->setParam($param);
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

    public function getMaxProducts() : int
    {
        return $this->maxProducts;
    }

    public function setMaxProducts(int $maxProducts)
    {
        $this->maxProducts = $maxProducts;
    }

    public function getParam() : string
    {
        return $this->param;
    }

    public function setParam(string $param)
    {
        $this->param = $param;
    }
}
