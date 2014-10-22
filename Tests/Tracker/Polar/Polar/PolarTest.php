<?php

namespace SportTrackerConnector\Tests\Tracker\Polar\Polar;

use DateTime;
use SportTrackerConnector\Tracker\Polar\Polar;
use SportTrackerConnector\Workout\Workout;
use SportTrackerConnector\Workout\Workout\Extension\HR;
use SportTrackerConnector\Workout\Workout\SportMapperInterface;
use SportTrackerConnector\Workout\Workout\Track;
use SportTrackerConnector\Workout\Workout\TrackPoint;

/**
 * Test the Polar tracker.
 */
class PolarTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test getting the ID of the tracker.
     */
    public function testGetID()
    {
        $expected = 'polar';

        $this->assertSame($expected, Polar::getID());

        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $polar = new Polar($logger, null, null);
        $this->assertSame($expected, $polar->getID());
    }

    /**
     * Test fetching a workout with one sport from an HTML page.
     */
    public function testDownloadWorkoutWithSingleSport()
    {
        $idWorkout = 1;

        $loggerMock = $this->getMock('Psr\Log\LoggerInterface');

        $polarMock = $this->getMock('SportTrackerConnector\Tracker\Polar\Polar', array('fetchWorkoutTCX'), array($loggerMock));
        $workoutTCX = file_get_contents(__DIR__ . '/Fixtures/workout-single.tcx');
        $polarMock->expects($this->once())->method('fetchWorkoutTCX')->with($idWorkout)->willReturn($workoutTCX);

        $expected = new Workout();
        $expected->addTrack(
            new Track(
                array(
                    $this->getTrackPoint('53.551075', '9.993672', '2014-05-30T17:12:58+00:00', '2.9', 78),
                    $this->getTrackPoint('53.550085', '9.992682', '2014-05-30T17:12:59+00:00', '6.86', 88)
                ),
                SportMapperInterface::RUNNING
            )
        );

        $actual = $polarMock->downloadWorkout($idWorkout);

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test fetching a workout with multiple sports from an HTML page.
     */
    public function testFetchWorkoutFromHTMLWithMultiSport()
    {
        $idWorkout = 1;

        $loggerMock = $this->getMock('Psr\Log\LoggerInterface');

        $polarMock = $this->getMock('SportTrackerConnector\Tracker\Polar\Polar', array('fetchWorkoutTCX'), array($loggerMock));
        $workoutTCX = file_get_contents(__DIR__ . '/Fixtures/workout-multi.tcx');
        $polarMock->expects($this->once())->method('fetchWorkoutTCX')->with($idWorkout)->willReturn($workoutTCX);

        $expected = new Workout();
        $expected->addTrack(
            new Track(
                array(
                    $this->getTrackPoint('53.551075', '9.993672', '2014-05-30T17:12:58+00:00', '-4.11', 78),
                    $this->getTrackPoint('53.550085', '9.992682', '2014-05-30T17:12:59+00:00', '-4.11', 88)
                ),
                SportMapperInterface::CYCLING_SPORT
            )
        );
        $expected->addTrack(
            new Track(
                array(
                    $this->getTrackPoint('53.551074', '9.993671', '2014-05-30T17:12:58+00:00', '6.10', 78),
                    $this->getTrackPoint('53.550084', '9.992681', '2014-05-30T17:12:59+00:00', '6.10', 88)
                ),
                SportMapperInterface::RUNNING
            )
        );

        $actual = $polarMock->downloadWorkout($idWorkout);

        $this->assertEquals($expected, $actual);
    }

    /**
     * Get a track point.
     *
     * @param string $lat The latitude.
     * @param string $lon The longitude.
     * @param string $time The time.
     * @param integer $ele The elevation.
     * @param integer $heartRate The heart rate.
     * @return TrackPoint
     */
    private function getTrackPoint($lat, $lon, $time, $ele, $heartRate)
    {
        $trackPoint = new TrackPoint($lat, $lon, new DateTime($time));
        $trackPoint->setElevation($ele);
        $extensions = array(
            new HR($heartRate)
        );
        $trackPoint->setExtensions($extensions);
        return $trackPoint;
    }
}
