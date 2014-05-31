<?php

namespace FitnessTrackingPorting\Workout\Workout;

use FitnessTrackingPorting\Workout\Workout\Sport;

/**
 * A track of a workout.
 */
class Track extends \ArrayObject
{

    /**
     * The sport for the workout.
     *
     * @var string
     */
    protected $sport = Sport::OTHER;

    /**
     * Constructor.
     *
     * @param array $trackPoints The track points.
     * @param string $sport The sport of this track.
     */
    public function __construct(array $trackPoints = array(), $sport = Sport::OTHER)
    {
        parent::__construct($trackPoints);
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
        $this[] = $trackPoint;
    }

    /**
     * Set the track points.
     *
     * @param TrackPoint[] $trackPoints The track points to set.
     */
    public function setTrackPoints(array $trackPoints)
    {
        $this->exchangeArray($trackPoints);
    }

    /**
     * Get the track points.
     *
     * @return TrackPoint[]
     */
    public function getTrackPoints()
    {
        return $this->getArrayCopy();
    }
} 