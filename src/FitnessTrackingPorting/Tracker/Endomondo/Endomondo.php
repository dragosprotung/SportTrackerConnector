<?php

namespace FitnessTrackingPorting\Tracker\Endomondo;

use FitnessTrackingPorting\Tracker\AbstractTracker;
use FitnessTrackingPorting\Workout\Dumper\GPX;
use FitnessTrackingPorting\Workout\Workout;
use BadMethodCallException;

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
     * @throws BadMethodCallException Not yet implemented.
     */
    public function downloadWorkout($idWorkout)
    {
        throw new BadMethodCallException('Downloading a workout from endomondo is not yet implemented.');
    }

    /**
     * Fetch the HTML page of a workout.
     *
     * @param Workout $workout The workout to upload.
     * @return boolean
     */
    public function uploadWorkout(Workout $workout)
    {
        $client = new \GuzzleHttp\Client();
        $endomondoAPI = new EndomondoAPI($client, $this->username, $this->password);
        $workoutId = $endomondoAPI->postWorkout($workout);

        return $workoutId !== null;
    }
}