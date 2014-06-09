<?php

namespace FitnessTrackingPorting\Tracker\Endomondo;

use FitnessTrackingPorting\Workout\Workout;
use FitnessTrackingPorting\Workout\Workout\Track;
use FitnessTrackingPorting\Workout\Workout\TrackPoint;
use FitnessTrackingPorting\Workout\Workout\Extension\HR;
use GuzzleHttp\Client;
use GuzzleHttp\Query;
use DateInterval;
use RuntimeException;

/**
 * Class for working with Endomondo API.
 */
class EndomondoAPI
{

    const URL_BASE = 'https://api.mobile.endomondo.com/mobile';
    const URL_AUTHENTICATE = 'https://api.mobile.endomondo.com/mobile/auth';
    const URL_WORKOUTS = 'https://api.mobile.endomondo.com/mobile/api/workouts';
    const URL_WORKOUT_GET = 'https://api.mobile.endomondo.com/mobile/api/workout/get';
    const URL_WORKOUT_POST = 'https://api.mobile.endomondo.com/mobile/api/workout/post';
    const URL_TRACK = 'https://api.mobile.endomondo.com/mobile/track';
    const URL_FRIENDS = 'https://api.mobile.endomondo.com/mobile/friends';

    const UUID = '27132407-5b55-5863-b150-7925b8d092a2';

    /**
     * The HTTP client.
     *
     * @var \GuzzleHttp\Client
     */
    protected $httpClient;

    /**
     * Endomondo auth token.
     *
     * @var string
     */
    protected $authToken;

