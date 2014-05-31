<?php

namespace FitnessTrackingPorting\Tests\Tracker\Polar\Polar;

use DateTimeZone;
use DateTime;
use FitnessTrackingPorting\Workout\Workout;
use FitnessTrackingPorting\Workout\Workout\TrackPoint;
use FitnessTrackingPorting\Workout\Workout\Sport;
use FitnessTrackingPorting\Workout\Workout\Extension\HR;

class PolarTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test fetching a workout from an HTML page.
     */
    public function testFetchWorkoutFromHTML()
    {
        $polarMock = $this->getMock('FitnessTrackingPorting\Tracker\Polar\Polar', array('getTimeZone'), array(null, null));
        $polarMock->expects($this->any())->method('getTimeZone')->will($this->returnValue(new DateTimeZone('Europe/Berlin')));

        $expected = new Workout();
        $expected->setSport(Sport::RUNNING);
        $expected->setTrackPoints(
            array(
                $this->getTrackPoint('53.551075', '9.993672', '2014-05-30T17:12:58+00:00', null, 78),
                $this->getTrackPoint('53.550085', '9.992682', '2014-05-30T17:12:59+00:00', null, 88)
            )
        );

        $html = file_get_contents(__DIR__ . '/Fixtures/workout.html');

        $actual = $polarMock->fetchWorkoutFromHTML($html);

        $this->assertEquals($expected, $actual);
    }

    /**
     * Get a track point.
     *
     * @param string $lat The latitude.
     * @param string $lon The longitude.
     * @param string $time The time.
     * @param integer $ele The elevation.
     * @param integer $hr The heart rate.
     * @return TrackPoint
     */
    private function getTrackPoint($lat, $lon, $time, $ele, $hr)
    {
        $trackPoint = new TrackPoint($lat, $lon, new DateTime($time));
        $trackPoint->setElevation($ele);
        $extensions = array(
            new HR($hr)
        );
        $trackPoint->setExtensions($extensions);
        return $trackPoint;
    }
} 