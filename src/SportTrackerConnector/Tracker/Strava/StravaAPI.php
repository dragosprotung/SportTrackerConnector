<?php

namespace SportTrackerConnector\Tracker\Strava;

use DateTime;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Post\PostFile;
use RuntimeException;
use SportTrackerConnector\Tracker\Exception\NoTrackPointsFoundException;
use SportTrackerConnector\Workout\Dumper\TCX as TCXDumper;
use SportTrackerConnector\Workout\Workout;
use SportTrackerConnector\Workout\Workout\SportMapperInterface;

/**
 * Class for working with Strava API.
 */
class StravaAPI
{

    const STRAVA_URL_WORKOUTS = 'https://www.strava.com/api/v3/activities';

    const STRAVA_URL_WORKOUTS_LIST = 'https://www.strava.com/api/v3/athlete/activities';

    const STRAVA_URL_WORKOUT_SUMMARY = 'https://www.strava.com/api/v3/activities/%s';

    const STRAVA_URL_WORKOUT_DETAILS = 'https://www.strava.com/api/v3/activities/%s/streams/time,latlng,altitude,heartrate';

    const STRAVA_URL_WORKOUT_POST = 'https://www.strava.com/api/v3/uploads';

    /**
     * The HTTP client.
     *
     * @var \GuzzleHttp\ClientInterface
     */
    private $httpClient;

    /**
     * The Strava API access token.
     *
     * @var string
     */
    protected $accessToken;

    /**
     * Constructor.
     *
     * @param ClientInterface $client The HTTP client.
     * @param string $accessToken The Strava API access token.
     * @param SportMapperInterface $sportMapper The sport mapper.
     */
    public function __construct(ClientInterface $client, $accessToken, SportMapperInterface $sportMapper)
    {
        $this->httpClient = $client;
        $this->accessToken = $accessToken;
        $this->sportMapper = $sportMapper;

        $this->httpClient->setDefaultOption('headers/Authorization', 'Bearer ' . $this->accessToken);
    }

    /**
     * Get the details of a workout.
     *
     * @param integer $idWorkout The ID of the workout.
     * @return array
     * @throws \RuntimeException If the workout can not be fetched.
     */
    public function getWorkout($idWorkout)
    {
        $workoutSummaryURL = sprintf(self::STRAVA_URL_WORKOUT_SUMMARY, $idWorkout);
        $workoutDetailsURL = sprintf(self::STRAVA_URL_WORKOUT_DETAILS, 11111);
        try {
            $workoutSummary = $this->httpClient->get($workoutSummaryURL)->json();

            $workoutStartDateTime = new DateTime($workoutSummary['start_date']);

            $workoutStreams = $this->httpClient->get($workoutDetailsURL)->json();

            $workoutDetails = array();
            foreach ($workoutStreams as $steam) {
                switch ($steam['type']) {
                    case 'time':
                        $workoutDetails[$steam['type']] = $this->processTimeStream($workoutStartDateTime, $steam['data']);
                        break;
                    case 'latlng':
                    case 'altitude':
                    case 'heartrate':
                        $workoutDetails[$steam['type']] = $steam['data'];
                        break;
                }
            }

            return $workoutDetails;
        } catch (ClientException $e) {
            if ($e->getCode() === 404 && $workoutDetailsURL === $e->getRequest()->getUrl()) {
                throw new NoTrackPointsFoundException('Workout "' . $idWorkout . '" has no track points.', null, $e);
            } elseif ($e->getCode() === 404) {
                throw new RuntimeException('Workout "' . $idWorkout . '" not found.', null, $e);
            }

            throw new RuntimeException('Could not get workout "' . $idWorkout . '".', null, $e);
        } catch (\Exception $e) {
            throw new RuntimeException('Could not get workout "' . $idWorkout . '".', null, $e);
        }
    }

    /**
     * Get a list of workouts in a date interval.
     *
     * @param DateTime $startDate The start date for the workouts.
     * @param DateTime $endDate The end date for the workouts.
     * @return array
     * @throws \RuntimeException If the request does not return the expected data.
     */
    public function listWorkouts(DateTime $startDate, DateTime $endDate)
    {
        $data = $this->httpClient->get(
            self::STRAVA_URL_WORKOUTS_LIST,
            [
                'query' => [
                    'before' => $endDate->getTimestamp(),
                    'after' => $startDate->getTimestamp(),
                    'per_page' => 200, // TODO Be lazy and fetch everything in one request. Make multiple request for proper listing.
                ]
            ]
        );

        return $data->json();
    }

    /**
     * Convert the time stream of a a workout to DateTime objects.
     *
     * @param DateTime $workoutStartDateTime The date time of the workout start.
     * @param array $data The stream data.
     * @return array
     */
    private function processTimeStream(DateTime $workoutStartDateTime, $data)
    {
        array_walk(
            $data,
            function (&$point) use ($workoutStartDateTime) {
                $point = new DateTime('@' . ($workoutStartDateTime->getTimestamp() + $point));
            }
        );

        return $data;
    }

    /**
     * Post a workout to endomondo.
     *
     * Each track of a workout is uploaded individually.
     *
     * @param Workout $workout The workout to post.
     * @return string Status message.
     * @throws \RuntimeException If there is a problem with the upload.
     */
    public function postWorkout(Workout $workout)
    {
        $tcx = new TCXDumper();
        $workoutFile = new PostFile('file', $tcx->dumpToString($workout), uniqid('SportTrackerConnectorWorkout_'));
        try {
            $response = $this->httpClient->post(
                self::STRAVA_URL_WORKOUT_POST,
                [
                    'body' => [
                        'data_type' => 'tcx',
                        'file' => $workoutFile
                    ]
                ]
            );

            $responseData = $response->json();

            if ($response->getStatusCode() !== '201' || $responseData['error'] !== null) {
                throw new RuntimeException('Strava returned error message: ' . $responseData['error'] . ' Status: ' . $responseData['status']);
            }

            return $responseData['status'];
        } catch (ClientException $e) {
            $response = $e->getResponse()->json();
            $errorMessage = 'Could not upload workout to strava.com.';
            if (isset($response['status'])) {
                $errorMessage .= ' ' . $response['status'] . ' Error: ' . $response['error'];
            }

            throw new RuntimeException($errorMessage, null, $e);
        }
    }
}
