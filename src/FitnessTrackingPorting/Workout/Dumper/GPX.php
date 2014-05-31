<?php

namespace FitnessTrackingPorting\Workout\Dumper;

use FitnessTrackingPorting\Workout\Workout;
use XMLWriter;
use DateTime;
use DateTimeZone;
use InvalidArgumentException;

/**
 * Dump a workout to GPX format.
 */
class GPX extends AbstractDumper
{
    /**
     * Dump a workout to string.
     *
     * @param Workout $workout The workout to dump.
     * @return string
     */
    public function dumpToString(Workout $workout)
    {
        $XMLWriter = new XMLWriter();
        $XMLWriter->openMemory();
        $XMLWriter->setIndent(true);
        $XMLWriter->startDocument('1.0', 'UTF-8');
        $XMLWriter->startElement('gpx');

        $XMLWriter->writeAttribute('version', '1.1');
        $XMLWriter->writeAttribute('creator', 'FitnessTrackingPorting');
        $XMLWriter->writeAttributeNs(
            'xsi',
            'schemaLocation',
            null,
            'http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd http://www.garmin.com/xmlschemas/GpxExtensions/v3 http://www.garmin.com/xmlschemas/GpxExtensionsv3.xsd http://www.garmin.com/xmlschemas/TrackPointExtension/v1 http://www.garmin.com/xmlschemas/TrackPointExtensionv1.xsd'
        );
        $XMLWriter->writeAttribute('xmlns', 'http://www.topografix.com/GPX/1/1');
        $XMLWriter->writeAttributeNs('xmlns', 'gpxtpx', null, 'http://www.garmin.com/xmlschemas/TrackPointExtension/v1');
        $XMLWriter->writeAttributeNs('xmlns', 'gpxx', null, 'http://www.garmin.com/xmlschemas/GpxExtensions/v3');
        $XMLWriter->writeAttributeNs('xmlns', 'xsi', null, 'http://www.w3.org/2001/XMLSchema-instance');

        $this->writeMetaData($XMLWriter, $workout);
        $this->writeTracks($XMLWriter, $workout);

        $XMLWriter->endElement();
        $XMLWriter->endDocument();

        return $XMLWriter->outputMemory(true);
    }

    /**
     * Write the tracks to the GPX.
     *
     * @param XMLWriter $XMLWriter The XML writer.
     * @param Workout $workout The workout.
     */
    protected function writeTracks(XMLWriter $XMLWriter, Workout $workout)
    {
        foreach ($workout->getTracks() as $track) {
            $XMLWriter->startElement('trk');
            $XMLWriter->writeElement('type', $track->getSport());
            $XMLWriter->startElement('trkseg');
            foreach ($track->getTrackpoints() as $trackPoint) {
                $XMLWriter->startElement('trkpt');

                // Location.
                $XMLWriter->writeAttribute('lat', $trackPoint->getLatitude());
                $XMLWriter->writeAttribute('lon', $trackPoint->getLongitude());

                // Elevation.
                $XMLWriter->writeElement('ele', $trackPoint->getElevation());

                // Time of position
                $dateTime = clone $trackPoint->getTime();
                $dateTime->setTimezone(new DateTimeZone('UTC'));
                $XMLWriter->writeElement('time', $dateTime->format(DateTime::W3C));

                // Extensions.
                $this->writeExtensions($XMLWriter, $trackPoint->getExtensions());

                $XMLWriter->endElement();
            }
            $XMLWriter->endElement();
            $XMLWriter->endElement();
        }
    }

    /**
     * Write the extensions into the GPX.
     *
     * @param XMLWriter $XMLWriter The XMLWriter.
     * @param array $extensions The extensions to write.
     * @throws InvalidArgumentException If an extension is not known.
     */
    protected function writeExtensions(XMLWriter $XMLWriter, array $extensions)
    {
        $XMLWriter->startElement('extensions');
        foreach ($extensions as $extension) {
            $XMLWriter->startElementNs('gpxtpx', 'TrackPointExtension', null);
            switch (get_class($extension)) {
                case 'FitnessTrackingPorting\Workout\Workout\Extension\HR':
                    $XMLWriter->writeElementNs('gpxtpx', 'hr', null, $extension->getValue());
                    break;
                default:
                    throw new InvalidArgumentException('Unknown extension "' . get_class($extension) . '" ');
            }
            $XMLWriter->endElement();
        }
        $XMLWriter->endElement();
    }

    /**
     * Write the metadata in the GPX.
     *
     * @param XMLWriter $XMLWriter The XML writer.
     * @param Workout $workout The workout.
     */
    protected function writeMetaData(XMLWriter $XMLWriter, Workout $workout)
    {
        $XMLWriter->startElement('metadata');
        if ($workout->getAuthor() !== null) {
            $XMLWriter->startElement('author');
            $XMLWriter->writeElement('name', $workout->getAuthor()->getName());
            $XMLWriter->endElement();
        }
        $XMLWriter->endElement();
    }
}