<?php
namespace App\Model\Data;

class Shipping
{
    private $name;
    private $phone;
    private $address;

    public function __construct(
        $name       = '',
        $phone      = '',
        $address    = ''
    ) {
        $this->setName($name);
        $this->setPhone($phone);
        $this->setAddress($address);
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getPhone() : string
    {
    	return $this->phone;
    }

    public function setPhone(string $phone)
    {
    	$this->phone = $phone;
    }

    public function getAddress() : string
    {
    	return $this->address;
    }

    public function setAddress(string $address)
    {
    	$this->address = $address;
    }
}
