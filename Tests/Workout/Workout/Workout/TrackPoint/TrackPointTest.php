<?php

namespace SportTrackerConnector\Tests\Workout\Workout\Workout\TrackPoint;

use DateTime;
use SportTrackerConnector\Workout\Workout\TrackPoint;

/**
 * Test for a workout track point.
 */
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
     * Data provider for testSpeedForPointsWithoutDistance().
     *
     * @return array
     */
    public function dataProviderTestSpeedForPointsWithoutDistance()
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
     * Test speed calculation between 2 points that do not have the distance set.
     *
     * @dataProvider dataProviderTestSpeedForPointsWithoutDistance
     * @param TrackPoint $destinationTrackPoint The destination track point.
     * @param float $expected The expected speed.
     */
    public function testSpeedForPointsWithoutDistance(TrackPoint $destinationTrackPoint, $expected)
    {
        $startPoint = new TrackPoint('-38.691450', '176.079795', new DateTime('2014-06-01 00:00:00'));

        $actual = $startPoint->speed($destinationTrackPoint);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test speed calculation where the start point has a distance but destination point does not.
     */
    public function testSpeedForPointsWhereStartPointHasDistanceAndDestinationDoesNot()
    {
        $startPoint = new TrackPoint('-38.691450', '176.079795', new DateTime('2014-06-01 00:00:00'));
        $startPoint->setDistance(1000);
        $destinationTrackPoint = new TrackPoint('-38.6914501', '176.0797951', new DateTime('2014-06-01 00:05:00'));

        $actual = $startPoint->speed($destinationTrackPoint);
        $this->assertEquals(0.00016926749741346, $actual);
    }

    /**
     * Test speed calculation where the start point does not have a distance but destination point has.
     */
    public function testSpeedForPointsWhereStartPointDoesNotHaveDistanceAndDestinationHas()
    {
        $startPoint = new TrackPoint('-38.691450', '176.079795', new DateTime('2014-06-01 00:00:00'));
        $destinationTrackPoint = new TrackPoint('-38.6914501', '176.0797951', new DateTime('2014-06-01 00:05:00'));
        $destinationTrackPoint->setDistance(1000);

        $actual = $startPoint->speed($destinationTrackPoint);
        $this->assertEquals(0.00016926749741346, $actual);
    }

    /**
     * Test speed calculation where the start point and destination point have a distance.
     */
    public function testSpeedForPointsWhereStartAndDestinationPointsHaveDistance()
    {
        $startPoint = new TrackPoint('-38.691450', '176.079795', new DateTime('2014-06-01 00:00:00'));
        $startPoint->setDistance(250);
        $destinationTrackPoint = new TrackPoint('-38.6914501', '176.0797951', new DateTime('2014-06-01 00:01:01'));
        $destinationTrackPoint->setDistance(1000);

        $actual = $startPoint->speed($destinationTrackPoint);
        $this->assertEquals(14.6950819672131149, $actual);
    }

    /**
     * Test get extension throws exception if extension not found.
     */
    public function testGetExtensionThrowsExceptionIfExtensionNotFound()
    {
        $trackPoint = new TrackPoint(null, null, new DateTime());

        $name = 'non-existing-extension';
        $this->setExpectedException('OutOfBoundsException', 'Extension "' . $name . '" not found.');
        $trackPoint->getExtension($name);
    }

    /**
     * Test get extension success.
     */
    public function testGetExtensionSuccess()
    {
        $trackPoint = new TrackPoint(null, null, new DateTime());

        $idExtension = 'existing-extension';
        $extensionMock = $this->getExtensionMock($idExtension);
        $trackPoint->addExtension($extensionMock);

        $this->assertSame($extensionMock, $trackPoint->getExtension($idExtension));
    }

    /**
     * Test setting extensions.
     */
    public function testSetExtensions()
    {
        $trackPoint = new TrackPoint(null, null, new DateTime());

        $em1 = $this->getExtensionMock('e1');
        $em2 = $this->getExtensionMock('e2');
        $em3 = $this->getExtensionMock('e3');
        $extensions = array($em1, $em2, $em3);

        $trackPoint->setExtensions($extensions);

        $this->assertEquals($extensions, array_values($trackPoint->getExtensions()));
        $this->assertCount(3, $trackPoint->getExtensions());
        $this->assertSame($em1, $trackPoint->getExtension('e1'));
        $this->assertSame($em2, $trackPoint->getExtension('e2'));
        $this->assertSame($em3, $trackPoint->getExtension('e3'));
    }

    /**
     * Data provider for testSetGetElevationSuccess().
     *
     * @return array
     */
    public function dataProviderTestSetGetElevationSuccess()
    {
        return array(
            array(null),
            array(-12),
            array(-12.5),
            array(0),
            array(10),
            array(10.258),
            array(99999),
            array('-45'),
            array('45.58'),
        );
    }

    /**
     * Test setting the elevation with valid values.
     *
     * @dataProvider dataProviderTestSetGetElevationSuccess
     * @param number $elevation The elevation to set.
     */
    public function testSetGetElevationSuccess($elevation)
    {
        $trackPoint = new TrackPoint(null, null, new DateTime());

        $trackPoint->setElevation($elevation);
        $this->assertSame($elevation, $trackPoint->getElevation());
    }

    /**
     * Data provider for testSetGetElevationError().
     *
     * @return array
     */
    public function dataProviderTestSetGetElevationError()
    {
        return array(
            array(array()),
            array('ele'),
            array(new \stdClass())
        );
    }

    /**
     * Test setting the elevation with invalid values.
     *
     * @dataProvider dataProviderTestSetGetElevationError
     * @param number $elevation The elevation to set.
     */
    public function testSetGetElevationError($elevation)
    {
        $trackPoint = new TrackPoint(null, null, new DateTime());

        $this->setExpectedException('InvalidArgumentException', 'Elevation for a tracking point must be a number.');
        $trackPoint->setElevation($elevation);
    }

    /**
     * Get a mock of an extension.
     *
     * @param string $extensionID The ID of the extension.
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getExtensionMock($extensionID)
    {
        $extensionMock = $this->getMockBuilder('SportTrackerConnector\Workout\Workout\Extension\ExtensionInterface')
            ->setMethods(array('getID'))
            ->getMockForAbstractClass();

        $extensionMock->expects($this->any())->method('getID')->willReturn($extensionID);

        return $extensionMock;
    }
}
