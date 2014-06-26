<?php

namespace FitnessTrackingPorting\Command;

use Symfony\Component\Console\Command\Command;
use FitnessTrackingPorting\Tracker\TrackerInterface;
use FitnessTrackingPorting\Workout\Loader\LoaderInterface;
use FitnessTrackingPorting\Workout\Dumper\DumperInterface;
use InvalidArgumentException;

/**
 * Abstract class for commands.
 */
abstract class AbstractCommand extends Command
{

    /**
     * Get the tracker from the code.
     *
     * @param string $code The code of the tracker.
     * @param array $config The full configuration for all trackers.
     * @return TrackerInterface
     * @throws InvalidArgumentException If the configuration is missing for the trackers.
     * @throws InvalidArgumentException If the tracker is unknown.
     */
    protected function getTrackerFromCode($code, array $config)
    {
        if (!isset($config[$code])) {
            throw new InvalidArgumentException('There is no configuration specified for tracker "' . $code . '"');
        }

        $class = null;
        switch ($code) {
            case 'polar':
                $class = 'FitnessTrackingPorting\Tracker\Polar\Polar';
                break;
            case 'endomondo':
                $class = 'FitnessTrackingPorting\Tracker\Endomondo\Endomondo';
                break;
            default:
                throw new InvalidArgumentException('Unknown tracker "' . $code . '".');
        }

        return $class::fromConfig($config[$code]);
    }

    /**
     * Get the workout dumper from the code.
     *
     * @param string $code The code of the dumper.
     * @return DumperInterface
     * @throws InvalidArgumentException If the tracker is unknown.
     */
    protected function getDumperFromCode($code)
    {
        $class = null;
        switch (strtolower($code)) {
            case 'gpx':
                $class = 'FitnessTrackingPorting\Workout\Dumper\GPX';
                break;
            case 'json':
                $class = 'FitnessTrackingPorting\Workout\Dumper\JSON';
                break;
            default:
                throw new InvalidArgumentException('Unknown dumper "' . $code . '".');
        }

        return new $class();
    }

    /**
     * Get the workout loader from the code.
     *
     * @param string $code The code of the dumper.
     * @return LoaderInterface
     * @throws InvalidArgumentException If the tracker is unknown.
     */
    protected function getLoaderFromCode($code)
    {
        $class = null;
        switch (strtolower($code)) {
            case 'gpx':
                $class = 'FitnessTrackingPorting\Workout\Loader\GPX';
                break;
            default:
                throw new InvalidArgumentException('Unknown loader "' . $code . '".');
        }

        return new $class();
    }
} 