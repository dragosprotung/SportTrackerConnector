<?php

namespace Tracker\Polar\API;

use GuzzleHttp\Client;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Subscriber\Mock;
use SportTrackerConnector\Core\Workout\Workout;

/**
 * Test the Polar API.
 */
class APITest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test fetching a workout CSV from Polar.
     */
    public function testFetchWorkoutCSV()
    {
        $idWorkout = 1;

        $response = new Response(
            200,
            array(
                'Content-Type' => 'application/x-download',
                'Content-Description' => 'File Transfer',
                'Content-Disposition' => 'attachment; filename="John_Doe_2015-01-01_00-10-00.csv"; filename*=UTF-8\'\'John_Doe_2015-01-01_00-15-00.csv',
            ),
            new Stream(fopen(__DIR__ . '/Fixtures/workout-single.csv', 'r+'))
        );
        $clientMock = $this->getClientMock(array($response));
        $sportMapper = $this->getMock('SportTrackerConnector\Core\Workout\SportMapperInterface');

        $polarMock = $this->getMock('SportTrackerConnector\Tracker\Polar\API', array('loginIntoPolar'), array($clientMock, null, null, $sportMapper));

        $actual = $polarMock->fetchWorkoutCSV($idWorkout);
        $expected = file_get_contents(__DIR__ . '/Fixtures/workout-single.csv');
        $this->assertSame($expected, $actual);
    }

    /**
     * Test fetching a workout TCX from Polar.
     */
    public function testFetchWorkoutTCX()
    {
        $idWorkout = 1;

        $response = new Response(
            200,
            array(
                'Content-Type' => 'application/x-download',
                'Content-Description' => 'File Transfer',
                'Content-Disposition' => 'attachment; filename="John_Doe_2015-01-01_00-10-00.tcx"; filename*=UTF-8\'\'John_Doe_2015-01-01_00-15-00.tcx',
            ),
            new Stream(fopen(__DIR__ . '/Fixtures/workout-single.tcx', 'r+'))
        );
        $clientMock = $this->getClientMock(array($response));
        $sportMapper = $this->getMock('SportTrackerConnector\Core\Workout\SportMapperInterface');

        $polarMock = $this->getMock('SportTrackerConnector\Tracker\Polar\API', array('loginIntoPolar'), array($clientMock, null, null, $sportMapper));

        $actual = $polarMock->fetchWorkoutTCX($idWorkout);
        $expected = file_get_contents(__DIR__ . '/Fixtures/workout-single.tcx');
        $this->assertSame($expected, $actual);
    }

    /**
     * Test fetching a list of calendar events returns an array of calendar entries.
     */
    public function testListCalendarEventsReturnsArrayOfCalendarEntries()
    {
        $startDate = new \DateTime('yesterday');
        $endDate = new \DateTime('today');

        $clientMock = $this->getClientMock(array(__DIR__ . '/Fixtures/testListCalendarEventsReturnsArrayOfCalendarEntries.txt'));
        $sportMapper = $this->getMock('SportTrackerConnector\Core\Workout\SportMapperInterface');

        $polarMock = $this->getMock('SportTrackerConnector\Tracker\Polar\API', array('loginIntoPolar'), array($clientMock, null, null, $sportMapper));
        $actual = $polarMock->listCalendarEvents($startDate, $endDate);

        $this->assertJsonStringEqualsJsonFile(__DIR__ . '/Expected/testListCalendarEventsReturnsArrayOfCalendarEntries.json',
            json_encode($actual, JSON_PRETTY_PRINT));
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
