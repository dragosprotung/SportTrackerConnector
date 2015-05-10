<?php

namespace Tracker\Polar\API;

use GuzzleHttp\Client;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Subscriber\Mock;
use SportTrackerConnector\Workout\Workout;

/**
 * Test the Polar API.
 */
class APITest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test fetching a workout TCX extracts the TCX from the zip archive coming from Polar.
     */
    public function testFetchWorkoutTCXExtractsTCXFromZip()
    {
        $idWorkout = 1;

        $response = new Response(
            200,
            array(
                'Content-Type' => 'application/x-download',
                'Content-Description' => 'File Transfer',
                'Content-Disposition' => 'attachment; filename="John_Doe_2015-01-01_00-15-00.zip"; filename*=UTF-8\'\'John_Doe_2015-01-01_00-15-00.zip',
            ),
            Stream::factory(fopen(__DIR__ . '/Fixtures/workout-single.zip', 'r+'))
        );
        $clientMock = $this->getClientMock(array($response));
        $sportMapper = $this->getMock('SportTrackerConnector\Workout\Workout\SportMapperInterface');

        $polarMock = $this->getMock('SportTrackerConnector\Tracker\Polar\API', array('loginIntoPolar'), array($clientMock, null, null, $sportMapper));

        $actual = $polarMock->fetchWorkoutTCX($idWorkout);
        $expected = file_get_contents(__DIR__ . '/Fixtures/workout-single.tcx');
        $this->assertSame($expected, $actual);
    }

    /**
     * Get a Guzzle HTTP client with mocked responses.
     *
     * @param array $responses The responses for the client.
     * @return Client
     */
    private function getClientMock(array $responses)
    {
        $loginResponse = __DIR__ . '/Fixtures/loginIntoPolar.txt';
        array_unshift($responses, $loginResponse);

        $client = new Client();
        $mock = new Mock($responses);

        $client->getEmitter()->attach($mock);

        return $client;
    }
}
