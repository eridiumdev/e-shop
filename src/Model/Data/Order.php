<?php
namespace App\Model\Data;

class Order
{
    private $id;
    private $date;
    private $status;

    private $user;
    private $delivery;
    private $payment;
    private $items = [];

    public function __construct(
        $id     = -1,
        $date   = '2017-01-01 00:00:00',
        $status = 'not_paid'
    ) {
        $this->setId($id);
        $this->setDate($date);
        $this->setStatus($status);
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getDate() : string
    {
    	return $this->date;
    }

    public function setDate(string $date)
    {
    	$this->date = $date;
    }

    public function getStatus() : string
    {
    	return $this->status;
    }

    public function setStatus(string $status)
    {
    	$this->status = $status;
    }

    public function getUser() : User
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function getDelivery() : Delivery
    {
    	return $this->delivery;
    }

    public function setDelivery(Delivery $delivery)
    {
    	$this->delivery = $delivery;
    }

    public function getPayment() : Payment
    {
        return $this->payment;
    }

    public function setPayment(Payment $payment)
    {
        $this->payment = $payment;
    }

    public function getItems() : array
    {
        return $this->items;
    }

    public function setItems(array $items)
    {
        $this->items = $items;
    }

    public function getTotal() : float
    {
        $total = 0.0;
        foreach ($this->items as $item) {
            $total += $item->getSubtotal();
        }
        return $total;
    }
}
