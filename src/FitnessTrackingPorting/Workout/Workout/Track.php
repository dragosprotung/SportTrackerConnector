<?php

namespace FitnessTrackingPorting\Workout\Workout;

use DateTime;
use DateInterval;

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
    protected $sport = SportInterface::OTHER;

    /**
     * The track points of this track.
     *
     * @var TrackPoint[]
     */
    protected $trackPoints = array();

    /**
     * The start date and time of the track.
     *
     * @var DateTime
     */
    protected $startDateTime;

    /**
     * The end date and time of the track.
     *
     * @var DateTime
     */
    protected $endDateTime;

    /**
     * Get the length of the track in meters.
     *
     * @var integer
     */
    protected $length = 0;

    /**
     * Constructor.
     *
     * @param array $trackPoints The track points.
     * @param mixed $sport The sport for this track.
     */
    public function __construct(array $trackPoints = array(), $sport = SportInterface::OTHER)
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

    /**
     * Set the start date and time of the track.
     *
     * @param DateTime $startDateTime The start date and time.
     */
    public function setStartDateTime(DateTime $startDateTime)
    {
        $this->startDateTime = $startDateTime;
    }

    /**
     * Get the start date and time of the track.
     *
     * @return DateTime
     */
    public function getStartDateTime()
    {
        if ($this->startDateTime === null) {
            $this->recomputeStartDateTime();
        }

        return $this->startDateTime;
    }

    /**
     * Recompute the start date and time of the track.
     *
     * @return DateTime
     */
    public function recomputeStartDateTime()
    {
        $this->startDateTime = null;
        foreach ($this->getTrackPoints() as $trackPoint) {
            if ($this->startDateTime > $trackPoint->getDateTime() || $this->startDateTime === null) {
                $this->startDateTime = clone $trackPoint->getDateTime();
            }
        }

        return $this->startDateTime;
    }

    /**
     * Set the end date and time of the track.
     *
     * @param DateTime $endDateTime The end date and time.
     */
    public function setEndDateTime(DateTime $endDateTime)
    {
        $this->endDateTime = $endDateTime;
    }

    /**
     * Get the start date and time of the track.
     *
     * @return DateTime
     */
    public function getEndDateTime()
    {
        if ($this->endDateTime === null) {
            $this->recomputeEndDateTime();
        }

        return $this->endDateTime;
    }

    /**
     * Recompute the start date and time of the track.
     *
     * @return DateTime
     */
    public function recomputeEndDateTime()
    {
        $this->endDateTime = null;
        foreach ($this->getTrackPoints() as $trackPoint) {
            if ($this->endDateTime < $trackPoint->getDateTime() || $this->startDateTime === null) {
                $this->endDateTime = clone $trackPoint->getDateTime();
            }
        }

        return $this->endDateTime;
    }

    /**
     * Get the duration of the track.
     *
     * @return DateInterval
     */
    public function getDuration()
    {
        $start = $this->getStartDateTime();
        $end = $this->getEndDateTime();

        $dateDifference = $start->diff($end);

        return $dateDifference;
    }

    /**
     * Set the length of the track.
     *
     * @param integer $length The length of the track.
     */
    public function setLength($length)
    {
        $this->length = $length;
    }

    /**
     * Get the length of the track.
     *
     * @return integer
     */
    public function getLength()
    {
        if ($this->length === 0) {
            $this->length = $this->recomputeLength();
        }

        return $this->length;

    }

    /**
     * Recompute the length of the track.
     */
    public function recomputeLength()
    {
        $this->length = 0;

        $trackPoints = $this->getTrackPoints();
        $trackPointsCount = count($trackPoints);
        if ($trackPointsCount < 2) {
            return 0;
        }

        for ($i = 1; $i < $trackPointsCount; $i++) {
            $previousTrack = $trackPoints[$i - 1];
            $currentTrack = $trackPoints[$i];

            $this->length += sqrt(
                pow(($previousTrack->getLongitude() - $currentTrack->getLongitude()), 2) + pow(
                    ($previousTrack->getLatitude() - $currentTrack->getLatitude()),
                    2
                )
            );
        }

        $this->length = round($this->length, 6);
        
        return $this->length;
    }
} 