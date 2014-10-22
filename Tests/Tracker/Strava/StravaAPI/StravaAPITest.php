<?php

namespace Tracker\Strava\StravaAPI;

use GuzzleHttp\Client;
use GuzzleHttp\Subscriber\Mock;
use SportTrackerConnector\Tracker\Strava\StravaAPI;
use SportTrackerConnector\Workout\Workout;

/**
 * Test the StravaAPI.
 */
class StravaAPITest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test getting a workout successful.
     */
    public function testGetWorkoutSuccess()
    {
        $strava = $this->getStravaAPIMock(
            array(
                __DIR__ . '/Fixtures/testGetWorkoutSuccess_0.txt',
                __DIR__ . '/Fixtures/testGetWorkoutSuccess_1.txt'
            )
        );
        $actual = $strava->getWorkout(111111);

        $expected = array(
            'latlng' =>
                array(
                    array(53.53, 10.10),
                    array(53.54, 10.11),
                    array(53.55, 10.12),
                ),
            'time' =>
                array(
                    new \DateTime('2014-10-13 17:36:48', new \DateTimeZone('+00:00')),
                    new \DateTime('2014-10-13 17:36:49', new \DateTimeZone('+00:00')),
                    new \DateTime('2014-10-13 17:36:50', new \DateTimeZone('+00:00')),
                ),
            'altitude' => array(6.0, 6.1, 6.2),
            'heartrate' => array(95, 96, 97)
        );

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test that getting a workout throws an exception when the stream is not found.
     */
    public function testGetWorkoutThrowsExceptionWhenNoStreamsAreFound()
    {
        $strava = $this->getStravaAPIMock(
            array(
                __DIR__ . '/Fixtures/testGetWorkoutThrowsExceptionWhenNoStreamsAreFound_0.txt',
                __DIR__ . '/Fixtures/testGetWorkoutThrowsExceptionWhenNoStreamsAreFound_1.txt'
            )
        );

        $this->setExpectedException('RuntimeException', 'Workout "111111" has no track points.');

        $strava->getWorkout(111111);
    }

    /**
     * Test getting a workout throws exception when workout is not found.
     */
    public function testGetWorkoutThrowsExceptionWhenWorkoutIsNotFound()
    {
        $strava = $this->getStravaAPIMock(array(__DIR__ . '/Fixtures/testGetWorkoutThrowsExceptionWhenWorkoutIsNotFound.txt'));

        $this->setExpectedException('RuntimeException', 'Workout "111111" not found.');

        $strava->getWorkout(111111);
    }

    /**
     * Test listing the workouts.
     */
    public function testListWorkoutsSuccess()
    {
        $startDate = new \DateTime('2014-10-13');
        $endDate = new \DateTime('2014-10-15');

        $strava = $this->getStravaAPIMock(array(__DIR__ . '/Fixtures/testListWorkoutsSuccess.txt'));
        $actual = $strava->listWorkouts($startDate, $endDate);

        $this->assertJsonStringEqualsJsonFile(__DIR__ . '/Expected/testListWorkoutsSuccess.json', json_encode($actual));
    }

    /**
     * Test post workout throws exception when response is not 20X.
     */
    public function testPostWorkoutThrowsExceptionWhenResponseIsNot20X()
    {
        $strava = $this->getStravaAPIMock(array(__DIR__ . '/Fixtures/testPostWorkoutThrowsExceptionWhenResponseIsNot20X.txt'));

        $workout = new Workout();

        $this->setExpectedException(
            'RuntimeException',
            'Could not upload workout to strava.com. There was an error processing your activity. Error: '
        );

        $strava->postWorkout($workout);
    }

    /**
     * Test post workout throws exception when JSON response error field is not null.
     */
    public function testPostWorkoutThrowsExceptionWhenJSONResponseErrorFieldIsNotNull()
    {
        $strava = $this->getStravaAPIMock(array(__DIR__ . '/Fixtures/testPostWorkoutThrowsExceptionWhenJSONResponseErrorFieldIsNotNull.txt'));

        $workout = new Workout();

        $this->setExpectedException('RuntimeException', 'Strava returned error message: There was an error. Status: Some status message.');

        $strava->postWorkout($workout);
    }

    /**
     * Test posting a workout returns status if upload is successful.
     */
    public function testPostWorkoutReturnsStatusIfUploadIsSuccessful()
    {
        $strava = $this->getStravaAPIMock(array(__DIR__ . '/Fixtures/testPostWorkoutReturnsStatusIfUploadIsSuccessful.txt'));

        $workout = new Workout();

        $actual = $strava->postWorkout($workout);
        $expected = 'Your activity is still being processed.';
        $this->assertSame($expected, $actual);
    }

    /**
     * Get an StravaAPI mock.
     *
     * @param string[] $responses The responses for the client.
     * @param string $accessToken The token for auth.
     * @return StravaAPI
     */
    private function getStravaAPIMock(array $responses, $accessToken = '1234567890abc')
    {
        $client = $this->getClientMock($responses);
        $sportMapper = $this->getMock('SportTrackerConnector\Workout\Workout\SportMapperInterface');

        return new StravaAPI($client, $accessToken, $sportMapper);
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
