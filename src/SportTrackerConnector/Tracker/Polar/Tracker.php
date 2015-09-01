<?php

namespace SportTrackerConnector\Tracker\Polar;

use BadMethodCallException;
use DateTime;
use GuzzleHttp\Client;
use SportTrackerConnector\Core\Workout\SportMapperInterface;
use SportTrackerConnector\Core\Tracker\AbstractTracker;
use SportTrackerConnector\Core\Tracker\TrackerListWorkoutsResult;
use SportTrackerConnector\Core\Workout\Loader\TCX;
use SportTrackerConnector\Core\Workout\Workout;

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
     * {@inheritdoc}
     */
    public static function getID()
    {
        return 'polar';
    }

    /**
     * {@inheritdoc}
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
                SportMapperInterface::OTHER,
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
     * {@inheritdoc}
     */
    public function uploadWorkout(Workout $workout)
    {
        throw new BadMethodCallException('Polar Flow does not support workout upload.');
    }

    /**
     * {@inheritdoc}
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
