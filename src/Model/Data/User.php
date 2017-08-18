<?php
namespace App\Model\Data;

class User
{
    private $id;
    private $username;
    private $email;
    private $password;
    private $type;
    private $registeredAt;

    private $shipping;
    private $orders = [];

    public function __construct(
        $id           = -1,
        $username     = '',
        $email        = '',
        $password     = '',
        $type         = 'guest',
        $registeredAt = '2017-01-01 00:00:00'
    ) {
        $this->setId($id);
        $this->setUsername($username);
        $this->setEmail($email);
        $this->setPassword($password);
        $this->setType($type);
        $this->setRegisteredAt($registeredAt);
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getUsername() : string
    {
    	return $this->username;
    }

    public function setUsername(string $username)
    {
    	$this->username = $username;
    }

    public function getEmail() : string
    {
    	return $this->email;
    }

    public function setEmail(string $email)
    {
    	$this->email = $email;
    }

    public function getPassword() : string
    {
        return $this->password;
    }

    public function setPassword(string $password)
    {
        $this->password = $password;
    }

    public function getType() : string
    {
    	return $this->type;
    }

    public function setType(string $type)
    {
    	$this->type = $type;
    }

    public function getRegisteredAt() : string
    {
        return $this->registeredAt;
    }

    public function setRegisteredAt(string $registeredAt)
    {
        $this->registeredAt = $registeredAt;
    }

    public function getShipping()
    {
        return $this->shipping;
    }

    public function setShipping(Shipping $shipping)
    {
        $this->shipping = $shipping;
    }

    public function getOrders() : array
    {
        return $this->orders;
    }

    public function getOrder(int $orderId)
    {
        if (in_array($orderId, array_keys($this->orders))) {
            return $this->orders[$orderId];
        } else {
            return false;
        }
    }

    public function setOrders(array $orders)
    {
        foreach ($orders as $order) {
            $this->addOrder($order);
        }
    }

    public function addOrder(Order $order)
    {
        $this->orders[$order->getId()] = $order;
    }
}
