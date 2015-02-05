<?php

namespace SportTrackerConnector\Tracker\Polar;

use BadMethodCallException;
use DateTime;
use GuzzleHttp\Client;
use RuntimeException;
use SportTrackerConnector\Tracker\AbstractTracker;
use SportTrackerConnector\Workout\Loader\TCX;
use SportTrackerConnector\Workout\Workout;

/**
 * Polar Flow tracker.
 */
class Polar extends AbstractTracker
{

    const POLAR_FLOW_URL_ROOT = 'https://flow.polar.com';

    const POLAR_FLOW_URL_LOGIN = 'https://flow.polar.com/login';

    const POLAR_FLOW_URL_WORKOUT = 'https://flow.polar.com/training/analysis/%s/export/tcx';

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
        throw new BadMethodCallException('Polar Flow does not support workout listing.');
    }

    /**
     * Download a workout.
     *
     * @param integer $idWorkout The ID of the workout to download.
     * @return Workout
     */
    public function downloadWorkout($idWorkout)
    {
        $this->logger->debug('Downloading TCX for workout "' . $idWorkout . '"');

        $loader = new TCX();
        $polarWorkoutTCX = $this->fetchWorkoutTCX($idWorkout);
        return $loader->fromString($polarWorkoutTCX);
    }

    /**
     * Fetch the HTML page of a workout.
     *
     * @param integer $idWorkout The ID of the workout.
     * @return string
     * @throws RuntimeException If the login fails.
     */
    public function fetchWorkoutTCX($idWorkout)
    {
        $this->logger->debug('Logging into polar.');
        $client = $this->loginIntoPolar();

        $this->logger->debug('Fetching the workout TCX.');

        $workoutURL = sprintf(self::POLAR_FLOW_URL_WORKOUT, $idWorkout);
        $tempWorkoutZipFile = tempnam(sys_get_temp_dir(), 'stc_polar_workout_');
        $this->logger->debug('Downloading zip file with workout to "' . $tempWorkoutZipFile . '"');

        $response = $client->get($workoutURL, ['cookies' => true]);
        file_put_contents($tempWorkoutZipFile, $response->getBody());

        return $this->getTCXFromPolarZipArchive($tempWorkoutZipFile);
    }

    /**
     * Login to polar website.
     *
     * @return Client
     */
    public function loginIntoPolar()
    {
        $client = new Client();
        $client->post(
            self::POLAR_FLOW_URL_LOGIN,
            array(
                'body' => array('email' => $this->username, 'password' => $this->password),
                'cookies' => true
            )
        );

        return $client;
    }

    /**
     * Get the TCX content from the zip file downloaded from Polar.
     *
     * @param string $zipFile The zip file containing the workout.
     * @return string
     * @throws \RuntimeException If the zip file is corrupted or can not read the file from it.
     */
    private function getTCXFromPolarZipArchive($zipFile)
    {
        $zipArchive = new \ZipArchive();
        $open = $zipArchive->open($zipFile);
        if ($open !== true) {
            throw new \RuntimeException('Could not open the zip file acquired from Polar. File might be corrupted.');
        }
        $data = $zipArchive->getFromIndex(0);
        if ($data === false) {
            throw new RuntimeException('There is no file in the zip from Polar.');
        }

        return $data;
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
}
