<?php

namespace FitnessTrackingPorting\Tests\Tracker\Endomondo\Endomondo;

use GuzzleHttp\Client;

/**
 * Endomondo test.
 */
class EndomondoTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test upload a workout.
     */
    public function testUploadWorkout()
    {
        $workoutMock = $this->getMock('FitnessTrackingPorting\Workout\Workout');
        $workoutID = 123;

        $endomondoAPI = $this->getEndomondoAPIMock(array('postWorkout'));
        $endomondoAPI->expects($this->once())->method('postWorkout')->with($workoutMock)->willReturn($workoutID);

        $endomondo = $this->getMock('FitnessTrackingPorting\Tracker\Endomondo\Endomondo', array('getEndomondoAPI'));
        $endomondo->expects($this->once())->method('getEndomondoAPI')->willReturn($endomondoAPI);

        $actual = $endomondo->uploadWorkout($workoutMock);
        $this->assertTrue($actual);
    }

    /**
     * Get an EndomondoAPI mock.
     *
     * @param array $mockMethods The methods to mock.
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getEndomondoAPIMock($mockMethods = array())
    {
        $client = new Client();
        $sportMapper = $this->getMock('FitnessTrackingPorting\Workout\Workout\SportMapperInterface');
        $endomondoAPI = $this->getMock('FitnessTrackingPorting\Tracker\Endomondo\EndomondoAPI', $mockMethods, array($client, null, null, $sportMapper));

        return $endomondoAPI;
    }
}