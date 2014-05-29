<?php

namespace FitnessTrackingPorting\Workout\Dumper;

use FitnessTrackingPorting\Workout\Workout;

/**
 * Interface for workout dumpers.
 */
interface DumperInterface
{
    /**
     * Dump a workout to string.
     *
     * @param Workout $workout The workout to dump.
     * @return string
     */
    public function dumpToString(Workout $workout);

    /**
     * Dump a workout to a file.
     *
     * @param Workout $workout The workout to dump.
     * @param string $outputFile The path to file where to dump the workout.
     * @param boolean $overwrite Flag if the file should be overwritten if it exists.
     * @return boolean
     */
    public function dumpToFile(Workout $workout, $outputFile, $overwrite = true);
} 