<?php

namespace FitnessTrackingPorting\Workout\Workout;

use FitnessTrackingPorting\Workout\Workout\Sport;

/**
 * A track of a workout.
 */
class Track
{

    /**
     * The sport for the workout.
     *
     * @var string
     */
    protected $sport = Sport::OTHER;

    /**
     * The track points of this track.
     *
     * @var TrackPoint[]
     */
    protected $trackPoints = array();

    /**
     * Constructor.
     *
     * @param array $trackPoints The track points.
     * @param mixed $sport The sport for this track.
     */
    public function __construct(array $trackPoints = array(), $sport = Sport::OTHER)
    {
        $this->setTrackPoints($trackPoints);
        $this->setSport($sport);
    }

    /**
     * Set the sport for this workout.
     *
     * @param string $sport The sport.
     */
    public function setSport($sport)
    {
        $this->sport = $sport;
    }

    /**
     * Get the sport of the workout.
     *
     * @return string
     */
    public function getSport()
    {
        return $this->sport;
    }

    /**
     * Add a track point.
     *
     * @param TrackPoint $trackPoint The track point to add.
     */
    public function addTrackPoint(TrackPoint $trackPoint)
    {
        $this->trackPoints[] = $trackPoint;
    }

    /**
     * Set the track points.
     *
     * @param TrackPoint[] $trackPoints The track points to set.
     */
    public function setTrackPoints(array $trackPoints)
    {
        $this->trackPoints = $trackPoints;
    }

    /**
     * Get the track points.
     *
     * @return TrackPoint[]
     */
    public function getTrackPoints()
    {
        return $this->trackPoints;
    }
} 