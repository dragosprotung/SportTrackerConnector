<?php

namespace FitnessTrackingPorting\Tests\Workout\Workout\Workout\Extension\HR;

use FitnessTrackingPorting\Workout\Workout\Extension\HR;

/**
 * Test for \FitnessTrackingPorting\Workout\Workout\Extension\HR.
 */
class HRTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test get the ID.
     */
    public function testGetID()
    {
        $hr = new HR();
        $this->assertSame(HR::ID, $hr->getID());
    }

    /**
     * Data provider for testSetValueValid();
     *
     * @return array
     */
    public function dataProviderTestSetValueValid()
    {
        return array(
            array(null),
            array(123),
            array(230)
        );
    }

    /**
     * Test set/get the value of an extension.
     *
     * @dataProvider dataProviderTestSetValueValid
     * @param mixed $value The value.
     */
    public function testSetValueValid($value)
    {
        $hr = new HR();
        $hr->setValue($value);

        $this->assertSame($value, $hr->getValue());
    }

    /**
     * Data provider for testSetValueInvalid();
     *
     * @return array
     */
    public function dataProviderTestSetValueInvalid()
    {
        return array(
            array(''),
            array(123.123),
            array('some string'),
            array(array('123')),
            array(new \stdClass()),
            array(-1),
            array(231),
        );
    }

    /**
     * Test set/get the value of an extension.
     *
     * @dataProvider dataProviderTestSetValueInvalid
     * @param mixed $value The value.
     */
    public function testSetValueInvalid($value)
    {
        $hr = new HR();

        $this->setExpectedException('InvalidArgumentException', 'The value for the HR must be an integer and between 0 and 230.');

        $hr->setValue($value);
    }
}
 