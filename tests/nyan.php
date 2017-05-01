<?php
require 'PigLatin.php';

use PHPUnit\Framework\TestCase;

class BuildTest extends TestCase
{
    /**
     * Provides data for simpleAcceptanceTest
     * @return array - key is the english Word, value is PigLatin word
     */
    public function dataProvider()
    {
        $arr = [];
        for ($i = 0; $i < 200; $i++) {
            $arr[] = ['nyan', 'yannay'];
        }

        return $arr;
    }

    /**
     * Compares the data inside data provider and runs the test
     * @param  string $word
     * @param  string $expectedResult
     * @test
     * @dataProvider dataProvider
     */
    public function simpleUnitTest($word, $expectedResult)
    {
        $pigLatin = new PigLatin();

        $this->assertEquals(
            $expectedResult,
            $pigLatin->convert($word),
            "PigLatin conversion did not work correctly"
        );
    }
}
