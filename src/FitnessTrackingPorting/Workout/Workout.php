<?php

namespace FitnessTrackingPorting\Workout;

use FitnessTrackingPorting\Workout\Workout\Author;
use FitnessTrackingPorting\Workout\Workout\Sport;
use FitnessTrackingPorting\Workout\Workout\TrackPoint;

/**
 * A workout.
 */
class Workout
{

    /**
     * The author of a workout.
     *
     * @var Author
     */
    protected $author;

    /**
     * The track points of the workout.
     *
     * @var TrackPoint[]
     */
    protected $trackPoints = array();

    /**
     * The sport for the workout.
     *
     * @var string
     */
    protected $sport = Sport::OTHER;

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
     * Set the author of a workout.
     *
     * @param Author $author The author.
     */
    public function setAuthor(Author $author)
    {
        $this->author = $author;
    }

    /**
     * Get the author of the workout.
     *
     * @return Author
     */
    public function getAuthor()
    {
        return $this->author;
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