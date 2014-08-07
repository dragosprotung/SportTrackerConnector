<?php

namespace SportTrackerConnector\Tracker\Polar;

use BadMethodCallException;
use DateTime;
use GuzzleHttp\Client;
use RuntimeException;
use SportTrackerConnector\Tracker\AbstractTracker;
use SportTrackerConnector\Workout\Workout\Extension\HR;
use SportTrackerConnector\Workout\Workout;
use SportTrackerConnector\Workout\Workout\Track;
use SportTrackerConnector\Workout\Workout\TrackPoint;

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
        $this->logger->debug('Downloading JSON for workout "' . $idWorkout . '"');

        $html = $this->fetchWorkoutHTML($idWorkout);
        return $this->fetchWorkoutFromHTML($html);
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
     * Get a workout from a Polar workout HTML page.
     *
     * @param string $html The HTML to fetch from.
     * @return Workout
     */
    public function fetchWorkoutFromHTML($html)
    {
        $workout = new Workout();

        $this->logger->debug('Parsing the HTML and extracting the data in JSON format.');
        $altitudeSamples = $this->extractAltitudeSamples($html);
        $polarExercise = $this->parseExerciseFromHTMLToJSON($html);

        $this->logger->debug('Building the workout from JSON data.');

        foreach ($polarExercise['exercises'] as $idExercise => $exercise) {
            $exercise = array_combine(array_keys((array)$polarExercise['ExerciseKeys']), $exercise);
            if ($exercise['HAS_SAMPLES'] === true) {
                $track = new Track();

                $sport = $this->parseWorkoutSportFromHTML($html, $idExercise);
                $track->setSport($sport);
                for ($i = $exercise['SAMPLES_START_INDEX']; $i <= $exercise['SAMPLES_STOP_INDEX']; $i++) {
                    $point = $polarExercise['samples'][$i];

                    $unixTime = substr($point[1], 0, -3); // time is represented in milliseconds.
                    $time = new DateTime('@' . $unixTime);
                    // Time is a UNIX timestamp so we have to set the timezone after we create the DateTime.
                    $time->setTimezone($this->getTimeZone());
                    // Adjust for time zone.
                    $time->modify($this->getTimeZoneOffset() . ' seconds');

                    $trackPoint = new TrackPoint($point[0]['lat'], $point[0]['lon'], $time);
                    if (isset($altitudeSamples[$idExercise])) {
                        $trackPoint->setElevation($this->extractElevationFromAltitudeSample($altitudeSamples[$idExercise], $unixTime));
                    }
                    $trackPoint->addExtension(new HR($point[3]));
                    $track->addTrackPoint($trackPoint);
                }

                $workout->addTrack($track);
            }
        }

        return $workout;
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

        return \GuzzleHttp\json_decode($matches[1], true);
    }

    /**
     * Extract altitude samples for the exercises.
     *
     * @param string $html The HTML to parse.
     * @return array
     */
    private function extractAltitudeSamples($html)
    {
        $altitudeSamples = array();

        $patternSingleWorkout = '/var curve = new Curve\((.*)true\}\);/s';
        $patternMultiWorkout = '/var curve = new Curve\((.*)\]\}\);/s';
        if (preg_match($patternSingleWorkout, $html, $matches)) {
            $match = $matches[1] . 'true}';
            $altitudeSamples = $this->parseAltitudeSamples($match);
        } elseif (preg_match($patternMultiWorkout, $html, $matches)) {
            $match = $matches[1] . ']}';
            $altitudeSamples = $this->parseAltitudeSamples($match);
        } else {
            $this->logger->warning('Could not extract altitude from the workout.');
        }

        return $altitudeSamples;
    }

    /**
     * Parse the json string and extract altitude samples.
     *
     * @param string $match The matched part of the HTML (json) from which to extract the data.
     * @return array
     */
    private function parseAltitudeSamples($match) {
        $altitudeSamples = array();

        $jsonData = \GuzzleHttp\json_decode($match, true);
        if (isset($jsonData['exercises'])) {
            foreach ($jsonData['exercises'] as $exercise) {
                if (isset($exercise['durationBasedSamples']['ALTITUDE'][0])) {
                    $exerciseData = array();
                    foreach ($exercise['durationBasedSamples']['ALTITUDE'][0] as $altitude) {
                        $exerciseData[] = array('time' => substr($altitude[0], 0, -3), 'elevation' => $altitude[1]);
                    }

                    $altitudeSamples[$exercise['id']] = $exerciseData;
                }
            }
        }

        return $altitudeSamples;
    }

    /**
     * Get the elevation from the samples.
     *
     * @param array $exerciseAltitudeSample All the altitude samples for the exercise.
     * @param string $unixTime The point in time to fetch the elevation in unix format (timestamp).
     * @return null|string
     */
    private function extractElevationFromAltitudeSample(array $exerciseAltitudeSample, $unixTime)
    {
        foreach ($exerciseAltitudeSample as $sample) {
            if ($unixTime <= $sample['time']) {
                return sprintf("%.2F", $sample['elevation']);
            }
        }

        return null;
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

        foreach ($json->exercises as $exercise) {
            if ($exercise->id == $idExercise) {
                return $this->getSportMapper()->getSportFromCode($exercise->sport->name);
            }
        }

        return Sport::OTHER;
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