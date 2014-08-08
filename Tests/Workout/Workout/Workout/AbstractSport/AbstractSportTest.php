<?php

namespace SportTrackerConnector\Tests\Workout\Workout\Workout\AbstractSport;

use SportTrackerConnector\Workout\Workout\SportMapperInterface;

/**
 * Test for \SportTrackerConnector\Workout\Workout\AbstractSport.
 */
class AbstractSportTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test get sport from code returns correct sport.
     */
    public function testGetSportFromCodeReturnsCorrectSport()
    {
        $mock = $this->getMockBuilder('SportTrackerConnector\Workout\Workout\AbstractSportMapper')
            ->setMethods(array('getMap'))
            ->getMockForAbstractClass();
        $mock->expects($this->once())->method('getMap')->willReturn(
            array(SportMapperInterface::SWIMMING => 'swim_sport', SportMapperInterface::GOLF => 'golf')
        );
        $actual = $mock->getSportFromCode('swim_sport');
        $this->assertSame(SportMapperInterface::SWIMMING, $actual);
    }

    /**
     * Test get sport from code returns other sport if code not defined.
     */
    public function testGetSportFromCodeReturnsOtherSportIfCodeNotDefined()
    {
        $mock = $this->getMockBuilder('SportTrackerConnector\Workout\Workout\AbstractSportMapper')
            ->setMethods(array('getMap'))
            ->getMockForAbstractClass();
        $mock->expects($this->once())->method('getMap')->willReturn(array());
        $actual = $mock->getSportFromCode('unexisting_code');
        $this->assertSame(SportMapperInterface::OTHER, $actual);
    }

    /**
     * Test get code from sport returns NULL if sport is not associated.
     */
    public function testGetCodeFromSportReturnsNULLIfSportIsNotAssociated()
    {
        $mock = $this->getMockBuilder('SportTrackerConnector\Workout\Workout\AbstractSportMapper')
            ->setMethods(array('getMap'))
            ->getMockForAbstractClass();
        $mock->expects($this->once())->method('getMap')->willReturn(array());
        $actual = $mock->getCodeFromSport(SportMapperInterface::SWIMMING);
        $this->assertNull($actual);
    }

    /**
     * Test get code from sport returns other sport if original sport not defined but other is defined.
     */
    public function testGetCodeFromSportReturnsOtherSportIfOriginalSportNotDefinedButOtherIsDefined()
    {
        $mock = $this->getMockBuilder('SportTrackerConnector\Workout\Workout\AbstractSportMapper')
            ->setMethods(array('getMap'))
            ->getMockForAbstractClass();
        $mock->expects($this->once())->method('getMap')->willReturn(
            array(SportMapperInterface::SWIMMING => 'swim_sport', SportMapperInterface::GOLF => 'golf', SportMapperInterface::OTHER => 'other_sport')
        );
        $actual = $mock->getCodeFromSport(SportMapperInterface::RUNNING);
        $this->assertSame('other_sport', $actual);
    }

    /**
     * Test get code from sport returns correct sport.
     */
    public function testGetCodeFromSportReturnsCorrectSport()
    {
        $mock = $this->getMockBuilder('SportTrackerConnector\Workout\Workout\AbstractSportMapper')
            ->setMethods(array('getMap'))
            ->getMockForAbstractClass();
        $mock->expects($this->once())->method('getMap')->willReturn(
            array(
                SportMapperInterface::SWIMMING => 'swim_sport',
                SportMapperInterface::RUNNING => 'running_hard',
                SportMapperInterface::OTHER => 'other_sport'
            )
        );
        $actual = $mock->getCodeFromSport(SportMapperInterface::RUNNING);
        $this->assertSame('running_hard', $actual);
    }
}
