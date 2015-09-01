<?php

namespace SportTrackerConnector\Tracker\Endomondo;

use DateTime;
use DateTimeZone;
use GuzzleHttp\Client;
use RuntimeException;
use SportTrackerConnector\Core\Tracker\AbstractTracker;
use SportTrackerConnector\Core\Tracker\TrackerListWorkoutsResult;
use SportTrackerConnector\Core\Workout\Workout;
use SportTrackerConnector\Core\Workout\Extension\HR;
use SportTrackerConnector\Core\Workout\Track;
use SportTrackerConnector\Core\Workout\TrackPoint;

/**
 * Endomondo tracker.
 */
class Tracker extends AbstractTracker
{

    /**
     * The Endomondo API.
     *
     * @var API
     */
    protected $endomondoAPI;

    /**
     * {@inheritdoc}
     */
    public static function getID()
    {
        return 'endomondo';
    }

    /**
     * {@inheritdoc}
     */
    public function listWorkouts(DateTime $startDate, DateTime $endDate)
    {
        $list = array();
        $this->logger->debug('Downloading JSON of workouts.');
        $data = $this->getEndomondoAPI()->listWorkouts($startDate, $endDate);
        $this->logger->debug('Parsing data.');
        foreach ($data['data'] as $workout) {
            $startDateTime = DateTime::createFromFormat('Y-m-d H:i:s \U\T\C', $workout['start_time'], new DateTimeZone('UTC'));
            if ($startDateTime === false) {
                throw new RuntimeException('The workout "' . $workout['id'] . '" start date time is not valid.');
            }
            $startDateTime->setTimezone($this->getTimeZone());
            $list[] = new TrackerListWorkoutsResult(
                $workout['id'],
                $this->getSportMapper()->getSportFromCode($workout['sport']),
                $startDateTime
            );
        }

        return $list;
    }

    /**
     * Get the Endomondo API.
     *
     * @return API
     */
    public function getEndomondoAPI()
    {
        if ($this->endomondoAPI === null) {
            $client = new Client();
            $this->endomondoAPI = new API($client, $this->username, $this->password, $this->getSportMapper());
        }

        return $this->endomondoAPI;
    }

    /**
     * {@inheritdoc}
     */
    public function downloadWorkout($idWorkout)
    {
        $this->logger->debug('Downloading JSON for workout "' . $idWorkout . '"');

        $json = $this->getEndomondoAPI()->getWorkout($idWorkout);

        $workout = new Workout();
        $track = new Track();
        $track->setSport($this->getSportMapper()->getSportFromCode($json['sport']));

        if (isset($json['points'])) {
            $this->logger->debug('Writing track points.');

            foreach ($json['points'] as $point) {
                $trackPoint = new TrackPoint($point['lat'], $point['lng'], new DateTime($point['time'], $this->getTimeZone()));
                if (isset($point['alt'])) {
                    $trackPoint->setElevation($point['alt']);
                }
                if (isset($point['hr'])) {
                    $trackPoint->addExtension(new HR($point['hr']));
                }

                $track->addTrackPoint($trackPoint);
            }
        } else {
            $this->logger->warning('No track points found for workout "' . $idWorkout . '".');
        }

        $workout->addTrack($track);

        return $workout;
    }

    /**
     * {@inheritdoc}
     */
    public function uploadWorkout(Workout $workout)
    {
        $this->logger->debug('Uploading workout.');

        $workoutId = $this->getEndomondoAPI()->postWorkout($workout);
        return $workoutId !== null;
    }

    /**
     * {@inheritdoc}
     */
    protected function constructSportMapper()
    {
        return new Sport();
    }
}
