<?php

namespace SportTrackerConnector\Tests\Workout\Loader\TCX;

use DateTime;
use SportTrackerConnector\Workout\Loader\TCX;
use SportTrackerConnector\Workout\Workout;
use SportTrackerConnector\Workout\Workout\Extension\HR;
use SportTrackerConnector\Workout\Workout\SportMapperInterface;
use SportTrackerConnector\Workout\Workout\Track;
use SportTrackerConnector\Workout\Workout\TrackPoint;

/**
 * Test the TCX file loader.
 */
class TCXTest extends \PHPUnit_Framework_TestCase
{

    public function testElevationIsRoundedWith2Decimals()
    {
        $expected = new Workout();
        $expected->addTrack(
            new Track(
                array(
                    $this->getTrackPoint('53.551075', '9.993672', '2014-05-30T17:12:58+00:00', 11.23, 0, 78),
                    $this->getTrackPoint('53.550085', '9.992682', '2014-05-30T17:12:59+00:00', 10.55, 50, 88)
                ),
                SportMapperInterface::RUNNING
            )
        );

        $tcx = new TCX();
        $actual = $tcx->fromString(file_get_contents(__DIR__ . '/Fixtures/testElevationIsRoundedWith2Decimals.tcx'));

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test loading a workout from a TCX string with a single activity.
     */
    public function testFromStringSingleActivity()
    {
        $expected = new Workout();
        $expected->addTrack(
            new Track(
                array(
                    $this->getTrackPoint('53.551075', '9.993672', '2014-05-30T17:12:58+00:00', 11, 200, 78),
                    $this->getTrackPoint('53.550085', '9.992682', '2014-05-30T17:12:59+00:00', 10, null, 88)
                ),
                SportMapperInterface::RUNNING
            )
        );

        $tcx = new TCX();
        $actual = $tcx->fromString(file_get_contents(__DIR__ . '/Fixtures/testFromStringSingleActivity.tcx'));

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test loading a workout from a string with multiple activities.
     */
    public function testFromStringMultiActivity()
    {
        $expected = new Workout();
        $expected->addTrack(
            new Track(
                array(
                    $this->getTrackPoint('53.551075', '9.993672', '2014-05-30T17:12:58+00:00', 11, 0, 78),
                    $this->getTrackPoint('53.550085', '9.992682', '2014-05-30T17:12:59+00:00', 10, 128, 88)
                ),
                SportMapperInterface::RUNNING
            )
        );
        $expected->addTrack(
            new Track(
                array(
                    $this->getTrackPoint('53.549075', '9.991672', '2014-05-30T17:13:00+00:00', 9, null, 98),
                    $this->getTrackPoint('53.548085', '9.990682', '2014-05-30T17:13:01+00:00', 8, 258, 108)
                ),
                SportMapperInterface::SWIMMING
            )
        );

        $tcx = new TCX();
        $actual = $tcx->fromString(file_get_contents(__DIR__ . '/Fixtures/testFromStringMultiActivity.tcx'));

        $this->assertEquals($expected, $actual);
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
