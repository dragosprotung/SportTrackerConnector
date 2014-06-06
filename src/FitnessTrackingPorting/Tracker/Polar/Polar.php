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
     * Username for polar.
     *
     * @var string
     */
    protected $username;

    /**
     * Password for polar.
     *
     * @var string
     */
    protected $password;

    /**
     * Constructor.
     *
     * @param string $username Username for polar.
     * @param string $password Password for polar.
     */
    public function __construct($username, $password)
    {
        parent::__construct();

        $this->username = $username;
        $this->password = $password;
    }

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

        $polarExercise = $this->parseExerciseFromHTMLToJSON($html);
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
        $client = new Client();
        $client->post(
            self::POLAR_FLOW_URL_LOGIN,
            array(
                'body' => array('email' => $this->username, 'password' => $this->password),
                'cookies' => true
            )
        );

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

        $json = json_decode($matches[1]);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Could not parse the JSON from the HTML to extract workout sport. ' . static::getJSONLastErrorMessage());
        }

        $code = null;
        foreach ($json->exercises as $exercise) {
            if ($exercise->id == $idExercise) {
                return Sport::getSportFromCode($exercise->sport->name);
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
    protected function parseExerciseFromHTMLToJSON($html)
    {
        $pattern = '/var mapSection = new MapSection\((.*)\,(.*)publicExercise\);/s';
        preg_match($pattern, $html, $matches);

        $json = json_decode($matches[1]);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Could not parse the JSON from the HTML. ' . static::getJSONLastErrorMessage());
        }

        return $json;
    }

    /**
     * Get the last error message after decoding JSON.
     *
     * @return string
     */
    protected static function getJSONLastErrorMessage()
    {
        if (!function_exists('json_last_error_msg')) {
            function json_last_error_msg()
            {
                static $errors = array(
                    JSON_ERROR_NONE => null,
                    JSON_ERROR_DEPTH => 'Maximum stack depth exceeded.',
                    JSON_ERROR_STATE_MISMATCH => 'Underflow or the modes mismatch.',
                    JSON_ERROR_CTRL_CHAR => 'Unexpected control character found.',
                    JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON.',
                    JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded.'
                );

                $error = json_last_error();

                return array_key_exists($error, $errors) ? $errors[$error] : "Unknown error ({$error}).";
            }
        }

        return json_last_error_msg();
    }
}