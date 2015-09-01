<?php

namespace SportTrackerConnector\Tests\Tracker\Endomondo\Tracker;

use GuzzleHttp\Client;
use SportTrackerConnector\Tracker\Endomondo\Tracker as EndomondoTracker;
use SportTrackerConnector\Core\Tracker\TrackerListWorkoutsResult;
use SportTrackerConnector\Core\Workout\Extension\HR;
use SportTrackerConnector\Core\Workout\Track;
use SportTrackerConnector\Core\Workout\Workout;
use SportTrackerConnector\Core\Workout\SportMapperInterface;
use SportTrackerConnector\Core\Workout\TrackPoint;

/**
 * Endomondo tracker test.
 */
class TrackerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test getting the ID of the tracker.
     */
    public function testGetID()
    {
        $expected = 'endomondo';

        $this->assertSame($expected, EndomondoTracker::getID());

        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $endomondo = new EndomondoTracker($logger, null, null);
        $this->assertSame($expected, $endomondo->getID());
    }

    /**
     * Test getting the endomondo API returns same object.
     */
    public function testGettingTheEndomondoAPIReturnsSameObject()
    {
        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $endomondo = new EndomondoTracker($logger, null, null);

        $endomondoAPI = $endomondo->getEndomondoAPI();
        $this->assertInstanceOf('SportTrackerConnector\Tracker\Endomondo\API', $endomondoAPI);

        $this->assertSame($endomondoAPI, $endomondo->getEndomondoAPI());

    }

    /**
     * Test upload a workout.
     */
    public function testUploadWorkout()
    {
        $workoutMock = $this->getMock('SportTrackerConnector\Core\Workout\Workout');
        $workoutID = 123;

        $endomondoAPI = $this->getEndomondoAPIMock(array('postWorkout'));
        $endomondoAPI->expects($this->once())->method('postWorkout')->with($workoutMock)->willReturn($workoutID);

        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $endomondo = $this->getMock('SportTrackerConnector\Tracker\Endomondo\Tracker', array('getEndomondoAPI'), array($logger));
        $endomondo->expects($this->once())->method('getEndomondoAPI')->willReturn($endomondoAPI);

        $actual = $endomondo->uploadWorkout($workoutMock);
        $this->assertTrue($actual);
    }

    /**
     * Test list workouts returns empty array if no workouts are found.
     */
    public function testListWorkoutsReturnsEmptyArrayIfNoWorkoutsAreFound()
    {
        $startDate = new \DateTime('yesterday');
        $endDate = new \DateTime('today');

        $endomondoAPI = $this->getEndomondoAPIMock(array('listWorkouts'));
        $APIReturn = array();
        $APIReturn['more'] = false;
        $APIReturn['data'] = array();
        $endomondoAPI->expects($this->once())->method('listWorkouts')->with($startDate, $endDate)->willReturn($APIReturn);

        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $endomondo = $this->getMock('SportTrackerConnector\Tracker\Endomondo\Tracker', array('getEndomondoAPI'), array($logger));
        $endomondo->expects($this->once())->method('getEndomondoAPI')->willReturn($endomondoAPI);

        $actual = $endomondo->listWorkouts($startDate, $endDate);
        $this->assertEmpty($actual);
    }

    /**
     * Test list workouts.
     */
    public function testListWorkoutsSuccess()
    {
        $startDate = new \DateTime('yesterday');
        $endDate = new \DateTime('today');

        $endomondoAPI = $this->getEndomondoAPIMock(array('listWorkouts'));
        $APIReturn = json_decode(file_get_contents(__DIR__ . '/Fixtures/testListWorkoutsSuccess.json'), true);
        $endomondoAPI->expects($this->once())->method('listWorkouts')->with($startDate, $endDate)->willReturn($APIReturn);

        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $endomondo = $this->getMock('SportTrackerConnector\Tracker\Endomondo\Tracker', array('getEndomondoAPI'), array($logger));
        $endomondo->expects($this->once())->method('getEndomondoAPI')->willReturn($endomondoAPI);

        $actual = $endomondo->listWorkouts($startDate, $endDate);

        $expected = array(
            $this->getTrackerListWorkoutsResultMock('111111', '2014-07-24 18:45:00'),
            $this->getTrackerListWorkoutsResultMock('222222', '2014-07-24 16:55:00'),
            $this->getTrackerListWorkoutsResultMock('333333', '2014-07-22 18:32:00')
        );

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test downloading a workout without points.
     */
    public function testDownloadWorkoutWithNoPoints()
    {
        $idWorkout = 1;

        $endomondoAPI = $this->getEndomondoAPIMock(array('getWorkout'));
        $endomondoAPI->expects($this->once())->method('getWorkout')->with($idWorkout)->willReturn(array('sport' => 0));

        $logger = $this->getMockForAbstractClass('Psr\Log\LoggerInterface', array('warning'));
        $logger->expects($this->once())->method('warning')->with('No track points found for workout "' . $idWorkout . '".');

        $endomondo = $this->getMock('SportTrackerConnector\Tracker\Endomondo\Tracker', array('getEndomondoAPI'), array($logger));
        $endomondo->expects($this->once())->method('getEndomondoAPI')->willReturn($endomondoAPI);

        $actual = $endomondo->downloadWorkout($idWorkout);

        $expected = new Workout();
        $track = new Track();
        $track->setSport(SportMapperInterface::RUNNING);
        $expected->addTrack($track);

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test downloading a workout with points.
     */
    public function testDownloadWorkoutWithPoints()
    {
        $idWorkout = 1;

        $endomondoAPI = $this->getEndomondoAPIMock(array('getWorkout'));
        $endomondoAPI->expects($this->once())->method('getWorkout')->with($idWorkout)->willReturn(
            array(
                'sport' => 15,
                'points' => array(
                    array('lat' => '10.0', 'lng' => '53.0', 'time' => '2014-07-27 20:33:15'),
                    array('lat' => '10.1', 'lng' => '53.1', 'time' => '2014-07-27 20:33:16', 'alt' => 10, 'hr' => 140),
                    array('lat' => '10.2', 'lng' => '53.2', 'time' => '2014-07-27 20:33:17', 'alt' => -2, 'hr' => 150)
                )
            )
        );

        $logger = $this->getMockForAbstractClass('Psr\Log\LoggerInterface');

        $endomondo = $this->getMock('SportTrackerConnector\Tracker\Endomondo\Tracker', array('getEndomondoAPI'), array($logger));
        $endomondo->expects($this->once())->method('getEndomondoAPI')->willReturn($endomondoAPI);

        $actual = $endomondo->downloadWorkout($idWorkout);

        $expected = new Workout();
        $track = new Track();
        $track->setSport(SportMapperInterface::GOLF);
        $track->addTrackPoint($this->getTrackPoint(10, 53, '2014-07-27 20:33:15'));
        $track->addTrackPoint($this->getTrackPoint(10.1, 53.1, '2014-07-27 20:33:16', 10, 140));
        $track->addTrackPoint($this->getTrackPoint(10.2, 53.2, '2014-07-27 20:33:17', -2, 150));
        $expected->addTrack($track);

        $this->assertEquals($expected, $actual);
    }

    /**
     * Get an Endomondo API mock.
     *
     * @param string[] $mockMethods The methods to mock.
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getEndomondoAPIMock($mockMethods = array())
    {
        $client = new Client();
        $sportMapper = $this->getMock('SportTrackerConnector\Core\Workout\SportMapperInterface');
        $endomondoAPI = $this->getMock(
            'SportTrackerConnector\Tracker\Endomondo\API',
            $mockMethods,
            array($client, null, null, $sportMapper)
        );

        return $endomondoAPI;
    }

    /**
     * Get a mock (not really) of a tracker list workout result.
     *
     * @param string $id The ID of the workout.
     * @param string $startDateTime The start date and time of the workout.
     * @param string $sport The sport.
     * @return TrackerListWorkoutsResult
     */
    private function getTrackerListWorkoutsResultMock($id, $startDateTime, $sport = 'running')
    {
        $startDateTime = new \DateTime($startDateTime, new \DateTimeZone('UTC'));
        return new TrackerListWorkoutsResult($id, $sport, $startDateTime);
    }

    /**
     * Get a track point mock.
     *
     * @param float $latitude The latitude.
     * @param float $longitude The longitude.
     * @param string $dateTime The date and time of the point.
     * @param integer $elevation The elevation (in meters).
     * @param integer $hr The hear rate.
     * @return \SportTrackerConnector\Core\Workout\TrackPoint
     */
    private function getTrackPoint($latitude, $longitude, $dateTime, $elevation = null, $hr = null)
    {
        $trackPoint = new TrackPoint($latitude, $longitude, new \DateTime($dateTime, new \DateTimeZone('UTC')));
        if ($elevation !== null) {
            $trackPoint->setElevation($elevation);
        }

        if ($hr !== null) {
            $trackPoint->addExtension(new HR($hr));
        }

        return $trackPoint;
    }
}
