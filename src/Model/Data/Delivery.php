<?php
namespace App\Model\Data;

class Delivery
{
    private $id;
    private $name;
    private $description;
    private $price;

    public function __construct(
        $id             = -1,
        $name           = '',
        $description    = '',
        $price          = 0.0
    ) {
        $this->setId($id);
        $this->setName($name);
        $this->setDescription($description);
        $this->setPrice($price);
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

    public function getPrice() : float
    {
        return $this->price;
    }

    public function setPrice(float $price)
    {
        $this->price = $price;
    }
}
