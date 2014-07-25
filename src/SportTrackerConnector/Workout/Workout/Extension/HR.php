<?php

namespace SportTrackerConnector\Workout\Workout\Extension;

use InvalidArgumentException;

/**
 * Heart rate extension.
 */
class HR extends AbstractExtension
{

    const ID = 'HR';

    /**
     * Name fot the extension.
     *
     * @var string
     */
    protected $name = 'Heart rate';

    /**
     * Set the value for the HR extension.
     *
     * @param integer $value The value to set.
     * @throws InvalidArgumentException If the value is invalid.
     */
    public function setValue($value)
    {
        if ($value !== null && (!is_int($value) || $value < 0 || $value > 230)) {
            throw new InvalidArgumentException('The value for the HR must be an integer and between 0 and 230.');
        }

        parent::setValue($value);
    }
}