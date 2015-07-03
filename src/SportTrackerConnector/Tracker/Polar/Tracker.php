<?php

namespace SportTrackerConnector\Tracker\Polar;

use BadMethodCallException;
use DateTime;
use GuzzleHttp\Client;
use SportTrackerConnector\Tracker\AbstractTracker;
use SportTrackerConnector\Tracker\TrackerListWorkoutsResult;
use SportTrackerConnector\Workout\Loader\TCX;
use SportTrackerConnector\Workout\Workout;

/**
 * Polar Flow tracker.
 */
class Tracker extends AbstractTracker
{

    /**
     * The Endomondo API.
     *
     * @var API
     */
    protected $polarAPI;

    /**
     * Get the ID of the tracker.
     *
     * @return string
     */
    public static function getID()
    {
        return 'polar';
    }

    /**
     * Get a list of workouts.
     *
     * @param DateTime $startDate The start date for the workouts.
     * @param DateTime $endDate The end date for the workouts.
     * @return \SportTrackerConnector\Tracker\TrackerListWorkoutsResult[]
     * @throws BadMethodCallException Functionality yet not supported.
     */
    public function listWorkouts(DateTime $startDate, DateTime $endDate)
    {
        $list = array();

        $this->logger->debug('Downloading calendar events.');
        $data = $this->getPolarAPI()->listCalendarEvents($startDate, $endDate);

        $this->logger->debug('Parsing data.');
        foreach ($data as $workout) {
            if ($workout['type'] !== 'EXERCISE') {
                continue;
            }

            $list[] = new TrackerListWorkoutsResult(
                $workout['listItemId'],
                Workout\SportMapperInterface::OTHER,
                new DateTime('@' . $workout['start'])
            );
        }

        return $list;
    }

    /**
     * Download a workout from a TCX.
     *
     * @param integer $idWorkout The ID of the workout to download.
     * @return Workout
     */
    public function downloadWorkout($idWorkout)
    {
        $this->logger->debug('Downloading TCX for workout "' . $idWorkout . '"');

        $loader = new TCX();
        $polarWorkoutTCX = $this->getPolarAPI()->fetchWorkoutTCX($idWorkout);
        return $loader->fromString($polarWorkoutTCX);
    }

    /**
     * Download a workout in CSV format.
     *
     * @param integer $idWorkout The ID of the workout to download.
     * @return Workout
     */
    public function downloadCSVWorkout($idWorkout)
    {
        $this->logger->debug('Downloading CSV for workout "' . $idWorkout . '"');

        return $this->getPolarAPI()->fetchWorkoutCSV($idWorkout);
    }

    /**
     * Upload a workout.
     *
     * @param Workout $workout The workout to upload.
     * @return boolean
     * @throws BadMethodCallException Functionality not supported.
     */
    public function uploadWorkout(Workout $workout)
    {
        throw new BadMethodCallException('Polar Flow does not support workout upload.');
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

    /**
     * Get the Endomondo API.
     *
     * @return API
     */
    public function getPolarAPI()
    {
        if ($this->polarAPI === null) {
            $client = new Client();
            $this->polarAPI = new API($client, $this->username, $this->password, $this->getSportMapper());
        }

        return $this->polarAPI;
    }
}
