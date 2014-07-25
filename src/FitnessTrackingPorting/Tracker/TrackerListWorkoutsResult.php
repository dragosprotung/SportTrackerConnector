<?php

namespace FitnessTrackingPorting\Tracker;

use DateTime;

class TrackerListWorkoutsResult
{

    /**
     * The ID of the workout.
     *
     * @var integer
     */
    public $id;

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
     * @param integer $id The ID of the workout.
     * @param string $sport The sport. One of the constants from SportMapperInterface.
     * @param DateTime $startDateTime The start date time of the workout.
     */
    public function __construct($id, $sport, DateTime $startDateTime)
    {
        $this->id = $id;
        $this->sport = $sport;
        $this->startDateTime = $startDateTime;
    }
} 