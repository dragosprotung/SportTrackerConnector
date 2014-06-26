<?php

namespace FitnessTrackingPorting\Tracker\Polar;

use FitnessTrackingPorting\Tracker\AbstractTracker;
use FitnessTrackingPorting\Workout\Workout;
use FitnessTrackingPorting\Workout\Workout\Track;
use FitnessTrackingPorting\Workout\Workout\TrackPoint;
use FitnessTrackingPorting\Workout\Workout\Extension\HR;
use DateTime;
use GuzzleHttp\Client;
use RuntimeException;
use BadMethodCallException;

/**
 * Polar Flow tracker.
 */
class Polar extends AbstractTracker
{

    const POLAR_FLOW_URL_ROOT = 'https://flow.polar.com';

    const POLAR_FLOW_URL_LOGIN = 'https://flow.polar.com/login';

    const POLAR_FLOW_URL_WORKOUT = 'https://flow.polar.com/training/analysis/%s';

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
     * Download a workout.
     *
     * @param integer $idWorkout The ID of the workout to download.
     * @return Workout
     */
    public function downloadWorkout($idWorkout)
    {
        $this->logger->debug('Downloading JSON for workout "' . $idWorkout . '"');

        $html = $this->fetchWorkoutHTML($idWorkout);
        return $this->fetchWorkoutFromHTML($html);
    }

    /**
     * Get a workout from a Polar workout HTML page.
     *
     * @param string $html The HTML to fetch from.
     * @return Workout
     */
    public function fetchWorkoutFromHTML($html)
    {
        $workout = new Workout();

        $this->logger->debug('Parsing the HTML and extracting the data in JSON format.');
        $polarExercise = $this->parseExerciseFromHTMLToJSON($html);

        $this->logger->debug('Building the workout from JSON data.');

        foreach ($polarExercise->exercises as $idExercise => $exercise) {
            $exercise = array_combine(array_keys((array)$polarExercise->ExerciseKeys), $exercise);
            if ($exercise['HAS_SAMPLES'] === true) {
                $track = new Track();

                $sport = $this->parseWorkoutSportFromHTML($html, $idExercise);
                $track->setSport($sport);
                for ($i = $exercise['SAMPLES_START_INDEX']; $i <= $exercise['SAMPLES_STOP_INDEX']; $i++) {
                    $point = $polarExercise->samples[$i];
                    $time = new DateTime('@' . substr($point[1], 0, -3));
                    // Time is a UNIX timestamp so we have to set the timezone after we create the DateTime.
                    $time->setTimezone($this->getTimeZone());
                    // Adjust for time zone.
                    $time->modify($this->getTimeZoneOffset() . ' seconds');

                    $trackPoint = new TrackPoint($point[0]->lat, $point[0]->lon, $time);
                    $trackPoint->addExtension(new HR($point[3]));
                    $track->addTrackPoint($trackPoint);
                }

                $workout->addTrack($track);
            }
        }

        return $workout;
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
     * Fetch the HTML page of a workout.
     *
     * @param integer $idWorkout The ID of the workout.
     * @return string
     * @throws RuntimeException If the login fails.
     */
    protected function fetchWorkoutHTML($idWorkout)
    {
        $this->logger->debug('Logging into polar.');

        $client = new Client();
        $client->post(
            self::POLAR_FLOW_URL_LOGIN,
            array(
                'body' => array('email' => $this->username, 'password' => $this->password),
                'cookies' => true
            )
        );

        $this->logger->debug('Fetching the workout HTML page.');

        $workoutURL = sprintf(self::POLAR_FLOW_URL_WORKOUT, $idWorkout);
        $response = $client->get($workoutURL, ['cookies' => true]);

        return (string)$response->getBody();
    }

    /**
     * Extract the workout sport.
     *
     * @param string $html The HTML to parse.
     * @param integer $idExercise The ID of the exercise.
     * @return string
     * @throws RuntimeException If the JSON can not be parsed.
     */
    protected function parseWorkoutSportFromHTML($html, $idExercise)
    {
        $pattern = '/var curve = new Curve\(([^\)]+)\);/s';
        preg_match($pattern, $html, $matches);

        $json = \GuzzleHttp\json_decode($matches[1]);

        $code = null;
        foreach ($json->exercises as $exercise) {
            if ($exercise->id == $idExercise) {
                return $this->getSportMapper()->getSportFromCode($exercise->sport->name);
            }
        }

        return Sport::OTHER;
    }

    /**
     * Extract the track data from HTML.
     *
     * @param string $html The HTML to parse.
     * @return array
     * @throws RuntimeException If the JSON can not be parsed.
     */
    private function parseExerciseFromHTMLToJSON($html)
    {
        $pattern = '/var mapSection = new MapSection\((.*)\,(.*)publicExercise\);/s';
        preg_match($pattern, $html, $matches);

        return \GuzzleHttp\json_decode($matches[1]);
    }

    /**
     * Construct the sport mapper.
     *
     * @return \FitnessTrackingPorting\Workout\Workout\SportMapperInterface
     */
    protected function constructSportMapper()
    {
        return new Sport();
    }
}