<?php
namespace App\Model\Data;

class User
{
    private $id;
    private $email;
    private $password;
    private $type;
    private $registeredAt;

    private $addresses = [];
    private $orders = [];

    public function __construct(
        int     $id = null,
        string  $email,
        string  $password,
        string  $type,
        string  $registeredAt
    ) {
        $this->setId($id);
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
}
