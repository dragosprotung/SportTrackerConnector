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
        $xmlWriter = new XMLWriter();
        $xmlWriter->openMemory();
        $xmlWriter->setIndent(true);
        $xmlWriter->startDocument('1.0', 'UTF-8');
        $xmlWriter->startElement('gpx');

        $xmlWriter->writeAttribute('version', '1.1');
        $xmlWriter->writeAttribute('creator', 'SportTrackerConnector');
        $xmlWriter->writeAttributeNs(
            'xsi',
            'schemaLocation',
            null,
            'http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd http://www.garmin.com/xmlschemas/GpxExtensions/v3 http://www.garmin.com/xmlschemas/GpxExtensionsv3.xsd http://www.garmin.com/xmlschemas/TrackPointExtension/v1 http://www.garmin.com/xmlschemas/TrackPointExtensionv1.xsd'
        );
        $xmlWriter->writeAttribute('xmlns', 'http://www.topografix.com/GPX/1/1');
        $xmlWriter->writeAttributeNs('xmlns', 'gpxtpx', null, 'http://www.garmin.com/xmlschemas/TrackPointExtension/v1');
        $xmlWriter->writeAttributeNs('xmlns', 'gpxx', null, 'http://www.garmin.com/xmlschemas/GpxExtensions/v3');
        $xmlWriter->writeAttributeNs('xmlns', 'xsi', null, 'http://www.w3.org/2001/XMLSchema-instance');

        $this->writeMetaData($xmlWriter, $workout);
        $this->writeTracks($xmlWriter, $workout);

        $xmlWriter->endElement();
        $xmlWriter->endDocument();

        return $xmlWriter->outputMemory(true);
    }

    /**
     * Write the tracks to the GPX.
     *
     * @param XMLWriter $xmlWriter The XML writer.
     * @param Workout $workout The workout.
     */
    protected function writeTracks(XMLWriter $xmlWriter, Workout $workout)
    {
        foreach ($workout->getTracks() as $track) {
            $xmlWriter->startElement('trk');
            $xmlWriter->writeElement('type', $track->getSport());
            $xmlWriter->startElement('trkseg');
            $this->writeTrackPoints($xmlWriter, $track->getTrackpoints());
            $xmlWriter->endElement();
            $xmlWriter->endElement();
        }
    }

    /**
     * Write the track points to the GPX.
     *
     * @param XMLWriter $xmlWriter The XML writer.
     * @param \SportTrackerConnector\Workout\Workout\TrackPoint[] $trackPoints The track points to write.
     */
    private function writeTrackPoints(XMLWriter $xmlWriter, array $trackPoints)
    {
        foreach ($trackPoints as $trackPoint) {
            $xmlWriter->startElement('trkpt');

            // Location.
            $xmlWriter->writeAttribute('lat', $trackPoint->getLatitude());
            $xmlWriter->writeAttribute('lon', $trackPoint->getLongitude());

            // Elevation.
            $xmlWriter->writeElement('ele', $trackPoint->getElevation());

            // Time of position
            $dateTime = clone $trackPoint->getDateTime();
            $dateTime->setTimezone(new DateTimeZone('UTC'));
            $xmlWriter->writeElement('time', $dateTime->format(DateTime::W3C));

            // Extensions.
            $this->writeExtensions($xmlWriter, $trackPoint->getExtensions());

            $xmlWriter->endElement();
        }
    }

    /**
     * Write the extensions into the GPX.
     *
     * @param XMLWriter $xmlWriter The XMLWriter.
     * @param \SportTrackerConnector\Workout\Workout\Extension\ExtensionInterface[] $extensions The extensions to write.
     * @throws InvalidArgumentException If an extension is not known.
     */
    protected function writeExtensions(XMLWriter $xmlWriter, array $extensions)
    {
        $xmlWriter->startElement('extensions');
        foreach ($extensions as $extension) {
            switch ($extension->getID()) {
                case HR::ID:
                    $xmlWriter->startElementNs('gpxtpx', 'TrackPointExtension', null);
                    $xmlWriter->writeElementNs('gpxtpx', 'hr', null, $extension->getValue());
                    $xmlWriter->endElement();
                    break;
            }
        }
        $xmlWriter->endElement();
    }

    /**
     * Write the metadata in the GPX.
     *
     * @param XMLWriter $xmlWriter The XML writer.
     * @param Workout $workout The workout.
     */
    protected function writeMetaData(XMLWriter $xmlWriter, Workout $workout)
    {
        $xmlWriter->startElement('metadata');
        if ($workout->getAuthor() !== null) {
            $xmlWriter->startElement('author');
            $xmlWriter->writeElement('name', $workout->getAuthor()->getName());
            $xmlWriter->endElement();
        }
        $xmlWriter->endElement();
    }
}
