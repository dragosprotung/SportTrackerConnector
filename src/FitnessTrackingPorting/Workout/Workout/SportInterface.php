<?php

namespace FitnessTrackingPorting\Workout\Workout;

/**
 * Workout sport types.
 */
interface SportInterface
{

    const RUNNING = 'running';

    const CYCLING_SPORT = 'cycling_sport';

    const CYCLING_TRANSPORT = 'cycling_transport';

    const SWIMMING = 'swimming';

    CONST OTHER = 'other';

    /**
     * Get the sport code (one of the SportInterface constants) from the tracker sport code.
     *
     * @param mixed $code The code from the tracker.
     * @return string
     */
    public static function getSportFromCode($code);

    /**
     * Get the tracker code for a sport from a SportInterface code.
     *
     * @param string $sport The sport (one of the SportInterface constants)
     * @return mixed
     */
    public static function getCodeFromSport($sport);
}