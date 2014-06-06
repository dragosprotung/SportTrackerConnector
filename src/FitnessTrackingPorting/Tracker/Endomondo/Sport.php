<?php

namespace FitnessTrackingPorting\Tracker\Endomondo;

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
            case self::RUNNING:
                return 0;
            case self::CYCLING_SPORT:
                return 2;
            case self::SWIMMING:
                return 20;
            default:
                return self::OTHER;
        }
    }
} 