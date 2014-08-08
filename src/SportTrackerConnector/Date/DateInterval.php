<?php

namespace SportTrackerConnector\Date;

/**
 * DateInterval helper class.
 */
class DateInterval extends \DateInterval
{

    /**
     * Get the total number of seconds from the DateInterval.
     *
     * @return integer
     */
    public function getTotalSeconds()
    {
        return $this->days * 86400 + $this->h * 3600 + $this->i * 60 + $this->s;
    }
}
