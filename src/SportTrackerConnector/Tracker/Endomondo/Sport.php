<?php

namespace SportTrackerConnector\Tracker\Endomondo;

use SportTrackerConnector\Workout\Workout\AbstractSportMapper;

/**
 * Sport mapper for Endomondo tracker.
 */
class Sport extends AbstractSportMapper
{

    /**
     * Get the map between the tracker's sport codes and internal sport codes.
     *
     * The key should be the internal sport code.
     *
     * @return array
     */
    public function getMap()
    {
        return array(
            self::RUNNING => 0,
            self::RUNNING_TREADMILL => 88,
            self::WALKING => 18,
            self::WALKING_FITNESS => 14,
            self::CYCLING_SPORT => 2,
            self::CYCLING_TRANSPORT => 1,
            self::CYCLING_INDOOR => 21,
            self::CYCLING_MOUNTAIN => 3,
            self::SWIMMING => 20,
            self::GOLF => 15,
            self::KAYAKING => 9,
            self::KITE_SURFING => 10,
            self::HIKING => 16,
            self::SKATING => 4,
            self::WEIGHT_TRAINING => 46,
            self::OTHER => 20,
        );
    }
}
