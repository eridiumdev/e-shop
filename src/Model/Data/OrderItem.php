<?php
namespace App\Model\Data;

class OrderItem extends Product
{
    private $qty;

    public function __construct(
        $id             = -1,
        $name           = '',
        $description    = '',
        $price          = 0.0,
        $qty            = 1
    ) {
        $this->setId($id);
        $this->setName($name);
        $this->setDescription($description);
        $this->setPrice($price);
        $this->setQty($qty);
    }

    public function getQty() : int
    {
        return $this->qty;
    }

    public function setQty(int $qty)
    {
        $this->qty = $qty;
    }

    public function getSubtotal() : float
    {
        return $this->getDiscountedPrice() * $this->qty;
    }
}
