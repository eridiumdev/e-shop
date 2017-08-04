<?php
namespace App\Model\Data;

class Spec
{
    private $id;
    private $name;
    private $type;
    private $value;         // single value referring to the product
    private $isRequired;
    private $isFilter;

    private $categories = [];
    private $values = [];   // all possible values

    public function __construct(
        $id         = -1,
        $name       = '',
        $type       = 'checkbox',
        $isRequired = false,
        $isFilter = false,
        $value      = ''
    ) {
        $this->setId($id);
        $this->setName($name);
        $this->setType($type);
        $this->setIsRequired($isRequired);
        $this->setIsFilter($isFilter);
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

    public function getIsFilter() : bool
    {
    	return $this->isFilter;
    }

    public function setIsFilter(bool $isFilter)
    {
    	$this->isFilter = $isFilter;
    }

    public function getCategories() : array
    {
        return $this->categories;
    }

    // should be deprecated honestly
    public function setCategories(array $categories)
    {
        $this->categories = $categories;
    }

    public function addCategory(Category $category)
    {
        $this->categories[$category->getId()] = $category;
    }

    public function hasCategory(int $catId)
    {
        if (array_key_exists($catId, $this->categories)) {
            return true;
        } else {
            return false;
        }
    }

    public function getValues() : array
    {
        return $this->values;
    }

    public function setValues(array $values)
    {
        $this->values = $values;
    }

    public function addValue(string $value)
    {
        if (!in_array($value, $this->values)) {
            $this->values[] = $value;
        }
    }
}
