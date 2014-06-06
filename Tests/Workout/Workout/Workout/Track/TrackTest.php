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
     * Test that calling the getEndDateTime() will trigger the recomputing if is not yet set.
     */
    public function testGetEndDateTimeCallsRecomputeIfDateTimeNotSet()
    {
        $track = $this->getMock('FitnessTrackingPorting\Workout\Workout\Track', array('recomputeEndDateTime'));
        $track->expects($this->once())->method('recomputeEndDateTime');

        $track->getEndDateTime();
    }

    /**
     * Test that calling the getLength() will trigger the recomputing if is not yet set.
     */
    public function testGetLengthCallsRecomputeIfLengthIsNotSet()
    {
        $track = $this->getMock('FitnessTrackingPorting\Workout\Workout\Track', array('recomputeLength'));
        $track->expects($this->once())->method('recomputeLength');

        $track->getLength();
    }

    /**
     * Test recompute start date time.
     */
    public function testRecomputeStartDateTime()
    {
        $track = $this->getMock('FitnessTrackingPorting\Workout\Workout\Track', array('getTrackPoints'));
        $trackPoints = array(
            $this->getTrackPointMock(null, null, 'now'),
            $this->getTrackPointMock(null, null, '-1 hour'),
            $this->getTrackPointMock(null, null, '2014-01-01 00:00:00'),
            $this->getTrackPointMock(null, null, '+1 hour')
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
            $this->getTrackPointMock(null, null, 'now'),
            $this->getTrackPointMock(null, null, '-1 hour'),
            $this->getTrackPointMock(null, null, '2014-01-01 00:00:00'),
            $this->getTrackPointMock(null, null, '2034-01-01 00:00:00')
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
     * Test recompute the length.
     */
    public function testRecomputeLength()
    {
        $track = $this->getMock('FitnessTrackingPorting\Workout\Workout\Track', array('getTrackPoints'));
        $trackPoints = array(
            $this->getTrackPointMock('-38.691450', '176.079795'),
            $this->getTrackPointMock('-38.719038', '176.081491'),
            $this->getTrackPointMock('-38.810918', '176.087366'),
            $this->getTrackPointMock('-38.997640', '176.082147')
        );
        $track->expects($this->once())->method('getTrackPoints')->will($this->returnValue($trackPoints));

        $expected = 0.306503;
        $actual = $track->recomputeLength();

        $this->assertEquals($expected, $actual);
    }

    /**
     * Get a track point mock.
     *
     * @param string $dateTime The date and time of the point.
     * @param string $lat The latitude.
     * @param string $lon The longitude.
     * @return TrackPoint
     */
    private function getTrackPointMock($lat = null, $lon = null, $dateTime = null)
    {
        return new TrackPoint($lat, $lon, new DateTime($dateTime));
    }
} 