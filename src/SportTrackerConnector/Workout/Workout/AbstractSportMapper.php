<?php

namespace SportTrackerConnector\Workout\Workout;

/**
 * Abstract class for tracker sport definitions.
 */
abstract class AbstractSportMapper implements SportMapperInterface
{

    /**
     * Get the map between the tracker's sport codes and internal sport codes.
     *
     * The key should be the internal sport code.
     *
     * @return array
     */
    abstract protected function getMap();

    /**
     * Get the sport code (one of the SportMapperInterface constants) from the tracker sport code.
     *
     * @param mixed $code The code from the tracker.
     * @return string
     */
    public function getSportFromCode($code)
    {
        $code = strtolower($code);
        $codes = array_flip(static::getMap());
        if (isset($codes[$code])) {
            return $codes[$code];
        } else {
            return self::OTHER;
        }
    }

    /**
     * Get the tracker code for a sport from a SportMapperInterface code.
     *
     * @param string $sport The sport (one of the SportMapperInterface constants)
     * @return mixed
     */
    public function getCodeFromSport($sport)
    {
        $sport = strtolower($sport);
        $codes = static::getMap();
        if (isset($codes[$sport])) {
            return $codes[$sport];
        } elseif (isset($codes[self::OTHER])) {
            return $codes[self::OTHER];
        } else {
            return null;
        }
    }
}
