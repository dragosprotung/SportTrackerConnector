<?php

namespace SportTrackerConnector\Tracker\Polar;

use SportTrackerConnector\Workout\Workout\AbstractSportMapper;

/**
 * Sport mapper for Polar tracker.
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
            self::RUNNING => 'running',
            self::CYCLING_SPORT => 'cycling',
            self::SWIMMING => 'swimming'
        );
    }
}