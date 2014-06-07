<?php

namespace FitnessTrackingPorting\Workout\Workout;

/**
 * Class that tries to guess the sport from text representation.
 */
class SportGuesser
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
            case SportInterface::RUNNING:
            case 'run':
                return SportInterface::RUNNING;
            case SportInterface::CYCLING_SPORT:
            case 'cycling':
                return SportInterface::CYCLING_SPORT;
            case SportInterface::CYCLING_TRANSPORT:
                return SportInterface::CYCLING_TRANSPORT;
            case SportInterface::SWIMMING:
                return SportInterface::SWIMMING;
            default:
                return SportInterface::OTHER;
        }
    }
}