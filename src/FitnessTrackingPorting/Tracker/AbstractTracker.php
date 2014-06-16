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
     * Username for polar.
     *
     * @var string
     */
    protected $username;

    /**
     * Password for polar.
     *
     * @var string
     */
    protected $password;

    /**
     * The tracker timezone.
     *
     * @var DateTimeZone
     */
    protected $timeZone;

    /**
     * The sport mapper.
     *
     * @var \FitnessTrackingPorting\Workout\Workout\SportMapperInterface
     */
    protected $sportMapper;

    /**
     * Constructor.
     *
     * @param string $username Username for the tracker.
     * @param string $password Password for the tracker.
     */
    public function __construct($username = null, $password = null)
    {
        $this->username = $username;
        $this->password = $password;
        $this->timeZone = new DateTimeZone('UTC');
    }

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

    /**
     * Get the sport mapper.
     *
     * @return \FitnessTrackingPorting\Workout\Workout\SportMapperInterface
     */
    public function getSportMapper()
    {
        if ($this->sportMapper === null) {
            $this->sportMapper = $this->constructSportMapper();
        }

        return $this->sportMapper;
    }

    /**
     * Construct the sport mapper.
     *
     * @return \FitnessTrackingPorting\Workout\Workout\SportMapperInterface
     */
    abstract protected function constructSportMapper();
}