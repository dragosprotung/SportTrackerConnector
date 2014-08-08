<?php

namespace SportTrackerConnector\Workout\Dumper;

use DateTime;
use DateTimeZone;
use InvalidArgumentException;
use SportTrackerConnector\Workout\Workout\Extension\HR;
use SportTrackerConnector\Workout\Workout\Track;
use SportTrackerConnector\Workout\Workout;
use XMLWriter;

/**
 * Dump a workout to TCX format.
 */
class TCX extends AbstractDumper
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
        $XMLWriter->startElement('TrainingCenterDatabase');

        $XMLWriter->writeAttributeNs(
            'xsi',
            'schemaLocation',
            null,
            'http://www.garmin.com/xmlschemas/TrainingCenterDatabase/v2 http://www.garmin.com/xmlschemas/TrainingCenterDatabasev2.xsd'
        );
        $XMLWriter->writeAttribute('xmlns', 'http://www.garmin.com/xmlschemas/TrainingCenterDatabase/v2');
        $XMLWriter->writeAttributeNs('xmlns', 'xsi', null, 'http://www.w3.org/2001/XMLSchema-instance');

        $this->writeTracks($XMLWriter, $workout);

        $XMLWriter->endElement();
        $XMLWriter->endDocument();

        return $XMLWriter->outputMemory(true);
    }

    /**
     * Write the tracks to the TCX.
     *
     * @param XMLWriter $XMLWriter The XML writer.
     * @param Workout $workout The workout.
     */
    protected function writeTracks(XMLWriter $XMLWriter, Workout $workout)
    {
        $XMLWriter->startElement('Activities');
        foreach ($workout->getTracks() as $track) {
            $XMLWriter->startElement('Activity');
            $XMLWriter->writeAttribute('Sport', ucfirst($track->getSport()));
            // Use the start date time as the ID. This could be anything.
            $XMLWriter->writeElement('Id', $this->formatDateTime($track->getStartDateTime()));

            $XMLWriter->startElement('Lap');

            $XMLWriter->writeAttribute('StartTime', $this->formatDateTime($track->getStartDateTime()));
            $XMLWriter->writeElement('TotalTimeSeconds', $track->getDuration()->getTotalSeconds());
            $XMLWriter->writeElement('DistanceMeters', $track->getLength());

            $this->writeLapHeartRateDate($XMLWriter, $track);

            $XMLWriter->startElement('Track');
            $this->writeTrackPoints($XMLWriter, $track->getTrackpoints());
            $XMLWriter->endElement();

            $XMLWriter->endElement();

            $XMLWriter->endElement();
        }
        $XMLWriter->endElement();
    }

    /**
     * Write the track points to the TCX.
     *
     * @param XMLWriter $XMLWriter The XML writer.
     * @param \SportTrackerConnector\Workout\Workout\TrackPoint[] $trackPoints The track points to write.
     */
    private function writeTrackPoints(XMLWriter $XMLWriter, array $trackPoints)
    {
        foreach ($trackPoints as $trackPoint) {
            $XMLWriter->startElement('Trackpoint');

            // Time of position
            $dateTime = clone $trackPoint->getDateTime();
            $dateTime->setTimezone(new DateTimeZone('UTC'));
            $XMLWriter->writeElement('Time', $this->formatDateTime($dateTime));

            // Position.
            $XMLWriter->startElement('Position');
            $XMLWriter->writeElement('LatitudeDegrees', $trackPoint->getLatitude());
            $XMLWriter->writeElement('LongitudeDegrees', $trackPoint->getLongitude());
            $XMLWriter->endElement();

            // Elevation.
            $XMLWriter->writeElement('AltitudeMeters', $trackPoint->getElevation());

            // Extensions.
            $this->writeExtensions($XMLWriter, $trackPoint->getExtensions());

            $XMLWriter->endElement();
        }
    }

    /**
     * Write the heart rate data for a lap.
     *
     * @param XMLWriter $XMLWriter The XML writer.
     * @param \SportTrackerConnector\Workout\Workout\Track $track The track to write.
     */
    private function writeLapHeartRateDate(XMLWriter $XMLWriter, Track $track)
    {
        $averageHeartRate = array();
        $maxHearRate = null;
        foreach ($track->getTrackPoints() as $trackPoint) {
            if ($trackPoint->hasExtension(HR::ID) === true) {
                $pointHearRate = $trackPoint->getExtension(HR::ID)->getValue();

                $maxHearRate = max($maxHearRate, $pointHearRate);
                $averageHeartRate[] = $pointHearRate;
            }
        }

        if ($averageHeartRate !== array()) {
            $XMLWriter->startElement('AverageHeartRateBpm');
            $XMLWriter->writeAttributeNs('xsi', 'type', null, 'HeartRateInBeatsPerMinute_t');
            $XMLWriter->writeElement('Value', array_sum($averageHeartRate) / count($averageHeartRate));
            $XMLWriter->endElement();
        }

        if ($maxHearRate !== null) {
            $XMLWriter->startElement('MaximumHeartRateBpm');
            $XMLWriter->writeAttributeNs('xsi', 'type', null, 'HeartRateInBeatsPerMinute_t');
            $XMLWriter->writeElement('Value', $maxHearRate);
            $XMLWriter->endElement();
        }
    }

    /**
     * Write the extensions into the TCX.
     *
     * @param XMLWriter $XMLWriter The XMLWriter.
     * @param \SportTrackerConnector\Workout\Workout\Extension\ExtensionInterface[] $extensions The extensions to write.
     * @throws InvalidArgumentException If an extension is not known.
     */
    protected function writeExtensions(XMLWriter $XMLWriter, array $extensions)
    {
        foreach ($extensions as $extension) {
            switch ($extension->getID()) {
                case HR::ID:
                    $XMLWriter->startElement('HeartRateBpm');
                    $XMLWriter->writeElement('Value', $extension->getValue());
                    $XMLWriter->endElement();
                    break;
            }
        }
    }

    /**
     * Format a DateTime object for TCX format.
     * @param DateTime $dateTime The date time to format.
     * @return string
     */
    private function formatDateTime(DateTime $dateTime)
    {
        return $dateTime->format('Y-m-d\TH:i:s\Z');
    }
}
