<?php

namespace FitnessTrackingPorting\Tracker;

use DateTime;
use DateTimeZone;

/**
 * Abstract tracker.
 */
abstract class AbstractTracker implements TrackerInterface
{

    /**
     * The tracker timezone.
     *
     * @var DateTimeZone
     */
    protected $timeZone;

    /**
     * Get a new instance using a config array.
     *
     * @param array $config The config for the new instance.
     * @return TrackerInterface
     */
    public static function fromConfig(array $config)
    {
        $tracker = new static($config['auth']['username'], $config['auth']['password']);

        $timeZone = new DateTimeZone($config['timezone']);
        $tracker->setTimeZone($timeZone);

        return $tracker;
    }

    /**
     * Set the timezone of the tracker.
     *
     * @param DateTimeZone $timeZone The timezone.
     */
    public function setTimeZone(DateTimeZone $timeZone)
    {
        $this->timeZone = $timeZone;
    }

    /**
     * Get the timezone of the tracker.
     *
     * @return DateTimeZone
     */
    public function getTimeZone()
    {
        return $this->timeZone;
    }

    /**
     * Get offset between the tracker time zone and UTC time zone in seconds.
     *
     * @return integer
     */
    public function getTimeZoneOffset()
    {
        $originDateTime = new DateTime('now', $this->getTimeZone());

        $UTCTimeZone = new DateTimeZone('UTC');
        $UTCDateTime = new DateTime('now', $UTCTimeZone);

        return $UTCTimeZone->getOffset($UTCDateTime) - $this->getTimeZone()->getOffset($originDateTime);
    }
}