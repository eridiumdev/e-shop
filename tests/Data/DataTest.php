<?php
namespace App\Test\Data;

require_once __DIR__ . '/../../web/config.php';

use PHPUnit\Framework\TestCase;

class DataTest extends TestCase
{
    public function runGetter($obj, $input, $expected)
    {
        foreach ($input as $propName => $propValue) {
            $getter = 'get' . ucfirst($propName);

            if (is_array($expected[$propName])) {
                // if item
                $out .= $item[$fields[$i]];
            } elseif (is_object($item)) {
                // if item is object, output uses class getters
                $getter = 'get' . ucfirst($fields[$i]);
                $out .= $item->$getter();
            }
            $this->assertEquals(
                $expected[$propName],
                $obj->$getter(),
                "Problem found in $getter"
            );
        }
    }

    public function runSetter($obj, $input, $expected)
    {
        foreach ($input as $propName => $propValue) {
            $setter = 'set' . ucfirst($propName);
            $getter = 'get' . ucfirst($propName);
            $obj->$setter($propValue);

            $this->assertEquals(
                $expected[$propName],
                $obj->$getter(),
                "Problem found in $setter."
            );
        }
    }

    protected function getterTest($input, $expected){}
    protected function setterTest($input, $expected){}
}
