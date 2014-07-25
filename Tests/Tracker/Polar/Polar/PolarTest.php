<?php

namespace SportTrackerConnector\Tests\Tracker\Polar\Polar;

use DateTimeZone;
use DateTime;
use SportTrackerConnector\Workout\Workout;
use SportTrackerConnector\Workout\Workout\Track;
use SportTrackerConnector\Workout\Workout\TrackPoint;
use SportTrackerConnector\Workout\Workout\SportMapperInterface;
use SportTrackerConnector\Workout\Workout\Extension\HR;

/**
 * Test the Polar tracker.
 */
class PolarTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test fetching a workout with one sport from an HTML page.
     */
    public function testFetchWorkoutFromHTMLWithSingleSport()
    {
        $loggerMock = $this->getMock('Psr\Log\LoggerInterface');

        $polarMock = $this->getMock('SportTrackerConnector\Tracker\Polar\Polar', array('getTimeZone'), array($loggerMock));
        $polarMock->expects($this->any())->method('getTimeZone')->will($this->returnValue(new DateTimeZone('Europe/Berlin')));

        $expected = new Workout();
        $expected->addTrack(
            new Track(
                array(
                    $this->getTrackPoint('53.551075', '9.993672', '2014-05-30T17:12:58+00:00', null, 78),
                    $this->getTrackPoint('53.550085', '9.992682', '2014-05-30T17:12:59+00:00', null, 88)
                ),
                SportMapperInterface::RUNNING
            )
        );

        $html = file_get_contents(__DIR__ . '/Fixtures/workout-single.html');

        $actual = $polarMock->fetchWorkoutFromHTML($html);

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test fetching a workout with multiple sports from an HTML page.
     */
    public function testFetchWorkoutFromHTMLWithMultiSport()
    {
        $loggerMock = $this->getMock('Psr\Log\LoggerInterface');

        $polarMock = $this->getMock('SportTrackerConnector\Tracker\Polar\Polar', array('getTimeZone'), array($loggerMock));
        $polarMock->expects($this->any())->method('getTimeZone')->will($this->returnValue(new DateTimeZone('Europe/Berlin')));

        $expected = new Workout();
        $expected->addTrack(
            new Track(
                array(
                    $this->getTrackPoint('53.551075', '9.993672', '2014-05-30T17:12:58+00:00', null, 78),
                    $this->getTrackPoint('53.550085', '9.992682', '2014-05-30T17:12:59+00:00', null, 88)
                ),
                SportMapperInterface::CYCLING_SPORT
            )
        );
        $expected->addTrack(
            new Track(
                array(
                    $this->getTrackPoint('53.551074', '9.993671', '2014-05-30T17:12:58+00:00', null, 78),
                    $this->getTrackPoint('53.550084', '9.992681', '2014-05-30T17:12:59+00:00', null, 88)
                ),
                SportMapperInterface::RUNNING
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