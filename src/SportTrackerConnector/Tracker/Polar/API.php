<?php

namespace SportTrackerConnector\Tracker\Polar;

use DateTime;
use GuzzleHttp\Client;
use RuntimeException;
use SportTrackerConnector\Workout\Workout\SportMapperInterface;

/**
 * Class for working with Polar API.
 */
class API
{

    const POLAR_FLOW_URL_ROOT = 'https://flow.polar.com';

    const POLAR_FLOW_URL_LOGIN = 'https://flow.polar.com/login';

    const POLAR_FLOW_URL_WORKOUTS = 'https://flow.polar.com/training/getCalendarEvents?start=%s&end=%s';

    const POLAR_FLOW_URL_WORKOUT = 'https://flow.polar.com/training/analysis/%s/export/%s/false';

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
     * The sport mapper.
     *
     * @var \SportTrackerConnector\Workout\Workout\SportMapperInterface
     */
    protected $sportMapper;

    /**
     * The HTTP client.
     *
     * @var \GuzzleHttp\ClientInterface
     */
    private $httpClient;

    /**
     * Constructor.
     *
     * @param Client $client The HTTP client.
     * @param string $username Username for polar website.
     * @param string $password Password for polar website.
     * @param SportMapperInterface $sportMapper The sport mapper.
     */
    public function __construct(Client $client, $username, $password, SportMapperInterface $sportMapper)
    {
        $this->httpClient = $client;
        $this->username = $username;
        $this->password = $password;
        $this->sportMapper = $sportMapper;

        $this->loginIntoPolar();
    }

    /**
     * Login to polar website.
     */
    private function loginIntoPolar()
    {
        $this->httpClient->post(
            self::POLAR_FLOW_URL_LOGIN,
            array(
                'body' => array('email' => $this->username, 'password' => $this->password),
                'cookies' => true
            )
        );
    }

    /**
     * Get a list of all calendar events in a date interval.
     *
     * @param DateTime $startDate The start date for the events.
     * @param DateTime $endDate The end date for the events.
     * @return array
     * @throws \RuntimeException If the request does not return the expected data.
     */
    public function listCalendarEvents(DateTime $startDate, DateTime $endDate)
    {
        $url = sprintf(self::POLAR_FLOW_URL_WORKOUTS, $startDate->format('d.m.Y'), $endDate->format('d.m.Y'));

        try {
            $response = $this->httpClient->get($url, ['cookies' => true]);

            if ($response->getStatusCode() === 200) {
                return $response->json();
            }

            throw new \Exception('Unexpected "' . $response->getStatusCode() . '"response code from Endomondo.');
        } catch (\Exception $e) {
            throw new RuntimeException('Could not list events.', null, $e);
        }
    }

    /**
     * Fetch the TCX content of a workout.
     *
     * @param integer $idWorkout The ID of the workout.
     * @return string
     * @throws RuntimeException If the login fails.
     */
    public function fetchWorkoutTCX($idWorkout)
    {
        $workoutURL = sprintf(self::POLAR_FLOW_URL_WORKOUT, $idWorkout, 'tcx');
        $response = $this->httpClient->get($workoutURL, ['cookies' => true]);

        return (string) $response->getBody(true);
    }

    /**
     * Fetch the CSV content of a workout.
     *
     * @param integer $idWorkout The ID of the workout.
     * @return string
     * @throws RuntimeException If the login fails.
     */
    public function fetchWorkoutCSV($idWorkout)
    {
        $workoutURL = sprintf(self::POLAR_FLOW_URL_WORKOUT, $idWorkout, 'csv');
        $response = $this->httpClient->get($workoutURL, ['cookies' => true]);

        return (string) $response->getBody(true);
    }
}
