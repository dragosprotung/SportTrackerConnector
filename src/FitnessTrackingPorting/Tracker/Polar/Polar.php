<?php

namespace FitnessTrackingPorting\Tracker\Polar;

use FitnessTrackingPorting\Tracker\TrackerInterface;
use FitnessTrackingPorting\Workout\Workout;
use FitnessTrackingPorting\Workout\Workout\TrackPoint;
use FitnessTrackingPorting\Workout\Workout\Extension\HR;
use DateTime;
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;
use RuntimeException;
use BadMethodCallException;
use Symfony\Component\DomCrawler\Form;

/**
 * Polar Flow tracker.
 */
class Polar implements TrackerInterface
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
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Get a new instance using a config array.
     *
     * @param array $config The config for the new instance.
     * @return Polar
     */
    public static function fromConfig(array $config)
    {
        return new static($config['username'], $config['password']);
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
    protected function fetchWorkoutFromHTML($html)
    {
        $workout = new Workout();

        $sport = $this->parseWorkoutSportFromHTML($html);
        $workout->setSport($sport);

        $trackPoints = $this->parseTrackPointsFromHTMLToJSON($html);
        foreach ($trackPoints as $point) {
            $time = new DateTime('@' . substr($point[1], 0, -3));
            $trackPoint = new TrackPoint($point[0]->lat, $point[0]->lon, $time);
            $trackPoint->addExtension(new HR($point[3]));
            $workout->addTrackPoint($trackPoint);
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

        $client->request('POST', self::POLAR_FLOW_URL_LOGIN, array('email' => $this->username, 'password' => $this->password));
        if ($client->getResponse()->getStatus() != 200) {
            throw new RuntimeException('Could not login to Polar Flow. ' . $client->getResponse()->getContent());
        }

        $workoutURL = sprintf(self::POLAR_FLOW_URL_WORKOUT, $idWorkout);
        $crawler = $client->request('GET', $workoutURL);

        return $crawler->html();
    }

    /**
     * Extract the workout sport.
     *
     * @param string $html The HTML to parse.
     * @return string
     */
    protected function parseWorkoutSportFromHTML($html)
    {
        $crawler = new Crawler();
        $crawler->addContent($html);

        $pageForm = iterator_to_array($crawler->filter('form'))[0];
        $form = new Form($pageForm, self::POLAR_FLOW_URL_ROOT);
        $values = $form->getValues();
        return Sport::getSportFromCode($values['sport']);
    }

    /**
     * Extract the track data from HTML.
     *
     * @param string $html The HTML to parse.
     * @return array
     * @throws RuntimeException If the JSON can not be parsed.
     */
    protected function parseTrackPointsFromHTMLToJSON($html)
    {
        $crawler = new Crawler();
        $crawler->addContent($html);
        $json = $crawler->filterXPath('//script')->last()->text();

        $pattern = '/var mapSection = (.*)"samples":(.*)\}\,(.*)publicExercise\);/';
        preg_match($pattern, $json, $matches);

        $json = json_decode($matches[2]);
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