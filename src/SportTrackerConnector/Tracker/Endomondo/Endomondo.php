<?php

namespace SportTrackerConnector\Tracker\Endomondo;

use DateTime;
use GuzzleHttp\Client;
use RuntimeException;
use SportTrackerConnector\Tracker\AbstractTracker;
use SportTrackerConnector\Tracker\TrackerListWorkoutsResult;
use SportTrackerConnector\Workout\Workout;
use SportTrackerConnector\Workout\Workout\Extension\HR;
use SportTrackerConnector\Workout\Workout\Track;
use SportTrackerConnector\Workout\Workout\TrackPoint;

/**
 * Endomondo tracker.
 */
class Endomondo extends AbstractTracker
{

    /**
     * The Endomondo API.
     *
     * @var EndomondoAPI
     */
    protected $endomondoAPI;

    /**
     * Get the ID of the tracker.
     *
     * @return string
     */
    public static function getID()
    {
        return 'endomondo';
    }

    /**
     * Get a list of workouts.
     *
     * @param DateTime $startDate The start date for the workouts.
     * @param DateTime $endDate The end date for the workouts.
     * @return \SportTrackerConnector\Tracker\TrackerListWorkoutsResult[]
     * @throws RuntimeException If the start date time of a workout is not valid.
     */
    public function listWorkouts(DateTime $startDate, DateTime $endDate)
    {
        $list = array();
        $this->logger->debug('Downloading JSON of workouts.');
        $data = $this->getEndomondoAPI()->listWorkouts($startDate, $endDate);
        $this->logger->debug('Parsing data.');
        foreach ($data['data'] as $workout) {
            $startDateTime = DateTime::createFromFormat('Y-m-d H:i:s \U\T\C', $workout['start_time']);
            if ($startDateTime === false) {
                throw new RuntimeException('The workout "' . $workout['id'] . '" start date time is not valid.');
            }
            $list[] = new TrackerListWorkoutsResult($workout['id'], $this->getSportMapper()->getSportFromCode($workout['sport']), $startDateTime);
        }

        return $list;
    }

    /**
     * Get the Endomondo API.
     *
     * @return EndomondoAPI
     */
    public function getEndomondoAPI()
    {
        if ($this->endomondoAPI === null) {
            $client = new Client();
            $this->endomondoAPI = new EndomondoAPI($client, $this->username, $this->password, $this->getSportMapper());
        }

        return $this->endomondoAPI;
    }

    /**
     * Download a workout.
     *
     * @param integer $idWorkout The ID of the workout to download.
     * @return Workout
     */
    public function downloadWorkout($idWorkout)
    {
        $this->logger->debug('Downloading JSON for workout "' . $idWorkout . '"');

        $json = $this->getEndomondoAPI()->getWorkout($idWorkout);

        $workout = new Workout();
        $track = new Track();

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
     * Fetch the HTML page of a workout.
     *
     * @param Workout $workout The workout to upload.
     * @return boolean
     */
    public function uploadWorkout(Workout $workout)
    {
        $this->logger->debug('Uploading workout.');

        $workoutId = $this->getEndomondoAPI()->postWorkout($workout);
        return $workoutId !== null;
    }

    /**
     * Construct the sport mapper.
     *
     * @return \SportTrackerConnector\Workout\Workout\SportMapperInterface
     */
    protected function constructSportMapper()
    {
        return new Sport();
    }
}
