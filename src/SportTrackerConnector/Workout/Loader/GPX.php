<?php

namespace SportTrackerConnector\Workout\Loader;

use SportTrackerConnector\Workout\Workout;
use SportTrackerConnector\Workout\Workout\Author;
use SportTrackerConnector\Workout\Workout\Track;
use SportTrackerConnector\Workout\Workout\TrackPoint;
use SportTrackerConnector\Workout\Workout\SportGuesser;
use SportTrackerConnector\Workout\Workout\Extension\ExtensionInterface;
use SportTrackerConnector\Workout\Workout\Extension\HR;
use SimpleXMLElement;
use DateTime;

/**
 * Load a workout from GPX format.
 */
class GPX extends AbstractLoader
{

    /**
     * Get a workout from a string.
     *
     * @param string $string The data.
     * @return \SportTrackerConnector\Workout\Workout
     */
    public function fromString($string)
    {
        $simpleXML = new SimpleXMLElement($string);
        $workout = new Workout();

        if (isset($simpleXML->metadata->author->name)) {
            $workout->setAuthor(new Author($simpleXML->metadata->author->name));
        }

        foreach ($simpleXML->trk as $simpleXMLTrack) {
            $track = new Track();

            // Sport.
            if (isset($simpleXMLTrack->type)) {
                $track->setSport(SportGuesser::getSportFromCode($simpleXMLTrack->type));
            }

            // Track points.
            foreach ($simpleXMLTrack->trkseg->trkpt as $point) {
                $attributes = $point->attributes();
                $dateTime = new DateTime($point->time);
                $trackPoint = new TrackPoint((string)$attributes['lat'], (string)$attributes['lon'], $dateTime);
                $trackPoint->setElevation((int)$point->ele);
                if (isset($point->extensions)) {
                    $trackPoint->setExtensions($this->parseExtensions($point->extensions));
                }

                $track->addTrackPoint($trackPoint);
            }

            $workout->addTrack($track);
        }


        return $workout;
    }

    /**
     * Parse and return an array of extensions from the XML.
     *
     * @param SimpleXMLElement $extensions The extensions to parse.
     * @return ExtensionInterface[]
     */
    private function parseExtensions(SimpleXMLElement $extensions)
    {
        $extensions = $extensions->asXML();
        $return = array();
        if (preg_match('/<gpxtpx:hr>(.*)<\/gpxtpx:hr>/', $extensions, $matches)) {
            $return[] = new HR((int)$matches[1]);
        }

        return $return;
    }
}
