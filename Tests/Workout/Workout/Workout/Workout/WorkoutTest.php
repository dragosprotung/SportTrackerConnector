<?php

namespace FitnessTrackingPorting\Tests\Workout\Workout\Workout\Workout;

use FitnessTrackingPorting\Workout\Workout;
use FitnessTrackingPorting\Workout\Workout\Author;

class WorkoutTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test setting/getting the author.
     */
    public function testSetGetAuthor() {
        $workout = new Workout();

        $this->assertNull($workout->getAuthor());

        $author = new Author();
        $workout->setAuthor($author);
        $this->assertSame($author, $workout->getAuthor());
    }
} 