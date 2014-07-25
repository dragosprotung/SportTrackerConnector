<?php

namespace SportTrackerConnector\Tests\Workout\Dumper\AbstractDumper;

use org\bovigo\vfs\vfsStream;

/**
 * Test for \SportTrackerConnector\Workout\Dumper\AbstractDumper.
 */
class AbstractDumperTest extends \PHPUnit_Framework_TestCase
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
     * Test dump to file throws exception if file does not exist and the directory is not writable.
     */
    public function testDumpToFileThrowsExceptionIfFileDoesNotExistAndTheDirectoryIsNotWritable()
    {
        $mock = $this->getMockForAbstractClass('SportTrackerConnector\Workout\Dumper\AbstractDumper');

        $this->root->chmod(000);
        $file = vfsStream::url('root/workout.tst');

        $workoutMock = $this->getMock('SportTrackerConnector\Workout\Workout');

        $this->setExpectedException('InvalidArgumentException', 'Directory for output file "vfs://root/workout.tst" is not writable.');

        $mock->dumpToFile($workoutMock, $file);
    }

    /**
     * Test dump to file throws exception if file is not writable.
     */
    public function testDumpToFileThrowsExceptionIfFileIsNotWritable()
    {
        $mock = $this->getMockForAbstractClass('SportTrackerConnector\Workout\Dumper\AbstractDumper');

        $file = vfsStream::url('root/workout.tst');
        touch($file);
        chmod($file, 000);

        $workoutMock = $this->getMock('SportTrackerConnector\Workout\Workout');

        $this->setExpectedException('InvalidArgumentException', 'The output file "vfs://root/workout.tst" is not writable.');

        $mock->dumpToFile($workoutMock, $file);
    }

    /**
     * Test that dump to file will call dump to string.
     */
    public function testDumpToFileCallsDumpToString()
    {
        $mock = $this->getMockBuilder('SportTrackerConnector\Workout\Dumper\AbstractDumper')
            ->setMethods(array('dumpToString'))
            ->getMockForAbstractClass();

        $workoutMock = $this->getMock('SportTrackerConnector\Workout\Workout');
        $fileMock = vfsStream::url('root/workout.tst');

        $mock->expects($this->once())->method('dumpToString')->with($workoutMock)->will($this->returnValue('dumped content'));

        $mock->dumpToFile($workoutMock, $fileMock);

        $this->assertFileExists($fileMock);
        $this->assertSame('dumped content', file_get_contents($fileMock));
    }
}