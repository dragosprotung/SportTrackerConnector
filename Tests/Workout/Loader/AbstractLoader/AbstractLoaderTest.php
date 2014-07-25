<?php

namespace SportTrackerConnector\Tests\Workout\Loader\AbstractLoader;

use org\bovigo\vfs\vfsStream;

/**
 * Test for \SportTrackerConnector\Workout\Loader\AbstractLoader
 */
class AbstractLoaderTest extends \PHPUnit_Framework_TestCase
{

    /**
     * The root folder of vfsStream for testing.
     *
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * Set up test environment.
     */
    public function setUp()
    {
        $this->root = vfsStream::setup();
    }

    /**
     * Test load from file throws exception if file does not exists.
     */
    public function testLoadFromFileThrowsExceptionIfFileDoesNotExists()
    {
        $mock = $this->getMockForAbstractClass('SportTrackerConnector\Workout\Loader\AbstractLoader');

        $file = vfsStream::url('root/workout.tst');

        $this->setExpectedException('InvalidArgumentException', 'File "vfs://root/workout.tst" is not readable.');

        $mock->fromFile($file);
    }

    /**
     * Test load from file throws exception if file does exists but is not readable.
     */
    public function testLoadFromFileThrowsExceptionIfFileDoesExistsButIsNotReadable()
    {
        $mock = $this->getMockForAbstractClass('SportTrackerConnector\Workout\Loader\AbstractLoader');

        $file = vfsStream::url('root/workout.tst');
        touch($file);
        chmod($file, 000);

        $this->setExpectedException('InvalidArgumentException', 'File "vfs://root/workout.tst" is not readable.');

        $mock->fromFile($file);
    }

    /**
     * Test that load from file will call load from string.
     */
    public function testDumpToFileCallsDumpToString()
    {
        $mock = $this->getMockBuilder('SportTrackerConnector\Workout\Loader\AbstractLoader')
            ->setMethods(array('fromString'))
            ->getMockForAbstractClass();

        $fileMock = vfsStream::url('root/workout.tst');
        file_put_contents($fileMock, 'workout data');

        $mock->expects($this->once())->method('fromString')->with('workout data');

        $mock->fromFile($fileMock);
    }
}