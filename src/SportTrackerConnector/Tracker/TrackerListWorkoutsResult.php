<?php

namespace SportTrackerConnector\Tracker;

use DateTime;

/**
 * One result item when fetching a list of workouts from a tracker.
 */
class TrackerListWorkoutsResult
{

    /**
     * The ID of the workout.
     *
     * @var integer
     */
    public $idWorkout;

    /**
     * The sport. One of the constants from SportMapperInterface.
     *
     * @var string
     */
    public $sport;

    /**
     * The start date time of the workout.
     *
     * @var \DateTime
     */
    public $startDateTime;

    /**
     * Constructor.
     *
     * @param integer $idWorkout The ID of the workout.
     * @param string $sport The sport. One of the constants from SportMapperInterface.
     * @param DateTime $startDateTime The start date time of the workout.
     */
    public function __construct($idWorkout, $sport, DateTime $startDateTime)
    {
        $this->idWorkout = $idWorkout;
        $this->sport = $sport;
        $this->startDateTime = $startDateTime;
    }
}
