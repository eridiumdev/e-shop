<?php
namespace App\Model\Data;

class Cart
{
    private $user;
    private $items = [];

    public function __construct($user = null) {
        if ($user) {
            $this->setUser($user);
        }
    }

    public function getUser() : User
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function getItems() : array
    {
        return $this->items;
    }

    public function setItems(array $items)
    {
        $this->items = $items;
    }

    public function addItem(CartItem $item)
    {
        $this->items[$item->getId()] = $item;
    }

    public function hasItem(int $prodId)
    {
        return key_exists($prodId, $this->items);
    }

    public function getTotal() : float
    {
        $total = 0.0;
        foreach ($this->items as $item) {
            $total += $item->getSubtotal();
        }
        return $total;
    }

    public function getSize() : int
    {
        $size = 0;
        foreach ($this->items as $item) {
            $size += $item->getQty();
        }
        return $size;
    }
}
