<?php

namespace FitnessTrackingPorting\Tests\Workout\Workout\Workout\Author;

use FitnessTrackingPorting\Workout\Workout\Author;

/**
 * Test for \FitnessTrackingPorting\Workout\Workout\Author.
 */
class AuthorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Data provider for testSetGetNameValid();
     *
     * @return array
     */
    public function dataProviderTestSetGetNameValid()
    {
        return array(
            array(null),
            array('100'),
            array('John Doe'),
            array(new TestSetGetNameInvalidToString('My author'))
        );
    }

    /**
     * Test setting the name of the author with valid values.
     *
     * @dataProvider dataProviderTestSetGetNameValid
     * @param mixed $name The name to set.
     */
    public function testSetGetNameValid($name)
    {
        $author = new Author($name);
        $this->assertEquals($name, $author->getName());
    }

    /**
     * Data provider for testSetGetNameValid();
     *
     * @return array
     */
    public function dataProviderTestSetGetNameInvalid()
    {
        return array(
            array(array()),
            array(new \stdClass())
        );
    }

    /**
     * Test setting the name of the author with invalid values.
     *
     * @dataProvider dataProviderTestSetGetNameInvalid
     * @param mixed $name The name to set.
     */
    public function testSetGetNameInvalid($name)
    {
        $this->setExpectedException('InvalidArgumentException', 'The name of the author must be a string.');
        new Author($name);
    }
}

/**
 * Class that implements __toString for testing setting the author name.
 */
class TestSetGetNameInvalidToString
{

    public function __toString()
    {
        return 'john Doe';
    }
}