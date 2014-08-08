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

    /**
     * Test loading a workout from a TCX string with a single activity.
     */
    public function testFromStringSingleActivity()
    {
        $expected = new Workout();
        $expected->addTrack(
            new Track(
                array(
                    $this->getTrackPoint('53.551075', '9.993672', '2014-05-30T17:12:58+00:00', 11, 78),
                    $this->getTrackPoint('53.550085', '9.992682', '2014-05-30T17:12:59+00:00', 10, 88)
                ),
                SportMapperInterface::RUNNING
            )
        );

        $tcx = new TCX();
        $actual = $tcx->fromString(file_get_contents(__DIR__ . '/Fixtures/testFromStringSingleActivity.tcx'));

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test loading a workout from a D:\Web\Sites\SportTrackerConnector\Tests\Workout\Loader\TCX\TCXTest.php string with multiple activities.
     */
    public function testFromStringMultiActivity()
    {
        $expected = new Workout();
        $expected->addTrack(
            new Track(
                array(
                    $this->getTrackPoint('53.551075', '9.993672', '2014-05-30T17:12:58+00:00', 11, 78),
                    $this->getTrackPoint('53.550085', '9.992682', '2014-05-30T17:12:59+00:00', 10, 88)
                ),
                SportMapperInterface::RUNNING
            )
        );
        $expected->addTrack(
            new Track(
                array(
                    $this->getTrackPoint('53.549075', '9.991672', '2014-05-30T17:13:00+00:00', 9, 98),
                    $this->getTrackPoint('53.548085', '9.990682', '2014-05-30T17:13:01+00:00', 8, 108)
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
