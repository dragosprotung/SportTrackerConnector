<?php

namespace SportTrackerConnector\Tests\Tracker\Polar\Polar;

use DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\Subscriber\Mock;
use SportTrackerConnector\Tracker\Polar\Polar;
use SportTrackerConnector\Tracker\TrackerListWorkoutsResult;
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

        $workoutTCX = file_get_contents(__DIR__ . '/Fixtures/workout-single.tcx');
        $polarAPIMock = $this->getPolarAPIMock(array('fetchWorkoutTCX'));
        $polarAPIMock->expects($this->once())->method('fetchWorkoutTCX')->with($idWorkout)->willReturn($workoutTCX);

        $polarMock = $this->getMock('SportTrackerConnector\Tracker\Polar\Polar', array('getPolarAPI'), array($loggerMock));
        $polarMock->expects($this->once())->method('getPolarAPI')->willReturn($polarAPIMock);

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

        $workoutTCX = file_get_contents(__DIR__ . '/Fixtures/workout-multi.tcx');
        $polarAPIMock = $this->getPolarAPIMock(array('fetchWorkoutTCX'));
        $polarAPIMock->expects($this->once())->method('fetchWorkoutTCX')->with($idWorkout)->willReturn($workoutTCX);

        $polarMock = $this->getMock('SportTrackerConnector\Tracker\Polar\Polar', array('getPolarAPI'), array($loggerMock));
        $polarMock->expects($this->once())->method('getPolarAPI')->willReturn($polarAPIMock);

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
     * Test fetching a list of workouts.
     */
    public function testListWorkoutsSuccess()
    {
        $startDate = new \DateTime('yesterday');
        $endDate = new \DateTime('today');
        $loggerMock = $this->getMock('Psr\Log\LoggerInterface');

        $APIReturn = json_decode(file_get_contents(__DIR__ . '/Fixtures/testListWorkoutsSuccess.json'), true);
        $polarAPIMock = $this->getPolarAPIMock(array('listCalendarEvents'));
        $polarAPIMock->expects($this->once())->method('listCalendarEvents')->with($startDate, $endDate)->willReturn($APIReturn);

        $polarMock = $this->getMock('SportTrackerConnector\Tracker\Polar\Polar', array('getPolarAPI'), array($loggerMock));
        $polarMock->expects($this->once())->method('getPolarAPI')->willReturn($polarAPIMock);

        $actual = $polarMock->listWorkouts($startDate, $endDate);

        $expected = array(
            $this->getTrackerListWorkoutsResultMock('111111', '2015-05-01 15:01:18'),
            $this->getTrackerListWorkoutsResultMock('222222', '2015-05-02 13:40:57'),
            $this->getTrackerListWorkoutsResultMock('333333', '2015-05-10 18:27:34')
        );

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

    /**
     * Get a Polar API mock.
     *
     * @param string[] $mockMethods The methods to mock.
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getPolarAPIMock(array $mockMethods = null)
    {
        $client = new Client();
        $mock = new Mock(array(__DIR__ . '/Fixtures/loginIntoPolar.txt'));
        $client->getEmitter()->attach($mock);

        $sportMapper = $this->getMock('SportTrackerConnector\Workout\Workout\SportMapperInterface');
        $polarAPI = $this->getMock(
            '\SportTrackerConnector\Tracker\Polar\API',
            $mockMethods,
            array($client, null, null, $sportMapper)
        );

        return $polarAPI;
    }

    /**
     * Get a mock (not really) of a tracker list workout result.
     *
     * @param string $id The ID of the workout.
     * @param string $startDateTime The start date and time of the workout.
     * @return TrackerListWorkoutsResult
     */
    private function getTrackerListWorkoutsResultMock($id, $startDateTime)
    {
        $startDateTime = new \DateTime($startDateTime, new \DateTimeZone('UTC'));
        return new TrackerListWorkoutsResult($id, SportMapperInterface::OTHER, $startDateTime);
    }
}
