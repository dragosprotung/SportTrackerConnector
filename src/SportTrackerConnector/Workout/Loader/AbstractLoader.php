<?php

namespace SportTrackerConnector\Workout\Loader;

use InvalidArgumentException;

/**
 * Abstract loader.
 */
abstract class AbstractLoader implements LoaderInterface
{

    /**
     * Get a workout from a file.
     *
     * @param string $file The path to the file to load.
     * @return \SportTrackerConnector\Workout\Workout;
     * @throws InvalidArgumentException If the file is not readable.
     */
    public function fromFile($file)
    {
        if (is_readable($file) !== true) {
            throw new InvalidArgumentException('File "' . $file . '" is not readable.');
        }

        return $this->fromString(file_get_contents($file));
    }
}