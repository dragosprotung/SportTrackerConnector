<?php

namespace FitnessTrackingPorting\Tests\Workout\Workout\Workout\Workout;

use FitnessTrackingPorting\Workout\Workout\TrackPoint;
use DateTime;
use DateInterval;

class TrackTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test that calling the getStartDateTime() will trigger the recomputing if is not yet set.
     */
    public function testGetStartDateTimeCallsRecomputeIfDateTimeNotSet()
    {
        $track = $this->getMock('FitnessTrackingPorting\Workout\Workout\Track', array('recomputeStartDateTime'));
        $track->expects($this->once())->method('recomputeStartDateTime');

        $track->getStartDateTime();
    }

    /**
     * Test that calling the recomputeEndDateTime() will trigger the recomputing if is not yet set.
     */
    public function testGetEndDateTimeCallsRecomputeIfDateTimeNotSet()
    {
        $track = $this->getMock('FitnessTrackingPorting\Workout\Workout\Track', array('recomputeEndDateTime'));
        $track->expects($this->once())->method('recomputeEndDateTime');

        $track->getEndDateTime();
    }

    /**
     * Test recompute start date time.
     */
    public function testRecomputeStartDateTime()
    {
        $track = $this->getMock('FitnessTrackingPorting\Workout\Workout\Track', array('getTrackPoints'));
        $trackPoints = array(
            $this->getTrackPointMock('now'),
            $this->getTrackPointMock('-1 hour'),
            $this->getTrackPointMock('2014-01-01 00:00:00'),
            $this->getTrackPointMock('+1 hour')
        );
        $track->expects($this->once())->method('getTrackPoints')->will($this->returnValue($trackPoints));

        $actual = $track->getStartDateTime();

        $expected = new DateTime('2014-01-01 00:00:00');
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test recompute start date time.
     */
    public function testRecomputeEndDateTime()
    {
        $track = $this->getMock('FitnessTrackingPorting\Workout\Workout\Track', array('getTrackPoints'));
        $trackPoints = array(
            $this->getTrackPointMock('now'),
            $this->getTrackPointMock('-1 hour'),
            $this->getTrackPointMock('2014-01-01 00:00:00'),
            $this->getTrackPointMock('2034-01-01 00:00:00')
        );
        $track->expects($this->once())->method('getTrackPoints')->will($this->returnValue($trackPoints));

        $actual = $track->getEndDateTime();

        $expected = new DateTime('2034-01-01 00:00:00');
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test the get duration.
     */
    public function testGetDuration()
    {
        $track = $this->getMock('FitnessTrackingPorting\Workout\Workout\Track', array('getStartDateTime', 'getEndDateTime'));
        $startDateTime = new DateTime('now');
        $endDateTime = new DateTime('+1 hour +5 minutes +20 seconds');
        $track->expects($this->once())->method('getStartDateTime')->will($this->returnValue($startDateTime));
        $track->expects($this->once())->method('getEndDateTime')->will($this->returnValue($endDateTime));

        $expected = new DateInterval('PT1H5M20S');
        $actual = $track->getDuration();

        $this->assertEquals($expected, $actual);
        $this->assertSame('1h:5m:20s', $actual->format('%hh:%im:%ss'));
    }

    /**
     * Get a track point mock.
     *
     * @param string $dateTime The date and time of the point.
     * @return TrackPoint
     */
    private function getTrackPointMock($dateTime)
    {
        return new TrackPoint(null, null, new DateTime($dateTime));
    }
} 