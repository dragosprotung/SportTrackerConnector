<?php

namespace FitnessTrackingPorting\Tests\Workout\Workout\Workout\Workout;

use FitnessTrackingPorting\Workout\Workout\TrackPoint;
use DateTime;

class TrackPointTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Data provider for testDistance().
     *
     * @return array
     */
    public function dataProviderTestDistance()
    {
        return array(
            array(new TrackPoint('-38.691450', '176.079795', new DateTime('2014-06-01 00:00:0')), 0),
            array(new TrackPoint('-38.714081', '176.084209', new DateTime()), 2545.4362815482),
            array(new TrackPoint('-38.723081', '176.079209', new DateTime()), 3517.5742562858),
            array(new TrackPoint('-38.6914501', '176.0797951', new DateTime()), 0.014105624784455),
            array(new TrackPoint('0', '0', new DateTime()), 15694215.397435, 0.000001)
        );
    }

    /**
     * Test distance calculation between 2 points.
     *
     * @dataProvider dataProviderTestDistance
     * @param TrackPoint $destinationTrackPoint The destination track point.
     * @param float $expected The expected distance.
     * @param float $delta The allowed numerical distance between two values to consider them equal
     */
    public function testDistance(TrackPoint $destinationTrackPoint, $expected, $delta = 0.0)
    {
        $starPoint = new TrackPoint('-38.691450', '176.079795', new DateTime());

        $actual = $starPoint->distance($destinationTrackPoint);
        $this->assertEquals($expected, $actual, '', $delta);
    }

    /**
     * Data provider for testSpeed().
     *
     * @return array
     */
    public function dataProviderTestSpeed()
    {
        return array(
            array(new TrackPoint('-38.691450', '176.079795', new DateTime('2014-06-01 00:00:00')), 0),
            array(new TrackPoint('-38.691450', '176.079795', new DateTime('2014-06-01 00:00:01')), 0),
            array(new TrackPoint('-38.714081', '176.084209', new DateTime('2014-06-01 00:00:10')), 916.35706135735),
            array(new TrackPoint('-38.723081', '176.079209', new DateTime('2014-06-01 00:01:00')), 211.05445537715),
            array(new TrackPoint('-38.723081', '176.079209', new DateTime('2014-06-01 00:02:00')), 105.52722768857),
            array(new TrackPoint('-38.6914501', '176.0797951', new DateTime('2014-06-01 00:05:00')), 0.00016926749741346),
            array(new TrackPoint('0', '0', new DateTime('2014-06-01 22:00:00')), 713.37342715616)
        );
    }

    /**
     * Test speed calculation between 2 points.
     *
     * @dataProvider dataProviderTestSpeed
     * @param TrackPoint $destinationTrackPoint The destination track point.
     * @param float $expected The expected speed.
     */
    public function testSpeed(TrackPoint $destinationTrackPoint, $expected)
    {
        $starPoint = new TrackPoint('-38.691450', '176.079795', new DateTime('2014-06-01 00:00:00'));

        $actual = $starPoint->speed($destinationTrackPoint);
        $this->assertEquals($expected, $actual);
    }
} 