<?php

namespace FitnessTrackingPorting\Tracker\Polar;


class Sport implements \FitnessTrackingPorting\Workout\Workout\Sport
{

    /**
     * Get the sport code from the tracker sport code.
     *
     * @param mixed $code The code from the tracker.
     * @return string
     */
    public static function getSportFromCode($code)
    {
        switch (strtolower($code)) {
            case 'running':
                return self::RUNNING;
            case 'cycling':
                return self::CYCLING_SPORT;
            case 'swimming':
                return self::SWIMMING;
            default:
                return self::OTHER;
        }
    }
} 