<?php

namespace FitnessTrackingPorting\Tracker\Endomondo;

use FitnessTrackingPorting\Tracker\AbstractTracker;
use FitnessTrackingPorting\Workout\Dumper\GPX;
use FitnessTrackingPorting\Workout\Workout;
use FitnessTrackingPorting\Workout\Workout\Track;
use FitnessTrackingPorting\Workout\Workout\TrackPoint;
use FitnessTrackingPorting\Workout\Workout\Extension\HR;
use DateTime;
use GuzzleHttp\Client;

/**
 * Endomondo tracker.
 */
class Endomondo extends AbstractTracker
{

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
     * The Endomondo API.
     *
     * @var EndomondoAPI
     */
    protected $endomondoAPI;

    /**
     * The GPX dumper.
     *
     * @var GPX
     */
    protected $dumper;

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
        $this->dumper = new GPX();
    }

    /**
     * Get the ID of the tracker.
     *
     * @return string
     */
    public static function getID()
    {
        return 'endomondo';
    }

    /**
     * Download a workout.
     *
     * @param integer $idWorkout The ID of the workout to download.
     * @return Workout
     */
    public function downloadWorkout($idWorkout)
    {
        $json = $this->getEndomondoAPI()->getWorkout($idWorkout);

        $workout = new Workout();
        $track = new Track();

        foreach ($json['points'] as $point) {
            $trackPoint = new TrackPoint($point['lat'], $point['lng'], new DateTime($point['time']));
            if (isset($point['alt'])) {
                $trackPoint->setElevation($point['alt']);
            }
            if (isset($point['hr'])) {
                $trackPoint->addExtension(new HR($point['hr']));
            }

            $track->addTrackPoint($trackPoint);
        }

        $workout->addTrack($track);

        return $workout;
    }

    /**
     * Fetch the HTML page of a workout.
     *
     * @param Workout $workout The workout to upload.
     * @return boolean
     */
    public function uploadWorkout(Workout $workout)
    {
        $workoutId = $this->getEndomondoAPI()->postWorkout($workout);
        return $workoutId !== null;
    }

    /**
     * Get the Endomondo API.
     *
     * @return EndomondoAPI
     */
    public function getEndomondoAPI()
    {
        if ($this->endomondoAPI === null) {
            $client = new Client();
            $this->endomondoAPI = new EndomondoAPI($client, $this->username, $this->password);
        }

        return $this->endomondoAPI;
    }
}