<?php

namespace FitnessTrackingPorting\Workout\Workout;

/**
 * Class that tries to guess the sport from text representation.
 */
class SportGuesser implements Sport
{

    /**
     * Get the sport code from the tracker sport code.
     *
     * @param mixed $code The code from the tracker.
     * @return string
     */
    public static function getSportFromCode($code)
    {
        switch (strtolower(trim($code))) {
            case self::RUNNING:
            case 'run':
                return self::RUNNING;
            case self::CYCLING_SPORT:
            case 'cycling':
                return self::CYCLING_SPORT;
            case self::CYCLING_TRANSPORT:
                return self::CYCLING_TRANSPORT;
            case self::SWIMMING:
                return self::SWIMMING;
            default:
                return self::OTHER;
        }
    }
}