<?php

namespace SportTrackerConnector\Tracker;

use DateTime;
use DateTimeZone;
use SportTrackerConnector\Workout\Workout;
use Psr\Log\LoggerInterface;

/**
 * Interface for trackers.
 */
interface TrackerInterface
{

    /**
     * Get a new instance using a config array.
     *
     * @param LoggerInterface $logger The logger.
     * @param array $config The config for the new instance.
     * @return TrackerInterface
     */
    public static function fromConfig(LoggerInterface $logger, array $config);

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
     * @return void
     */
    public function setTimeZone(DateTimeZone $timeZone);

    /**
     * Get the timezone of the tracker.
     *
     * @return DateTimeZone
     */
    public function getTimeZone();

    /**
     * Get a list of workouts.
     *
     * @param DateTime $startDate The start date for the workouts.
     * @param DateTime $endDate The end date for the workouts.
     * @return \SportTrackerConnector\Tracker\TrackerListWorkoutsResult[]
     */
    public function listWorkouts(DateTime $startDate, DateTime $endDate);

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
     * @return \SportTrackerConnector\Workout\Workout\SportMapperInterface
     */
    public function getSportMapper();

    /**
     * The a logger.
     *
     * @param LoggerInterface $logger The logger to set.
     * @return void
     */
    public function setLogger(LoggerInterface $logger);
}
