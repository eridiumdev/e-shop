<?php
namespace App\Model\Data;

class Discount
{
    private $amount;

    public function __construct($amount = 0.0) {
        $this->setAmount($amount);
    }

    public function getAmount() : float
    {
        return $this->amount;
    }

    public function setAmount(float $amount)
    {
        $this->amount = $amount;
    }
}
