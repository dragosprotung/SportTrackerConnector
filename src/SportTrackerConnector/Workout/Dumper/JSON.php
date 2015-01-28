<?php

namespace SportTrackerConnector\Workout\Dumper;

use DateTime;
use DateTimeZone;
use SportTrackerConnector\Workout\Workout;

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
        $tracks = $workout->getTracks();
        foreach ($tracks as $track) {
            $data[] = array(
                'workout' => array(
                    'points' => $this->writeTrackPoints($track->getTrackpoints())
                )
            );
        }

        return json_encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * Write the track points into an array.
     *
     * @param \SportTrackerConnector\Workout\Workout\TrackPoint[] $trackPoints The track points to write.
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
                'latitude' => $trackPoint->getLatitude(),
                'longitude' => $trackPoint->getLongitude(),
                'elevation' => $trackPoint->getElevation(),
                'distance' => $trackPoint->getDistance(),
                'extensions' => $this->writeExtensions($trackPoint->getExtensions())
            );

            $points[] = $point;
        }

        return $points;
    }

    /**
     * Write the extensions into an array.
     *
     * @param \SportTrackerConnector\Workout\Workout\Extension\ExtensionInterface[] $extensions The extensions to write.
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
