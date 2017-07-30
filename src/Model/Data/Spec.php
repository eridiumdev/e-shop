<?php
namespace App\Model\Data;

class Spec
{
    private $id;
    private $name;
    private $type;
    private $value;
    private $isRequired;

    private $categories = [];

    public function __construct(
        $id         = -1,
        $name       = '',
        $type       = 'checkbox',
        $isRequired = false,
        $value      = ''
    ) {
        $this->setId($id);
        $this->setName($name);
        $this->setType($type);
        $this->setIsRequired($isRequired);
        $this->setValue($value);
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

    public function getType() : string
    {
    	return $this->type;
    }

    public function setType(string $type)
    {
    	$this->type = $type;
    }

    public function getValue() : string
    {
    	return $this->value;
    }

    public function setValue(string $value)
    {
    	$this->value = $value;
    }

    public function getIsRequired() : bool
    {
    	return $this->isRequired;
    }

    public function setIsRequired(bool $isRequired)
    {
    	$this->isRequired = $isRequired;
    }

    public function getCategories() : array
    {
        return $this->categories;
    }

    public function setCategories(array $categories)
    {
        $this->categories = $categories;
    }
}
