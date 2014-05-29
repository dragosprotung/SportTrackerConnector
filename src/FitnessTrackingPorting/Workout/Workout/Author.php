<?php

namespace FitnessTrackingPorting\Workout\Workout;

/**
 * Author of a workout.
 */
class Author
{

    /**
     * The name.
     *
     * @var string
     */
    protected $name;

    /**
     * Constructor.
     *
     * @param string $name The name of the author.
     */
    public function __construct($name = null)
    {
        $this->setName($name);
    }

    /**
     * Set the name of the author.
     *
     * @param string $name The name.
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get the name of the author.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
} 