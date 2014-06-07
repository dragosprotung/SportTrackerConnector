<?php

namespace FitnessTrackingPorting\Tracker\Polar;

use FitnessTrackingPorting\Workout\Workout\SportInterface;

class Sport implements SportInterface
{

    /**
     * Get the sport code (one of the SportInterface constants) from the tracker sport code.
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

    /**
     * Get the tracker code for a sport from a SportInterface code.
     *
     * @param string $sport The sport (one of the SportInterface constants)
     * @return mixed
     */
    public static function getCodeFromSport($sport)
    {
        switch (strtolower($sport)) {
            case self::RUNNING:
                return 'running';
            case self::CYCLING_SPORT:
                return 'cycling';
            case self::SWIMMING:
                return 'swimming';
            default:
                return null;
        }
    }
} 