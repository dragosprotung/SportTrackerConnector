<?php

namespace FitnessTrackingPorting\Tracker\Endomondo;

use FitnessTrackingPorting\Workout\Workout\SportInterface;

class Sport implements SportInterface
{

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
                return 0;
            case self::CYCLING_SPORT:
                return 2;
            case self::SWIMMING:
                return 20;
            default:
                return null;
        }
    }

    /**
     * Get the sport code (one of the SportInterface constants) from the tracker sport code.
     *
     * @param mixed $code The code from the tracker.
     * @return string
     */
    public static function getSportFromCode($code)
    {
        switch (strtolower($code)) {
            case 0:
                return self::RUNNING;
            case 2:
                return self::CYCLING_SPORT;
            case 20:
                return self::SWIMMING;
            default:
                return self::OTHER;
        }
    }
} 