<?php

namespace SportTrackerConnector\Workout\Workout;

use InvalidArgumentException;

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
     * @throws InvalidArgumentException If the value is not a string or an object implementing the __toString() method.
     */
    public function setName($name)
    {
        if ($name !== null && (is_string($name)) || (is_object($name) && method_exists($name, '__toString'))) {
            $name = (string)$name;
        } elseif ($name !== null) {
            throw new InvalidArgumentException('The name of the author must be a string.');
        }

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