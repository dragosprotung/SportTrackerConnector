<?php

namespace SportTrackerConnector\Tracker\Strava;

use DateTime;
use DateTimeZone;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use RuntimeException;
use SportTrackerConnector\Core\Tracker\AbstractTracker;
use SportTrackerConnector\Core\Tracker\Exception\NoTrackPointsFoundException;
use SportTrackerConnector\Core\Tracker\TrackerInterface;
use SportTrackerConnector\Core\Tracker\TrackerListWorkoutsResult;
use SportTrackerConnector\Core\Workout\Workout;
use SportTrackerConnector\Core\Workout\Extension\HR;
use SportTrackerConnector\Core\Workout\Track;
use SportTrackerConnector\Core\Workout\TrackPoint;

/**
 * Tracker for strava.com
 */
class Tracker extends AbstractTracker
{

    const STRAVA_URL_WORKOUTS = 'https://www.strava.com/api/v3/activities';

    const STRAVA_URL_WORKOUTS_LIST = 'https://www.strava.com/api/v3/athlete/activities';

    const STRAVA_URL_WORKOUT_SUMMARY = 'https://www.strava.com/api/v3/activities/%s';

    const STRAVA_URL_WORKOUT_DETAILS = 'https://www.strava.com/api/v3/activities/%s/streams/time,latlng,altitude,heartrate';

    const STRAVA_URL_WORKOUT_POST = 'https://www.strava.com/api/v3/uploads';

    /**
     * The Strava API access token.
     *
     * @var string
     */
    protected $accessToken;

    /**
     * The Strava API.
     *
     * @var \SportTrackerConnector\Tracker\Strava\API
     */
    protected $stravaAPI;

    /**
     * Constructor.
     *
     * @param LoggerInterface $logger The logger.
     * @param string $accessToken The Strava API access token.
     */
    public function __construct(LoggerInterface $logger, $accessToken)
    {
        $this->logger = $logger;
        $this->accessToken = $accessToken;

        $this->timeZone = new DateTimeZone('UTC');
    }

    /**
     * {@inheritdoc}
     */
    public static function fromConfig(LoggerInterface $logger, array $config)
    {
        $tracker = new static($logger, $config['auth']['accessToken']);

        $timeZone = new DateTimeZone($config['timezone']);
        $tracker->setTimeZone($timeZone);

        return $tracker;
    }

    /**
     * {@inheritdoc}
     */
    public static function getID()
    {
        return 'strava';
    }

    /**
     * {@inheritdoc}
     */
    public function listWorkouts(DateTime $startDate, DateTime $endDate)
    {
        $this->logger->debug('Downloading JSON of workouts.');
        $data = $this->getStravaAPI()->listWorkouts($startDate, $endDate);

        $this->logger->debug('Parsing data.');
        $list = array();
        foreach ($data as $workout) {
            $startDateTime = new DateTime($workout['start_date']);
            if ($startDateTime === false) {
                throw new RuntimeException('The workout "' . $workout['id'] . '" start date time is not valid.');
            }
            $list[] = new TrackerListWorkoutsResult(
                (string)$workout['id'],
                $this->getSportMapper()->getSportFromCode($workout['type']),
                $startDateTime
            );
        }

        return $list;
    }

    /**
     * {@inheritdoc}
     */
    public function uploadWorkout(Workout $workout)
    {
        $response = $this->getStravaAPI()->postWorkout($workout);
        $message = 'Workout to strava executed. ' . $response;
        $this->logger->info($message);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function downloadWorkout($idWorkout)
    {
        $this->logger->debug('Downloading JSON summary for workout "' . $idWorkout . '"');

        $workout = new Workout();
        $track = new Track();

        try {
            $workoutDetails = $this->getStravaAPI()->getWorkout($idWorkout);

            $this->logger->debug('Writing track points.');
            $pointsSize = count(reset($workoutDetails));
            for ($i = 0; $i < $pointsSize; $i++) {
                $latitude = $workoutDetails['latlng'][$i][0];
                $longitude = $workoutDetails['latlng'][$i][1];
                $dateTime = $workoutDetails['time'][$i];
                $dateTime->setTimezone($this->getTimeZone());

                $trackPoint = new TrackPoint($latitude, $longitude, $dateTime);
                if (isset($workoutDetails['altitude'])) {
                    $trackPoint->setElevation($workoutDetails['altitude'][$i]);
                }

                if (isset($workoutDetails['heartrate'])) {
                    $trackPoint->addExtension(new HR($workoutDetails['heartrate'][$i]));
                }

                $track->addTrackPoint($trackPoint);
            }
        } catch (NoTrackPointsFoundException $e) {
            $this->logger->warning('No track points found for workout "' . $idWorkout . '".');
        }

        $workout->addTrack($track);

        return $workout;
    }

    /**
     * Get the Strava API.
     *
     * @return API
     */
    public function getStravaAPI()
    {
        if ($this->stravaAPI === null) {
            $client = new Client();
            $this->stravaAPI = new API($client, $this->accessToken, $this->getSportMapper());
        }

        return $this->stravaAPI;
    }

    /**
     * {@inheritdoc}
     */
    protected function constructSportMapper()
    {
        return new Sport();
    }
}
