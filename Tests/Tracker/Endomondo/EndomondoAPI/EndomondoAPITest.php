<?php

namespace SportTrackerConnector\Tests\Tracker\Endomondo\EndomondoAPI;

use GuzzleHttp\Client;
use GuzzleHttp\Subscriber\Mock;
use SportTrackerConnector\Tracker\Endomondo\EndomondoAPI;
use SportTrackerConnector\Tracker\Endomondo\Sport;

/**
 * Test the EndomondoAPI.
 */
class EndomondoAPITest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test authentication is successful.
     */
    public function testAuthenticateSuccess()
    {
        $endomondo = $this->getEndomondoMock(array(__DIR__ . '/Fixtures/testAuthenticateSuccess.txt'), null);

        $this->assertEquals('asd3dD-aFArr8QQ', $endomondo->getAuthToken());
    }

    /**
     * Test authentication fails if token is not found.
     */
    public function testAuthenticateFailWhenTokenNotFoundInResponse()
    {
        $this->setExpectedException('\RuntimeException', 'Authentication on Endomondo failed.');

        $endomondo = $this->getEndomondoMock(array(__DIR__ . '/Fixtures/testAuthenticateFailWhenTokenNotFoundInResponse.txt'), null);
        $endomondo->getAuthToken();
    }

    /**
     * Test authentication fails if token is not found.
     */
    public function testAuthenticateFailWhenWrongCredentials()
    {
        $endomondo = $this->getEndomondoMock(array(__DIR__ . '/Fixtures/testAuthenticateFailWhenWrongCredentials.txt'), null);

        $this->setExpectedException('\RuntimeException', 'Authentication on Endomondo failed.');

        $endomondo->getAuthToken();
    }

    /**
     * Test getting a workout successful.
     */
    public function testGetWorkoutSuccess()
    {
        $endomondo = $this->getEndomondoMock(array(__DIR__ . '/Fixtures/testGetWorkoutSuccess.txt'));
        $workout = $endomondo->getWorkout(1);

        $this->assertJsonStringEqualsJsonFile(__DIR__ . '/Expected/testGetWorkoutSuccess.json', json_encode($workout));
    }

    /**
     * Test get workout throws exception if Endomondo page is not found.
     */
    public function testGetWorkoutThrowsExceptionIfEndomondoPageIsNotFound()
    {
        $this->setExpectedException('\RuntimeException', 'Could not get workout "1".');

        $endomondo = $this->getEndomondoMock(
            array(
                __DIR__ . '/Fixtures/testGetWorkoutThrowsExceptionIfEndomondoPageIsNotFound_0.txt',
                __DIR__ . '/Fixtures/testGetWorkoutThrowsExceptionIfEndomondoPageIsNotFound_1.txt'
            )
        );
        $endomondo->getWorkout(1);
    }

    /**
     * Test get workout throws exception if HTTP server response is not 200.
     */
    public function testGetWorkoutThrowsExceptionIfServerDoesNotReturn200()
    {
        $this->setExpectedException('\RuntimeException', 'Could not get workout "1".');

        $endomondo = $this->getEndomondoMock(array(__DIR__ . '/Fixtures/testGetWorkoutThrowsExceptionIfServerDoesNotReturn200.txt'));
        $endomondo->getWorkout(1);
    }

    /**
     * Test listing the workouts.
     */
    public function testListWorkoutsSuccess()
    {
        $startDate = new \DateTime('yesterday');
        $endDate = new \DateTime('today');

        $endomondo = $this->getEndomondoMock(array(__DIR__ . '/Fixtures/testListWorkoutsSuccess.txt'));
        $actual = $endomondo->listWorkouts($startDate, $endDate);

        $this->assertJsonStringEqualsJsonFile(__DIR__ . '/Expected/testListWorkoutsSuccess.json', json_encode($actual));
    }

    /**
     * Test list workouts throws exception if HTTP server response is not 200.
     */
    public function testListWorkoutsThrowsExceptionIfServerDoesNotReturn200()
    {
        $this->setExpectedException('\RuntimeException', 'Could not list workouts.');

        $startDate = new \DateTime('yesterday');
        $endDate = new \DateTime('today');

        $endomondo = $this->getEndomondoMock(array(__DIR__ . '/Fixtures/testListWorkoutsThrowsExceptionIfServerDoesNotReturn200.txt'));
        $endomondo->listWorkouts($startDate, $endDate);
    }

    /**
     * Test posting a workout.
     */
    public function testPostWorkoutSuccess()
    {
        $endomondo = $this->getEndomondoMock(
            array(
                __DIR__ . '/Fixtures/testPostWorkoutSuccess_0.txt',
                __DIR__ . '/Fixtures/testPostWorkoutSuccess_1.txt',
                __DIR__ . '/Fixtures/testPostWorkoutSuccess_2.txt'
            )
        );

        $track = $this->getMock('SportTrackerConnector\Workout\Workout\Track');
        $track->expects($this->once())->method('getSport')->will($this->returnValue(Sport::SWIMMING));
        $track->expects($this->once())->method('getDuration')->willReturn($this->getDateIntervalMock(0));
        $trackPoints = array_merge($this->getTrackPointMocks(150, true), $this->getTrackPointMocks(150));
        $track->expects($this->once())->method('getTrackPoints')->will($this->returnValue($trackPoints));

        $workout = $this->getMock('SportTrackerConnector\Workout\Workout');
        $workout->expects($this->once())->method('getTracks')->will($this->returnValue(array($track)));

        $post = $endomondo->postWorkout($workout);
        $this->assertSame(array('123456789'), $post);
    }

    /**
     * Test posting a workout throws an exception if the response from Endomondo is not "OK" string.
     */
    public function testPostWorkoutThrowsExceptionIfResponseIsNotOK()
    {
        $endomondo = $this->getEndomondoMock(array(__DIR__ . '/Fixtures/testPostWorkoutThrowsExceptionIfResponseIsNotOK.txt'));

        $track = $this->getMock('SportTrackerConnector\Workout\Workout\Track');
        $track->expects($this->once())->method('getSport')->will($this->returnValue(Sport::CYCLING_SPORT));
        $track->expects($this->once())->method('getDuration')->will($this->returnValue($this->getDateIntervalMock(0)));
        $track->expects($this->once())->method('getTrackPoints')->will($this->returnValue($this->getTrackPointMocks(50)));

        $workout = $this->getMock('SportTrackerConnector\Workout\Workout');
        $workout->expects($this->once())->method('getTracks')->will($this->returnValue(array($track)));

        $this->setExpectedException(
            '\RuntimeException',
            'Unexpected response from Endomondo. Data may be partially uploaded. Response was: ERROR'
        );

        $endomondo->postWorkout($workout);
    }

    /**
     * Get a number of track point mocks.
     *
     * @param integer $number Number of mocks to get.
     * @param boolean $distance Flag if distance should be present in the track point.
     * @return \SportTrackerConnector\Workout\Workout\TrackPoint[]
     */
    private function getTrackPointMocks($number, $distance = false)
    {
        $mocks = array();
        for ($i = 0; $i < $number; $i++) {
            $mocks[] = $this->getTrackPointMock($distance ? $i : null);
        }

        return $mocks;
    }

    /**
     * Get a track point mock.
     *
     * @param integer $distance The distance for the point.
     * @return \SportTrackerConnector\Workout\Workout\TrackPoint
     */
    private function getTrackPointMock($distance = null)
    {
        $trackPointMock = $this->getMockBuilder('SportTrackerConnector\Workout\Workout\TrackPoint')
            ->disableOriginalConstructor()
            ->getMock();
        $trackPointMock->expects($this->once())->method('getDateTime')->will($this->returnValue(new \DateTime()));
        $trackPointMock->expects($this->once())->method('getLatitude')->will($this->returnValue(rand(5353579, 5353479) / 100000));
        $trackPointMock->expects($this->once())->method('getLongitude')->will($this->returnValue(rand(10000000, 10100000) / 1000000));
        $trackPointMock->expects($this->once())->method('getElevation')->will($this->returnValue(rand(0, 100)));
        if ($distance !== null) {
            $trackPointMock->expects($this->once())->method('hasDistance')->will($this->returnValue(true));
            $trackPointMock->expects($this->once())->method('getDistance')->will($this->returnValue($distance));
        }
        $trackPointMock->expects($this->once())->method('hasExtension')->with('HR')->will($this->returnValue(false));
        return $trackPointMock;
    }

    /**
     * Get an EndomondoAPI mock.
     *
     * @param string[] $responses The responses for the client.
     * @param string $token The token for auth.
     * @return EndomondoAPI
     */
    private function getEndomondoMock(array $responses, $token = '123456')
    {
        $client = $this->getClientMock($responses);
        $sportMapper = $this->getMock('SportTrackerConnector\Workout\Workout\SportMapperInterface');

        $endomondo = new EndomondoAPI($client, 'email', 'test', $sportMapper);
        $endomondo->setAuthToken($token);

        return $endomondo;
    }

    /**
     * Get a Guzzle HTTP client with mocked responses.
     *
     * @param array $responses The responses for the client.
     * @return Client
     */
    private function getClientMock(array $responses)
    {
        $client = new Client();
        $mock = new Mock($responses);

        $client->getEmitter()->attach($mock);

        return $client;
    }

    /**
     * Get a mock for the date interval.
     *
     * @param integer $seconds The number of seconds to return when calling getTotalSeconds().
     * @return \SportTrackerConnector\Date\DateInterval
     */
    private function getDateIntervalMock($seconds = 0)
    {
        $dateInterval = $this->getMock('SportTrackerConnector\Date\DateInterval', array('getTotalSeconds'), array('PT1S'));
        $dateInterval->expects($this->once())->method('getTotalSeconds')->willReturn($seconds);
        return $dateInterval;
    }
}
