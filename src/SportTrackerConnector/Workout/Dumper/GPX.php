<?php

namespace SportTrackerConnector\Workout\Dumper;

use SportTrackerConnector\Workout\Workout;
use XMLWriter;
use DateTime;
use DateTimeZone;
use SportTrackerConnector\Workout\Workout\Extension\HR;
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
        $XMLWriter->writeAttribute('creator', 'SportTrackerConnector');
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
            $this->writeTrackPoints($XMLWriter, $track->getTrackpoints());
            $XMLWriter->endElement();
            $XMLWriter->endElement();
        }
    }

    /**
     * Write the track points to the GPX.
     *
     * @param XMLWriter $XMLWriter The XML writer.
     * @param \SportTrackerConnector\Workout\Workout\TrackPoint[] $trackPoints The track points to write.
     */
    private function writeTrackPoints(XMLWriter $XMLWriter, array $trackPoints)
    {
        foreach ($trackPoints as $trackPoint) {
            $XMLWriter->startElement('trkpt');

            // Location.
            $XMLWriter->writeAttribute('lat', $trackPoint->getLatitude());
            $XMLWriter->writeAttribute('lon', $trackPoint->getLongitude());

            // Elevation.
            $XMLWriter->writeElement('ele', $trackPoint->getElevation());

            // Time of position
            $dateTime = clone $trackPoint->getDateTime();
            $dateTime->setTimezone(new DateTimeZone('UTC'));
            $XMLWriter->writeElement('time', $dateTime->format(DateTime::W3C));

            // Extensions.
            $this->writeExtensions($XMLWriter, $trackPoint->getExtensions());

            $XMLWriter->endElement();
        }
    }

    /**
     * Write the extensions into the GPX.
     *
     * @param XMLWriter $XMLWriter The XMLWriter.
     * @param \SportTrackerConnector\Workout\Workout\Extension\ExtensionInterface[] $extensions The extensions to write.
     * @throws InvalidArgumentException If an extension is not known.
     */
    protected function writeExtensions(XMLWriter $XMLWriter, array $extensions)
    {
        $XMLWriter->startElement('extensions');
        foreach ($extensions as $extension) {
            switch ($extension->getID()) {
                case HR::ID:
                    $XMLWriter->startElementNs('gpxtpx', 'TrackPointExtension', null);
                    $XMLWriter->writeElementNs('gpxtpx', 'hr', null, $extension->getValue());
                    $XMLWriter->endElement();
                    break;
            }
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
