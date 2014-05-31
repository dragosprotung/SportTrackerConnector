<?php

namespace FitnessTrackingPorting\Tests\Tracker\Polar\Polar;

use DateTimeZone;
use DateTime;
use FitnessTrackingPorting\Tracker\Polar\Polar;
use FitnessTrackingPorting\Workout\Workout;
use FitnessTrackingPorting\Workout\Workout\Track;
use FitnessTrackingPorting\Workout\Workout\TrackPoint;
use FitnessTrackingPorting\Workout\Workout\Sport;
use FitnessTrackingPorting\Workout\Workout\Extension\HR;

class PolarTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test fetching a workout with one sport from an HTML page.
     */
    public function testFetchWorkoutFromHTMLWithSingleSport()
    {
        $polarMock = $this->getMock('FitnessTrackingPorting\Tracker\Polar\Polar', array('getTimeZone'), array(null, null));
        $polarMock->expects($this->any())->method('getTimeZone')->will($this->returnValue(new DateTimeZone('Europe/Berlin')));

        $expected = new Workout();
        $expected->addTrack(
            new Track(
                array(
                    $this->getTrackPoint('53.551075', '9.993672', '2014-05-30T17:12:58+00:00', null, 78),
                    $this->getTrackPoint('53.550085', '9.992682', '2014-05-30T17:12:59+00:00', null, 88)
                ),
                Sport::RUNNING
            )
        );

        $html = file_get_contents(__DIR__ . '/Fixtures/workout-single.html');

        $actual = $polarMock->fetchWorkoutFromHTML($html);

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test fetching a workout with multiple sports from an HTML page.
     * @group ttt
     */
    public function testFetchWorkoutFromHTMLWithMultiSport()
    {
        $polarMock = $this->getMock('FitnessTrackingPorting\Tracker\Polar\Polar', array('getTimeZone'), array(null, null));
        $polarMock->expects($this->any())->method('getTimeZone')->will($this->returnValue(new DateTimeZone('Europe/Berlin')));

        $expected = new Workout();
        $expected->addTrack(
            new Track(
                array(
                    $this->getTrackPoint('53.551075', '9.993672', '2014-05-30T17:12:58+00:00', null, 78),
                    $this->getTrackPoint('53.550085', '9.992682', '2014-05-30T17:12:59+00:00', null, 88)
                ),
                Sport::CYCLING_SPORT
            )
        );
        $expected->addTrack(
            new Track(
                array(
                    $this->getTrackPoint('53.551074', '9.993671', '2014-05-30T17:12:58+00:00', null, 78),
                    $this->getTrackPoint('53.550084', '9.992681', '2014-05-30T17:12:59+00:00', null, 88)
                ),
                Sport::RUNNING
            )
        );

        $html = file_get_contents(__DIR__ . '/Fixtures/workout-multi.html');

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