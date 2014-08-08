<?php

namespace SportTrackerConnector\Tests\Workout\Dumper\JSON;

use SportTrackerConnector\Workout\Dumper\JSON;
use SportTrackerConnector\Workout\Workout;
use SportTrackerConnector\Workout\Workout\SportMapperInterface;
use SportTrackerConnector\Workout\Workout\Track;
use SportTrackerConnector\Workout\Workout\TrackPoint;
use SportTrackerConnector\Workout\Workout\Author;
use SportTrackerConnector\Workout\Workout\Extension\HR;
use DateTime;

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
                    $this->getTrackPoint('53.551075', '9.993672', '2014-05-30T17:12:58+00:00', 11, 78),
                    $this->getTrackPoint('53.550085', '9.992682', '2014-05-30T17:12:59+00:00', 10, 88)
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
                    $this->getTrackPoint('53.551075', '9.993672', '2014-05-30T17:12:58+00:00', 11, 78),
                    $this->getTrackPoint('53.550085', '9.992682', '2014-05-30T17:12:59+00:00', 10, 88)
                ),
                SportMapperInterface::RUNNING
            )
        );
        $workout->addTrack(
            new Track(
                array(
                    $this->getTrackPoint('53.549075', '9.991672', '2014-05-30T17:13:00+00:00', 9, 98),
                    $this->getTrackPoint('53.548085', '9.990682', '2014-05-30T17:13:01+00:00', 8, 108)
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
     * @param string $lat The latitude.
     * @param string $lon The longitude.
     * @param string $time The time.
     * @param integer $ele The elevation.
     * @param integer $hr The heart rate.
     * @return TrackPoint
     */
    private function getTrackPoint($lat, $lon, $time, $ele, $hr)
    {
        $trackPoint = new TrackPoint($lat, $lon, new DateTime($time));
        $trackPoint->setElevation($ele);
        $extensions = array(
            new HR($hr)
        );
        $trackPoint->setExtensions($extensions);
        return $trackPoint;
    }
}
