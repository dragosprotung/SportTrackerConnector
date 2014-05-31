<?php

namespace FitnessTrackingPorting\Tests\Workout\Dumper\GPX;

use FitnessTrackingPorting\Workout\Dumper\GPX;
use FitnessTrackingPorting\Workout\Workout;
use FitnessTrackingPorting\Workout\Workout\Sport;
use FitnessTrackingPorting\Workout\Workout\TrackPoint;
use FitnessTrackingPorting\Workout\Workout\Author;
use FitnessTrackingPorting\Workout\Workout\Extension\HR;
use DateTime;

class GPXTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test dumping a workout to a GPX string.
     */
    public function testDumpToString()
    {
        $workout = new Workout();
        $workout->setSport(Sport::RUNNING);
        $workout->setTrackPoints(
            array(
                $this->getTrackPoint('53.551075', '9.993672', '2014-05-30T17:12:58+00:00', 11, 78),
                $this->getTrackPoint('53.550085', '9.992682', '2014-05-30T17:12:59+00:00', 10, 88)
            )
        );
        $workout->setAuthor(
            new Author('John Doe')
        );

        $gpx = new GPX();
        $actual = $gpx->dumpToString($workout);

        $this->assertXmlStringEqualsXmlFile(__DIR__ . '/Expected/testDumpToString.gpx', $actual);
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