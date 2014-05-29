<?php

namespace FitnessTrackingPorting\Workout\Workout\Extension;

/**
 * Abstract extension.
 */
class AbstractExtension implements ExtensionInterface
{

    /**
     * Name fot the extension.
     *
     * @var string
     */
    protected $name;

    /**
     * Value of the extension.
     *
     * @var mixed
     */
    protected $value;

    /**
     * Constructor.
     *
     * @param mixed $value The value for the extension.
     */
    public function __construct($value = null)
    {
        $this->setValue($value);
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Get the value for the extension.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get the name of the extension.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}