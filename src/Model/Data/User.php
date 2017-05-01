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
    private $address;

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

    public function getAddress() : string
    {
        return $this->address;
    }

    public function setAddress(string $address)
    {
        $this->address = $address;
    }

    public function getOrders() : array
    {
        return $this->orders;
    }

    public function setOrders(array $orders)
    {
        $this->orders = $orders;
    }
}
