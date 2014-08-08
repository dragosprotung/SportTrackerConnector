<?php

namespace SportTrackerConnector\Workout\Workout;

use SportTrackerConnector\Workout\Workout\Extension\ExtensionInterface;
use DateTime;
use InvalidArgumentException;

/**
 * A point in a track.
 */
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
    protected $dateTime;

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
     * @param DateTime $dateTime The date and time of the point.
     */
    public function __construct($latitude, $longitude, DateTime $dateTime)
    {
        $this->setLatitude($latitude);
        $this->setLongitude($longitude);
        $this->setDateTime($dateTime);
    }

    /**
     * Set the elevation.
     *
     * @param float $elevation The elevation.
     * @throws InvalidArgumentException If the elevation is not a number.
     */
    public function setElevation($elevation)
    {
        if ($elevation !== null && !is_numeric($elevation)) {
            throw new InvalidArgumentException('Elevation for a tracking point must be a number.');
        }

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
        $this->extensions = array();
        foreach ($extensions as $extension) {
            $this->addExtension($extension);
        }
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
        $this->extensions[$extension->getID()] = $extension;
    }

    /**
     * Check if an extension is present.
     *
     * @param string $id The ID of the extension.
     * @return boolean
     */
    public function hasExtension($id)
    {
        return isset($this->extensions[$id]);
    }

    /**
     * Get an extension by ID.
     *
     * @param string $id The ID of the extension.
     * @return ExtensionInterface
     * @throws \OutOfBoundsException If the extension is not found.
     */
    public function getExtension($id)
    {
        if ($this->hasExtension($id) !== true) {
            throw new \OutOfBoundsException('Extension "' . $id . '" not found.');
        }

        return $this->extensions[$id];
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
     * @param DateTime $dateTime The date time of the point.
     */
    public function setDateTime(DateTime $dateTime)
    {
        $this->dateTime = $dateTime;
    }

    /**
     * Get the date time of the point.
     *
     * @return DateTime
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }

    /**
     * Get the distance between this point and another point in meters.
     *
     * @param TrackPoint $trackPoint The other point.
     * @return float The distance in meters.
     */
    public function distance(TrackPoint $trackPoint)
    {
        $earthRadius = 6371000;

        $latFrom = deg2rad($this->getLatitude());
        $lonFrom = deg2rad($this->getLongitude());
        $latTo = deg2rad($trackPoint->getLatitude());
        $lonTo = deg2rad($trackPoint->getLongitude());

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(
            sqrt(
                pow(sin($latDelta / 2), 2) +
                cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)
            )
        );
        return $angle * $earthRadius;
    }

    /**
     * Get the speed between this point and another point in km/h.
     *
     * @param TrackPoint $trackPoint The other point.
     * @return float
     */
    public function speed(TrackPoint $trackPoint)
    {

        $start = $this->getDateTime();
        $end = $trackPoint->getDateTime();
        $dateDifference = $start->diff($end);
        $secondsDifference = $dateDifference->days * 86400 + $dateDifference->h * 3600 + $dateDifference->i * 60 + $dateDifference->s;

        if ($secondsDifference === 0) {
            return 0;
        }

        $distance = $this->distance($trackPoint);

        return ($distance / $secondsDifference) * 3.6;
    }
}
