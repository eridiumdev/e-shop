<?php
namespace App\Model\Data;

class Product
{
    protected $id;
    protected $name;
    protected $description;
    protected $price;

    protected $category;
    protected $discount;
    protected $mainPic;
    protected $pics = [];
    protected $specs = [];

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

    public function getCategory() : Category
    {
        return $this->category;
    }

    public function setCategory(Category $category)
    {
        $this->category = $category;
    }

    public function getDiscount() : Discount
    {
        return $this->discount;
    }

    public function setDiscount(Discount $discount)
    {
        $this->discount = $discount;
    }

    public function getMainPic() : Picture
    {
        return $this->mainPic;
    }

    public function setMainPic(Picture $mainPic)
    {
        $this->mainPic = $mainPic;
    }

    public function getPics() : array
    {
        return $this->pics;
    }

    public function setPics(array $pics)
    {
        $this->pics = $pics;
    }

    public function getSpecs() : array
    {
        return $this->specs;
    }

    public function setSpecs(array $specs)
    {
        $this->specs = $specs;
    }

    public function onSale() : bool
    {
        if (!empty($this->discount)) {
            return true;
        } else {
            return false;
        }
    }

    public function getDiscountedPrice() : float
    {
        if (!empty($this->onSale())) {
            return (1 -$this->discount->getAmount()) * $this->price;
        } else {
            return $this->price;
        }
    }
}
