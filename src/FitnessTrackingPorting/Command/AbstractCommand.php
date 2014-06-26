<?php

namespace FitnessTrackingPorting\Command;

use FitnessTrackingPorting\Tracker\TrackerInterface;
use FitnessTrackingPorting\Workout\Dumper\DumperInterface;
use FitnessTrackingPorting\Workout\Loader\LoaderInterface;
use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Abstract class for commands.
 */
abstract class AbstractCommand extends Command
{

    /**
     * The console input.
     *
     * @var \Symfony\Component\Console\Input\InputInterface
     */
    protected $input;

    /**
     * The console output.
     *
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    /**
     * The logger.
     *
     * @var \Symfony\Component\Console\Logger\ConsoleLogger
     */
    protected $logger;

    /**
     * Execute the command.
     *
     * @param InputInterface $input The input.
     * @param OutputInterface $output The output.
     * @return integer
     * @throws InvalidArgumentException If the input file is not readable or the output file is not writable.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->logger = new ConsoleLogger($this->output);

        return $this->runCommand();
    }

    /**
     * Run the command.
     *
     * @return integer
     */
    abstract protected function runCommand();

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

        $tracker = $class::fromConfig($this->logger, $config[$code]);
        return $tracker;
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