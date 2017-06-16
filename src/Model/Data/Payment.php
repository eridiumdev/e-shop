<?php
namespace App\Model\Data;

class Payment
{
    private $id;
    private $name;
    private $description;

    public function __construct(
        $id             = -1,
        $name           = '',
        $description    = ''
    ) {
        $this->setId($id);
        $this->setName($name);
        $this->setDescription($description);
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

    public function getDescription() : string
    {
    	return $this->description;
    }

    public function setDescription(string $description)
    {
    	$this->description = $description;
    }
}
