<?php

namespace SportTrackerConnector\Tests\Workout\Dumper\JSON;

use DateTime;
use SportTrackerConnector\Workout\Dumper\JSON;
use SportTrackerConnector\Workout\Workout;
use SportTrackerConnector\Workout\Workout\Author;
use SportTrackerConnector\Workout\Workout\Extension\HR;
use SportTrackerConnector\Workout\Workout\SportMapperInterface;
use SportTrackerConnector\Workout\Workout\Track;
use SportTrackerConnector\Workout\Workout\TrackPoint;

/**
 * Test for JSON dumper.
 */
class JSONTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test dumping a workout to a GPX string.
     */
    public function testDumpToStringSingleTrack()
    {
        $workout = new Workout();
        $workout->addTrack(
            new Track(
                array(
                    $this->getTrackPoint('53.551075', '9.993672', '2014-05-30T17:12:58+00:00', 11, null, 78),
                    $this->getTrackPoint('53.550085', '9.992682', '2014-05-30T17:12:59+00:00', 10, null, 88)
                ),
                SportMapperInterface::RUNNING
            )
        );
        $workout->setAuthor(
            new Author('John Doe')
        );

        $gpx = new JSON();
        $actual = $gpx->dumpToString($workout);

        $this->assertJsonStringEqualsJsonFile(__DIR__ . '/Expected/testDumpToStringSingleTrack.json', $actual);
    }

    /**
     * Test dumping a workout to a GPX string.
     */
    public function testDumpToStringMultiTrack()
    {
        $workout = new Workout();
        $workout->addTrack(
            new Track(
                array(
                    $this->getTrackPoint('53.551075', '9.993672', '2014-05-30T17:12:58+00:00', 11, 0, 78),
                    $this->getTrackPoint('53.550085', '9.992682', '2014-05-30T17:12:59+00:00', 10, 10, 88)
                ),
                SportMapperInterface::RUNNING
            )
        );
        $workout->addTrack(
            new Track(
                array(
                    $this->getTrackPoint('53.549075', '9.991672', '2014-05-30T17:13:00+00:00', 9, null, 98),
                    $this->getTrackPoint('53.548085', '9.990682', '2014-05-30T17:13:01+00:00', 8, null, 108)
                ),
                SportMapperInterface::SWIMMING
            )
        );
        $workout->setAuthor(
            new Author('John Doe')
        );

        $gpx = new JSON();
        $actual = $gpx->dumpToString($workout);

        $this->assertJsonStringEqualsJsonFile(__DIR__ . '/Expected/testDumpToStringMultiTrack.json', $actual);
    }

    /**
     * Get a track point.
     *
     * @param string $latitude The latitude.
     * @param string $longitude The longitude.
     * @param string $time The time.
     * @param float $distance The distance from start to that point.
     * @param integer $elevation The elevation.
     * @param integer $heartRate The heart rate.
     * @return TrackPoint
     */
    private function getTrackPoint($latitude, $longitude, $time, $elevation, $distance = null, $heartRate = null)
    {
        $trackPoint = new TrackPoint($latitude, $longitude, new DateTime($time));
        $trackPoint->setElevation($elevation);
        $trackPoint->setDistance($distance);
        if ($heartRate !== null) {
            $trackPoint->setExtensions(array(new HR($heartRate)));
        }

        return $trackPoint;
    }
}
