<?php

namespace FitnessTrackingPorting\Workout\Workout;

use FitnessTrackingPorting\Workout\Workout\Extension\ExtensionInterface;
use DateTime;

class TrackPoint
{

    /**
     * Latitude of the point.
     *
     * @var float
     */
    protected $latitude;

    /**
     * Longitude of the point.
     *
     * @var float
     */
    protected $longitude;

    /**
     * Elevation of the point.
     *
     * @var float
     */
    protected $elevation;

    /**
     * The time for the point.
     *
     * @var DateTime
     */
    protected $time;

    /**
     * Array of extensions.
     *
     * @var ExtensionInterface[]
     */
    protected $extensions = array();

    /**
     * Constructor.
     *
     * @param float $latitude The latitude.
     * @param float $longitude The longitude.
     * @param DateTime $time The time.
     */
    public function __construct($latitude, $longitude, DateTime $time)
    {
        $this->setLatitude($latitude);
        $this->setLongitude($longitude);
        $this->setTime($time);
    }

    /**
     * Set the elevation.
     *
     * @param float $elevation The elevation.
     */
    public function setElevation($elevation)
    {
        $this->elevation = $elevation;
    }

    /**
     * Get the elevation.
     *
     * @return float
     */
    public function getElevation()
    {
        return $this->elevation;
    }

    /**
     * Set the extensions.
     *
     * @param ExtensionInterface[] $extensions The extensions to set.
     */
    public function setExtensions(array $extensions)
    {
        $this->extensions = $extensions;
    }

    /**
     * Get the extensions.
     *
     * @return ExtensionInterface[]
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * Add an extension to the workout.
     *
     * @param ExtensionInterface $extension The extension to add.
     */
    public function addExtension(ExtensionInterface $extension)
    {
        $this->extensions[] = $extension;
    }

    /**
     * Set the latitude.
     *
     * @param float $latitude The latitude.
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    }

    /**
     * Get the latitude.
     *
     * @return float
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set the longitude.
     * @param float $longitude The longitude.
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
    }

    /**
     * Get the longitude.
     *
     * @return float
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Set the date time of the point.
     *
     * @param DateTime $time The date time of the point.
     */
    public function setTime(DateTime $time)
    {
        $this->time = $time;
    }

    /**
     * Get the date time of the point.
     *
     * @return DateTime
     */
    public function getTime()
    {
        return $this->time;
    }
} 