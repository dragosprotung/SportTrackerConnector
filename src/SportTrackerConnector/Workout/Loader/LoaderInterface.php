<?php

namespace SportTrackerConnector\Workout\Loader;

/**
 * Interface for workout loaders.
 */
interface LoaderInterface
{
    /**
     * Get a workout from a string.
     *
     * @param string $gpx The data.
     * @return \SportTrackerConnector\Workout\Workout;
     */
    public function fromString($gpx);

    /**
     * Get a workout from a file.
     *
     * @param string $file The path to the file to load.
     * @return \SportTrackerConnector\Workout\Workout;
     */
    public function fromFile($file);
}