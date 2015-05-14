<?php

namespace SportTrackerConnector\Tests\Tracker\Strava\Tracker;

use GuzzleHttp\Client;
use SportTrackerConnector\Tracker\Exception\NoTrackPointsFoundException;
use SportTrackerConnector\Tracker\Strava\Tracker as StravaTracker;
use SportTrackerConnector\Tracker\TrackerListWorkoutsResult;
use SportTrackerConnector\Workout\Workout;
use SportTrackerConnector\Workout\Workout\Extension\HR;
use SportTrackerConnector\Workout\Workout\Track;
use SportTrackerConnector\Workout\Workout\TrackPoint;

/**
 * Strava tracker test.
 */
class TrackerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test getting the ID of the tracker.
     */
    public function testGetID()
    {
        $expected = 'strava';

        $this->assertSame($expected, StravaTracker::getID());

        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $strava = new StravaTracker($logger, null);
        $this->assertSame($expected, $strava->getID());
    }

    /**
     * Test getting the strava API returns same object.
     */
    public function testGettingTheStravaAPIReturnsSameObject()
    {
        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $strava = new StravaTracker($logger, null);

        $stravaAPI = $strava->getStravaAPI();
        $this->assertInstanceOf('SportTrackerConnector\Tracker\Strava\API', $stravaAPI);

        $this->assertSame($stravaAPI, $strava->getStravaAPI());
    }

    /**
     * Test list workouts returns empty array if no workouts are found.
     */
    public function testListWorkoutsReturnsEmptyArrayIfNoWorkoutsAreFound()
    {
        $startDate = new \DateTime('yesterday');
        $endDate = new \DateTime('today');

        $stravaAPI = $this->getStravaAPIMock(array('listWorkouts'));
        $stravaAPI->expects($this->once())->method('listWorkouts')->with($startDate, $endDate)->willReturn(array());

        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $accessToken = '1234567890abc';
        $strava = $this->getMock('SportTrackerConnector\Tracker\Strava\Tracker', array('getStravaAPI'), array($logger, $accessToken));
        $strava->expects($this->once())->method('getStravaAPI')->willReturn($stravaAPI);

        $actual = $strava->listWorkouts($startDate, $endDate);
        $this->assertEmpty($actual);
    }

    /**
     * Test list workouts.
     */
    public function testListWorkoutsSuccess()
    {
        $startDate = new \DateTime('yesterday');
        $endDate = new \DateTime('today');

        $stravaAPI = $this->getStravaAPIMock(array('listWorkouts'));
        $apiReturn = json_decode(file_get_contents(__DIR__ . '/Fixtures/testListWorkoutsSuccess.json'), true);
        $stravaAPI->expects($this->once())->method('listWorkouts')->with($startDate, $endDate)->willReturn($apiReturn);

        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $accessToken = '1234567890abc';
        $strava = $this->getMock('SportTrackerConnector\Tracker\Strava\Tracker', array('getStravaAPI'), array($logger, $accessToken));
        $strava->expects($this->once())->method('getStravaAPI')->willReturn($stravaAPI);

        $actual = $strava->listWorkouts($startDate, $endDate);

        $expected = array(
            $this->getTrackerListWorkoutsResultMock('111111', '2014-10-13 17:36:48'),
            $this->getTrackerListWorkoutsResultMock('222222', '2014-10-14 17:36:48'),
        );

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test downloading a workout without points.
     */
    public function testDownloadWorkoutWithNoPoints()
    {
        $idWorkout = 1;

        $stravaAPI = $this->getStravaAPIMock(array('getWorkout'));
        $stravaAPI->expects($this->once())->method('getWorkout')->with($idWorkout)->willThrowException(new NoTrackPointsFoundException());

        $logger = $this->getMockForAbstractClass('Psr\Log\LoggerInterface', array('warning'));
        $logger->expects($this->once())->method('warning')->with('No track points found for workout "' . $idWorkout . '".');

        $accessToken = '1234567890abc';

        $strava = $this->getMock('SportTrackerConnector\Tracker\Strava\Tracker', array('getStravaAPI'), array($logger, $accessToken));
        $strava->expects($this->once())->method('getStravaAPI')->willReturn($stravaAPI);

        $actual = $strava->downloadWorkout($idWorkout);

        $expected = new Workout();
        $expected->addTrack(new Track());

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test downloading a workout with points.
     */
    public function testDownloadWorkoutWithPoints()
    {
        $idWorkout = 1;

        $stravaAPI = $this->getStravaAPIMock(array('getWorkout'));
        $stravaAPI->expects($this->once())->method('getWorkout')->with($idWorkout)->willReturn(
            array(
                'latlng' => array(
                    array('10.0', '53.0'),
                    array('10.1', '53.1'),
                    array('10.2', '53.2')
                ),
                'time' => array(
                    new \DateTime('2014-07-27 20:33:15', new \DateTimeZone('UTC')),
                    new \DateTime('2014-07-27 20:33:16', new \DateTimeZone('UTC')),
                    new \DateTime('2014-07-27 20:33:17', new \DateTimeZone('UTC'))
                ),
                'altitude' => array(0, 10, -2),
                'heartrate' => array(130, 140, 150),
            )
        );

        $logger = $this->getMockForAbstractClass('Psr\Log\LoggerInterface');

        $accessToken = '1234567890abc';

        $strava = $this->getMock('SportTrackerConnector\Tracker\Strava\Tracker', array('getStravaAPI'), array($logger, $accessToken));
        $strava->expects($this->once())->method('getStravaAPI')->willReturn($stravaAPI);

        $actual = $strava->downloadWorkout($idWorkout);

        $expected = new Workout();
        $track = new Track();
        $track->addTrackPoint($this->getTrackPoint(10, 53, '2014-07-27 20:33:15', 0, 130));
        $track->addTrackPoint($this->getTrackPoint(10.1, 53.1, '2014-07-27 20:33:16', 10, 140));
        $track->addTrackPoint($this->getTrackPoint(10.2, 53.2, '2014-07-27 20:33:17', -2, 150));
        $expected->addTrack($track);

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test uploading a workout with success.
     */
    public function testUploadWorkoutSuccess()
    {
        $workout = new Workout();

        $stravaAPI = $this->getStravaAPIMock(array('postWorkout'));
        $stravaAPI->expects($this->once())->method('postWorkout')->with($workout)->willReturn('Upload status.');

        $logger = $this->getMockForAbstractClass('Psr\Log\LoggerInterface', array('warning'));
        $logger->expects($this->once())->method('info')->with('Workout to strava executed. Upload status.');

        $accessToken = '1234567890abc';

        $strava = $this->getMock('SportTrackerConnector\Tracker\Strava\Tracker', array('getStravaAPI'), array($logger, $accessToken));
        $strava->expects($this->once())->method('getStravaAPI')->willReturn($stravaAPI);

        $actual = $strava->uploadWorkout($workout);

        $this->assertTrue($actual);
    }

    /**
     * Get an Strava API mock.
     *
     * @param string[] $mockMethods The methods to mock.
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getStravaAPIMock($mockMethods = array())
    {
        $client = new Client();
        $accessToken = '1234567890abc';
        $sportMapper = $this->getMock('SportTrackerConnector\Workout\Workout\SportMapperInterface');
        $stravaAPI = $this->getMock(
            'SportTrackerConnector\Tracker\Strava\API',
            $mockMethods,
            array($client, $accessToken, $sportMapper)
        );

        return $stravaAPI;
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
        return new TrackerListWorkoutsResult($id, 'running', $startDateTime);
    }

    /**
     * Get a track point mock.
     *
     * @param float $latitude The latitude.
     * @param float $longitude The longitude.
     * @param string $dateTime The date and time of the point.
     * @param integer $elevation The elevation (in meters).
     * @param integer $hr The hear rate.
     * @return \SportTrackerConnector\Workout\Workout\TrackPoint
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
