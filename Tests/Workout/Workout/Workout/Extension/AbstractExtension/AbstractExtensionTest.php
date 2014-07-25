<?php

namespace SportTrackerConnector\Tests\Workout\Workout\Workout\Extension\AbstractExtension;

/**
 * Test for \SportTrackerConnector\Workout\Workout\Extension\AbstractExtension.
 */
class AbstractExtensionTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Data provider for testSetGetValue();
     * 
     * @return array
     */
    public function dataProviderTestSetGetValue()
    {
        return array(
            array(null),
            array(''),
            array('some string'),
            array(123),
            array(123.456),
            array(array('123')),
            array(new \stdClass())
        );
    }

    /**
     * Test set/get the value of an extension.
     *
     * @dataProvider dataProviderTestSetGetValue
     * @param mixed $value The value.
     */
    public function testSetGetValue($value)
    {
        $mock = $this->getMockForAbstractClass('SportTrackerConnector\Workout\Workout\Extension\AbstractExtension');
        $mock->setValue($value);

        $this->assertSame($value, $mock->getValue());
    }
}
 