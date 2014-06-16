<?php

namespace FitnessTrackingPorting\Tracker;

use FitnessTrackingPorting\Workout\Workout;
use DateTimeZone;

/**
 * Interface for trackers.
 */
interface TrackerInterface
{

    /**
     * Get a new instance using a config array.
     *
     * @param array $config The config for the new instance.
     * @return TrackerInterface
     */
    public static function fromConfig(array $config);

    /**
     * Get the ID of the tracker.
     *
     * @return string
     */
    public static function getID();

    /**
     * Set the timezone of the tracker.
     *
     * @param DateTimeZone $timeZone The timezone.
     */
    public function setTimeZone(DateTimeZone $timeZone);

    /**
     * Get the timezone of the tracker.
     *
     * @return DateTimeZone
     */
    public function getTimeZone();

    /**
     * Upload a workout.
     *
     * @param Workout $workout The workout to upload.
     * @return boolean
     */
    public function uploadWorkout(Workout $workout);

    /**
     * Download a workout.
     *
     * @param integer $idWorkout The ID of the workout to download.
     * @return Workout
     */
    public function downloadWorkout($idWorkout);

    /**
     * Get the sport mapper.
     *
     * @return \FitnessTrackingPorting\Workout\Workout\SportMapperInterface
     */
    public function getSportMapper();
}