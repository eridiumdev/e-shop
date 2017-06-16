<?php
namespace App\Model\Data;

class Picture
{
    private $name;
    private $path;

    public function __construct($name = '', $path = '') {
        $this->setName($name);
        $this->setPath($path);
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getPath() : string
    {
    	return $this->path;
    }

    public function setPath(string $path)
    {
    	$this->path = $path;
    }
}
