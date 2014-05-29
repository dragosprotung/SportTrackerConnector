<?php

namespace FitnessTrackingPorting\Workout\Dumper;

use FitnessTrackingPorting\Workout\Workout;
use InvalidArgumentException;

abstract class AbstractDumper implements DumperInterface
{
    /**
     * Dump a workout to a file.
     *
     * @param Workout $workout The workout to dump.
     * @param string $outputFile The path to file where to dump the workout.
     * @param boolean $overwrite Flag if the file should be overwritten if it exists.
     * @return boolean
     * @throws InvalidArgumentException If not possible to write to output file.
     */
    public function dumpToFile(Workout $workout, $outputFile, $overwrite = true)
    {
        if (file_exists($outputFile) !== true && is_writable(dirname($outputFile)) !== true) {
            throw new InvalidArgumentException('Directory for output file "' . $outputFile . '" is not writable.');
        } elseif ($overwrite === true && file_exists($outputFile) && is_writable($outputFile) !== true) {
            throw new InvalidArgumentException('The output file "' . $outputFile . '" is not writable.');
        }

        return file_put_contents($outputFile, $this->dumpToString($workout));
    }
}