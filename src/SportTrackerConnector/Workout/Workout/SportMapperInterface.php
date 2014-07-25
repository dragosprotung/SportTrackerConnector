<?php

namespace SportTrackerConnector\Workout\Workout;

/**
 * Workout sport types.
 */
interface SportMapperInterface
{

    const RUNNING = 'running';

    const RUNNING_TREADMILL = 'running_treadmill';

    const WALKING = 'walking';

    const WALKING_FITNESS = 'walking_fitness';

    const CYCLING_SPORT = 'cycling_sport';

    const CYCLING_TRANSPORT = 'cycling_transport';

    const CYCLING_INDOOR = 'cycling_indoor';

    const CYCLING_MOUNTAIN = 'cycling_mountain';

    const SWIMMING = 'swimming';

    const GOLF = 'golf';

    const KAYAKING = 'kayaking';

    const KITE_SURFING = 'kite_surfing';

    const HIKING = 'hiking';

    const SKATING = 'skating';

    const WEIGHT_TRAINING = 'weight_training';

    CONST OTHER = 'other';

    /**
     * Get the sport code (one of the SportMapperInterface constants) from the tracker sport code.
     *
     * @param mixed $code The code from the tracker.
     * @return string
     */
    public function getSportFromCode($code);

    /**
     * Get the tracker code for a sport from a SportMapperInterface code.
     *
     * @param string $sport The sport (one of the SportMapperInterface constants)
     * @return mixed
     */
    public function getCodeFromSport($sport);
}