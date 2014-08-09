<?php

namespace SportTrackerConnector\Workout\Workout\Extension;

/**
 * Interface for all extension.
 */
interface ExtensionInterface
{
    /**
     * Get the ID of the extension.
     *
     * @return string
     */
    public function getID();

    /**
     * Get the name of the extension.
     *
     * @return string
     */
    public function getName();

    /**
     * Get the value for the extension.
     *
     * @return string|null
     */
    public function getValue();
}
