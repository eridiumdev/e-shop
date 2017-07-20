<?php
namespace App\Model\Data;

class Picture
{
    private $name;
    private $path;

    public function __construct($path) {
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
        $pathParts = explode('/', $path);
        $this->setName(array_pop($pathParts));
    }
}
