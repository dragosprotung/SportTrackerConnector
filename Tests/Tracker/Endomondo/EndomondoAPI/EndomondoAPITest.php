<?php

namespace SportTrackerConnector\Tests\Tracker\Endomondo\EndomondoAPI;

use SportTrackerConnector\Tracker\Endomondo\EndomondoAPI;
use SportTrackerConnector\Tracker\Endomondo\Sport;
use GuzzleHttp\Client;
use GuzzleHttp\Subscriber\Mock;

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
        $this->setExpectedException('\RuntimeException', 'Authentication on endomondo failed.');

        $endomondo = $this->getEndomondoMock(array(__DIR__ . '/Fixtures/testAuthenticateFailWhenTokenNotFoundInResponse.txt'), null);
        $endomondo->getAuthToken();
    }

    /**
     * Test authentication fails if token is not found.
     */
    public function testAuthenticateFailWhenWrongCredentials()
    {
        $endomondo = $this->getEndomondoMock(array(__DIR__ . '/Fixtures/testAuthenticateFailWhenWrongCredentials.txt'), null);

        $this->setExpectedException('\RuntimeException', 'Authentication on endomondo failed.');

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
     * Test posting a workout.
     */
    public function testPostWorkout()
    {
        $endomondo = $this->getEndomondoMock(
            array(
                __DIR__ . '/Fixtures/testPostWorkout_0.txt',
                __DIR__ . '/Fixtures/testPostWorkout_1.txt',
                __DIR__ . '/Fixtures/testPostWorkout_2.txt'
            )
        );

        $track = $this->getMock('SportTrackerConnector\Workout\Workout\Track');
        $track->expects($this->once())->method('getSport')->will($this->returnValue(Sport::CYCLING_SPORT));
        $track->expects($this->once())->method('getDuration')->will($this->returnValue(new \DateInterval('PT1H5M20S')));
        $track->expects($this->once())->method('getTrackPoints')->will($this->returnValue($this->getTrackPointMocks(150)));

        $workout = $this->getMock('SportTrackerConnector\Workout\Workout');
        $workout->expects($this->once())->method('getTracks')->will($this->returnValue(array($track)));

        $post = $endomondo->postWorkout($workout);
        $this->assertSame(array('123456789'), $post);
    }

    /**
     * Get a number of track point mocks.
     *
     * @param integer $number Number of mocks to get.
     * @return \SportTrackerConnector\Workout\Workout\TrackPoint[]
     */
    private function getTrackPointMocks($number)
    {
        $mocks = array();
        for ($i = 0; $i < $number; $i++) {
            $mocks[] = $this->getTrackPointMock();
        }

        return $mocks;
    }

    /**
     * Get a track point mock.
     *
     * @return \SportTrackerConnector\Workout\Workout\TrackPoint
     */
    private function getTrackPointMock()
    {
        $trackPointMock = $this->getMockBuilder('SportTrackerConnector\Workout\Workout\TrackPoint')
            ->disableOriginalConstructor()
            ->getMock();
        $trackPointMock->expects($this->once())->method('getDateTime')->will($this->returnValue(new \DateTime()));
        $trackPointMock->expects($this->once())->method('getLatitude')->will($this->returnValue(rand(5353579, 5353479) / 100000));
        $trackPointMock->expects($this->once())->method('getLongitude')->will($this->returnValue(rand(10000000, 10100000) / 1000000));
        $trackPointMock->expects($this->once())->method('getElevation')->will($this->returnValue(rand(0, 100)));
        $trackPointMock->expects($this->once())->method('hasExtension')->with('HR')->will($this->returnValue(false));
        return $trackPointMock;
    }

    /**
     * Get an EndomondoAPI mock.
     *
     * @param array $responses The responses for the client.
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
}