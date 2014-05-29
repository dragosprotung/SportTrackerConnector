<?php

namespace FitnessTrackingPorting\Workout\Workout;

/**
 * Workout sport types.
 */
interface Sport
{

    const RUNNING = 'running';

    const CYCLING_SPORT = 'cycling_sport';

    const CYCLING_TRANSPORT = 'cycling_transport';

    const SWIMMING = 'swimming';

    CONST OTHER = 'other';

    /**
     * Get the sport code from the tracker sport code.
     *
     * @param mixed $code The code from the tracker.
     * @return string
     */
    public static function getSportFromCode($code);
}