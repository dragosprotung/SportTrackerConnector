<?php

namespace FitnessTrackingPorting\Workout\Dumper;

use FitnessTrackingPorting\Workout\Workout;
use DateTimeZone;
use DateTime;

/**
 * Dump a workout to JSON.
 */
class JSON extends AbstractDumper
{

    /**
     * Dump a workout to string.
     *
     * @param Workout $workout The workout to dump.
     * @return string
     */
    public function dumpToString(Workout $workout)
    {
        $data = array();
        foreach ($workout->getTracks() as $track) {
            $workout = array('points' => $this->writeTrackPoints($track->getTrackpoints()));
            $data[] = array('workout' => $workout);
        }

        return json_encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * Write the track points into an array.
     *
     * @param \FitnessTrackingPorting\Workout\Workout\TrackPoint[] $trackPoints The track points to write.
     * @return array
     */
    private function writeTrackPoints(array $trackPoints)
    {
        $points = array();
        foreach ($trackPoints as $trackPoint) {
            $dateTime = clone $trackPoint->getDateTime();
            $dateTime->setTimezone(new DateTimeZone('UTC'));
            $point = array(
                'time' => $dateTime->format(DateTime::W3C),
                'lat' => $trackPoint->getLatitude(),
                'lon' => $trackPoint->getLongitude(),
                'ele' => $trackPoint->getElevation(),
                'extensions' => $this->writeExtensions($trackPoint->getExtensions())
            );

            $points[] = $point;
        }

        return $points;
    }

    /**
     * Write the extensions into an array.
     *
     * @param \FitnessTrackingPorting\Workout\Workout\Extension\ExtensionInterface[] $extensions The extensions to write.
     * @return array
     */
    protected function writeExtensions(array $extensions)
    {
        $return = array();
        foreach ($extensions as $extension) {
            $return[$extension->getID()] = $extension->getValue();
        }

        return $return;
    }
}