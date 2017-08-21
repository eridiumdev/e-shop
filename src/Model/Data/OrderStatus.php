<?php
namespace App\Model\Data;

class OrderStatus
{
    private $id;
    private $name;

    public function __construct($id = -1, $name = 'pending') {
        $this->setId($id);
        $this->setName($name);
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
}
