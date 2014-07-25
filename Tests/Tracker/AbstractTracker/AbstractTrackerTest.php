<?php

namespace SportTrackerConnector\Tests\Tracker\AbstractTracker;

use DateTimeZone;

/**
 * Test for AbstractTracker.
 */
class AbstractTrackerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Data provider for testGetTimeZoneOffsetProvider().
     *
     * @return array
     */
    public function dataProviderTestGetTimeZoneOffset()
    {
        return array(
            array(new DateTimeZone('UTC'), 0),
            array(new DateTimeZone('Europe/Berlin'), -7200),
            array(new DateTimeZone('Europe/Bucharest'), -10800),
            array(new DateTimeZone('Pacific/Auckland'), -43200),
            array(new DateTimeZone('America/Martinique'), 14400)
        );
    }

    /**
     * Test get time zone offset.
     *
     * @param DateTimeZone $originTimeZone The origin timezone.
     * @param integer $expected The number of seconds expected to be the time zone difference.
     * @dataProvider dataProviderTestGetTimeZoneOffset
     */
    public function testGetTimeZoneOffset($originTimeZone, $expected)
    {
        $mock = $this->getMockBuilder('SportTrackerConnector\Tracker\AbstractTracker')
            ->setMethods(array('getTimeZone'))
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $mock->expects($this->any())
            ->method('getTimeZone')
            ->will($this->returnValue($originTimeZone));

        $this->assertEquals($expected, $mock->getTimeZoneOffset());
    }
}
 