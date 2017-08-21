<?php
namespace App\Model\Data;

class OrderItem extends Product
{
    private $qty;

    public function __construct(Product $product, $qty = 1)
    {
        $this->setId($product->getId());
        $this->setName($product->getName());
        $this->setDescription($product->getDescription());
        $this->setPrice($product->getPrice());

        $this->setCategory($product->getCategory());
        $this->setDiscount($product->getDiscount());
        $this->setMainPic($product->getMainPic());
        $this->setPics($product->getPics());
        $this->setSpecs($product->getSpecs());

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
