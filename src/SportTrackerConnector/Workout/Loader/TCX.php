<?php

namespace SportTrackerConnector\Workout\Loader;

use DateTime;
use SimpleXMLElement;
use SportTrackerConnector\Workout\Workout\Extension\ExtensionInterface;
use SportTrackerConnector\Workout\Workout\Extension\HR;
use SportTrackerConnector\Workout\Workout\SportGuesser;
use SportTrackerConnector\Workout\Workout\Track;
use SportTrackerConnector\Workout\Workout\TrackPoint;
use SportTrackerConnector\Workout\Workout;

/**
 * Load a workout from TCX format.
 */
class TCX extends AbstractLoader
{

    /**
     * Get a workout from a string.
     *
     * @param string $string The data.
     * @return \SportTrackerConnector\Workout\Workout;
     */
    public function fromString($string)
    {
        $simpleXML = new SimpleXMLElement($string);
        $workout = new Workout();

        foreach ($simpleXML->Activities[0] as $simpleXMLActivity) {
            $workoutTrack = new Track();

            // Sport.
            $attributes = $simpleXMLActivity->attributes();
            if (isset($attributes['Sport'])) {
                $workoutTrack->setSport(SportGuesser::getSportFromCode((string)$attributes['Sport']));
            }

            // Track points.
            foreach ($simpleXMLActivity->Lap as $lap) {
                foreach ($lap->Track as $track) {
                    foreach ($track->Trackpoint as $trackPoint) {
                        $dateTime = new DateTime($trackPoint->Time);
                        $latitude = (string)$trackPoint->Position->LatitudeDegrees;
                        $longitude = (string)$trackPoint->Position->LongitudeDegrees;

                        $workoutTrackPoint = new TrackPoint($latitude, $longitude, $dateTime);
                        $workoutTrackPoint->setElevation((int)$trackPoint->AltitudeMeters);

                        $extensions = $this->parseExtensions($trackPoint);
                        $workoutTrackPoint->setExtensions($extensions);

                        $workoutTrack->addTrackPoint($workoutTrackPoint);
                    }
                }
            }

            $workout->addTrack($workoutTrack);
        }


        return $workout;
    }

    /**
     * Parse and return an array of extensions from the XML.
     *
     * @param SimpleXMLElement $trackPoint The track point from the TCX to parse.
     * @return ExtensionInterface[]
     */
    private function parseExtensions(SimpleXMLElement $trackPoint)
    {
        $return = array();

        if ($trackPoint->HeartRateBpm) {
            $return[] = new HR((int)$trackPoint->HeartRateBpm->Value);
        }

        return $return;
    }
}