    /**
     * The logged in user.
     *
     * @var integer
     */
    protected $userID;

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
     * @param Client $client The HTTP client.
     * @param string $username Username for polar.
     * @param string $password Password for polar.
     */
    public function __construct(Client $client, $username, $password)
    {
        $this->httpClient = $client;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Fetch the authentication token.
     *
     * @throws \RuntimeException If the authentication fails.
     */
    private function fetchAuthenticationToken()
    {
        $url = $this->buildGETUrl(
            self::URL_AUTHENTICATE,
            array(
                'action' => 'pair',
                'deviceId' => self::UUID,
                'country' => 'GB',
                'email' => $this->username,
                'password' => $this->password
            )
        );

        $response = $this->httpClient->get($url);

        $lines = explode("\n", $response->getBody());
        if ($lines[0] === 'OK') {
            foreach ($lines as $line) {
                $line = explode('=', $line, 2);
                switch ($line[0]) {
                    case 'authToken':
                        $this->authToken = trim($line[1]);
                        break;
                    case 'userId';
                        $this->userID = trim($line[1]);
                        break;
                }
            }
        }

        if ($this->authToken == null) {
            throw new RuntimeException('Authentication on endomondo failed.');
        }
    }

    /**
     * If you already have a token set it here to skip over the authentication.
     *
     * @param string $token The token.
     */
    public function setAuthToken($token)
    {
        $this->authToken = $token;
    }

    /**
     * Get the auth token.
     *
     * @return string
     */
    public function getAuthToken()
    {
        if ($this->authToken == null) {
            $this->fetchAuthenticationToken();
        }

        return $this->authToken;
    }

    /**
     * Get the details of a workout.
     *
     * Possible fields when getting the workout: device,simple,basic,motivation,interval,weather,polyline_encoded_small,points,lcp_count,tagged_users,pictures.
     *
     * @param integer $idWorkout The ID of the workout.
     * @return array
     * @throws \RuntimeException If the workout can not be fetched.
     */
    public function getWorkout($idWorkout)
    {
        $url = $this->buildGETUrl(
            self::URL_WORKOUT_GET,
            array(
                'authToken' => $this->getAuthToken(),
                'fields' => 'device,simple,basic,motivation,interval,weather,polyline_encoded_small,points,lcp_count,tagged_users,pictures',
                'workoutId' => $idWorkout
            )
        );

        $response = $this->httpClient->get($url);

        if ($response->getStatusCode() == 200) {
            // TODO find a new way of fetching the workout as this does not return all points.
            return $response->json();
        } else {
            throw new RuntimeException('Could not get workout "' . $idWorkout . '".');
        }
    }

    /**
     * Post a workout to endomondo.
     *
     * Each track of a workout is uploaded individually.
     *
     * @param Workout $workout
     * @return array IDs of the workouts posted on endomondo.
     */
    public function postWorkout(Workout $workout)
    {
        $workoutIds = array();
        foreach ($workout->getTracks() as $track) {
            $workoutIds[] = $this->postTrack($track);
        }

        return $workoutIds;
    }

    /**
     * Post one workout track to endomondo.
     *
     * @param Track $track
     * @return integer
     * @throws \RuntimeException If the uploading stops at one point.
     */
    private function postTrack(Track $track)
    {
        $deviceWorkoutId = '-' . $this->bigRandomNumber(19);
        $sport = Sport::getCodeFromSport($track->getSport());
        $duration = $this->convertDateIntervalInSeconds($track->getDuration());

        $workoutId = null;
        $previousPoint = null;
        $distance = 0;
        $speed = 0;
        // Split in chunks of 100 points like the mobile app.
        foreach (array_chunk($track->getTrackPoints(), 100) as $trackPoints) {
            $data = array();
            foreach ($trackPoints as $trackPoint) {
                if ($previousPoint !== null) {
                    $distance += $trackPoint->distance($previousPoint);
                    $speed = $trackPoint->speed($previousPoint);
                }
                $data[] = $this->flattenTrackPoint($trackPoint, $distance, $speed);
                $previousPoint = $trackPoint;
            }

            $url = $this->buildGETUrl(
                self::URL_TRACK,
                array(
                    'authToken' => $this->getAuthToken(),
                    'workoutId' => $deviceWorkoutId,
                    'sport' => $sport,
                    'duration' => $duration,
                    'gzip' => 'true',
                    'audioMessage' => 'true',
                    'goalType' => 'BASIC',
                    'extendedResponse' => 'true'
                )
            );

            $response = $this->httpClient->post(
                $url,
                array(
                    'headers' => array(
                        'Content-Type' => 'application/octet-stream'
                    ),
                    'body' => gzencode(implode("\n", $data))
                )
            );

            $responseLines = explode("\n", $response->getBody());
            if ($responseLines[0] !== 'OK') {
                throw new RuntimeException('Response back unexpected. Data may be partially uploaded. Response: ' . $response->getBody());
            }

            $workoutId = explode('=', $responseLines[1])[1];
        }

        return $workoutId;
    }

    /**
     * Flatten a track point to be posted on endomondo.
     *
     * @param TrackPoint $trackPoint The track point to flatten.
     * @param float $distance The total distance the point in meters.
     * @param float $speed The speed the point in km/h from the previous point.
     * @return string
     */
    private function flattenTrackPoint(TrackPoint $trackPoint, $distance, $speed)
    {
        $dateTime = clone $trackPoint->getDateTime();
        $dateTime->setTimezone(new \DateTimeZone('UTC'));
        return sprintf(
            '%s;2;%s;%s;%s;%s;%s;%s;',
            $dateTime->format('Y-m-d H:i:s \U\T\C'),
            $trackPoint->getLatitude(),
            $trackPoint->getLongitude(),
            $distance / 1000,
            $speed,
            $trackPoint->getElevation(),
            $trackPoint->hasExtension(HR::ID) ? $trackPoint->getExtension(HR::ID)->getValue() : ''
        );
    }

    /**
     * Build an URL.
     *
     * @param string $baseUrl The base URL.
     * @param array $parameters Parameters for the URL.
     * @return string
     */
    private function buildGETUrl($baseUrl, array $parameters)
    {
        $query = new Query();
        foreach ($parameters as $key => $value) {
            $query->add($key, $value);
        }

        return $baseUrl . '?' . (string)$query;
    }

    /**
     * Generate a big number of specified length.
     *
     * @param integer $randNumberLength The length of the number.
     * @return string
     */
    private function bigRandomNumber($randNumberLength)
    {
        $randNumber = null;

        for ($i = 0; $i < $randNumberLength; $i++) {
            $randNumber .= rand(0, 9);
        }

        return $randNumber;
    }

    /**
     * Get the number of seconds from a DateInterval.
     *
     * @param DateInterval $dateInterval The date interval to convert.
     * @return integer
     */
    private function convertDateIntervalInSeconds(DateInterval $dateInterval)
    {
        return $dateInterval->days * 86400 + $dateInterval->h * 3600 + $dateInterval->i * 60 + $dateInterval->s;
    }
} 