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
        switch ($code) {
            case 1:
                return self::RUNNING;
            case 2:
                return self::CYCLING_SPORT;
            case 23:
                return self::SWIMMING;
            default:
                return self::OTHER;
        }
    }
} 