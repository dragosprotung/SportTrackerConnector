<?php

namespace FitnessTrackingPorting\Tests\Workout\Loader\GPX;

use FitnessTrackingPorting\Workout\Loader\GPX;
use FitnessTrackingPorting\Workout\Workout;
use FitnessTrackingPorting\Workout\Workout\Track;
use FitnessTrackingPorting\Workout\Workout\TrackPoint;
use FitnessTrackingPorting\Workout\Workout\SportInterface;
use FitnessTrackingPorting\Workout\Workout\Author;
use FitnessTrackingPorting\Workout\Workout\Extension\HR;
use DateTime;

class GPXTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test loading a workout from a GPX string with a single track.
     */
    public function testFromStringSingleTrack()
    {
        $expected = new Workout();
        $expected->addTrack(
            new Track(
                array(
                    $this->getTrackPoint('53.551075', '9.993672', '2014-05-30T17:12:58+00:00', 11, 78),
                    $this->getTrackPoint('53.550085', '9.992682', '2014-05-30T17:12:59+00:00', 10, 88)
                ),
                SportInterface::RUNNING
            )
        );
        $expected->setAuthor(
            new Author('John Doe')
        );

        $gpx = new GPX();
        $actual = $gpx->fromString(file_get_contents(__DIR__ . '/Fixtures/testFromStringSingleTrack.gpx'));

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test loading a workout from a GPX string with multiple tracks.
     */
    public function testFromStringMultiTrack()
    {
        $expected = new Workout();
        $expected->addTrack(
            new Track(
                array(
                    $this->getTrackPoint('53.551075', '9.993672', '2014-05-30T17:12:58+00:00', 11, 78),
                    $this->getTrackPoint('53.550085', '9.992682', '2014-05-30T17:12:59+00:00', 10, 88)
                ),
                SportInterface::RUNNING
            )
        );
        $expected->addTrack(
            new Track(
                array(
                    $this->getTrackPoint('53.549075', '9.991672', '2014-05-30T17:13:00+00:00', 9, 98),
                    $this->getTrackPoint('53.548085', '9.990682', '2014-05-30T17:13:01+00:00', 8, 108)
                ),
                SportInterface::SWIMMING
            )
        );
        $expected->setAuthor(
            new Author('John Doe')
        );

        $gpx = new GPX();
        $actual = $gpx->fromString(file_get_contents(__DIR__ . '/Fixtures/testFromStringMultiTrack.gpx'));

        $this->assertEquals($expected, $actual);
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
        $trackPoint->addExtension(new HR($hr));
        return $trackPoint;
    }
} 