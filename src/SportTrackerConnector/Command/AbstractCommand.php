<?php

namespace SportTrackerConnector\Command;

use SportTrackerConnector\Tracker\TrackerInterface;
use SportTrackerConnector\Workout\Dumper\DumperInterface;
use SportTrackerConnector\Workout\Loader\LoaderInterface;
use InvalidArgumentException;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Yaml\Yaml;

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
     * Configures the current command.
     */
    protected function configure()
    {
        $this->addOption('config-file', 'c', InputOption::VALUE_REQUIRED, 'The configuration file.', getcwd() . DIRECTORY_SEPARATOR . 'config.yaml');
    }

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
        $this->logger = new ConsoleLogger($this->output, array(), array(LogLevel::WARNING => ConsoleLogger::ERROR));

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
     * @return TrackerInterface
     * @throws InvalidArgumentException If the configuration is missing for the trackers.
     * @throws InvalidArgumentException If the tracker is unknown.
     */
    protected function getTrackerFromCode($code)
    {
        $configFile = $this->input->getOption('config-file');
        $config = Yaml::parse(file_get_contents($configFile), true);

        if (!isset($config[$code])) {
            throw new InvalidArgumentException('There is no configuration specified for tracker "' . $code . '"');
        }

        switch ($code) {
            case 'polar':
                $class = 'SportTrackerConnector\Tracker\Polar\Polar';
                break;
            case 'endomondo':
                $class = 'SportTrackerConnector\Tracker\Endomondo\Endomondo';
                break;
            case 'strava':
                $class = 'SportTrackerConnector\Tracker\Strava\Strava';
                break;
            default:
                throw new InvalidArgumentException('Unknown tracker "' . $code . '".');
        }

        return $class::fromConfig($this->logger, $config[$code]);
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
        switch (strtolower($code)) {
            case 'gpx':
                $class = 'SportTrackerConnector\Workout\Dumper\GPX';
                break;
            case 'tcx':
                $class = 'SportTrackerConnector\Workout\Dumper\TCX';
                break;
            case 'json':
                $class = 'SportTrackerConnector\Workout\Dumper\JSON';
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
        switch (strtolower($code)) {
            case 'gpx':
                $class = 'SportTrackerConnector\Workout\Loader\GPX';
                break;
            case 'tcx':
                $class = 'SportTrackerConnector\Workout\Loader\TCX';
                break;
            default:
                throw new InvalidArgumentException('Unknown loader "' . $code . '".');
        }

        return new $class();
    }

    /**
     * Check if a file is writable. If not ask the user if he want to overwrite.
     *
     * @param string $file The file to check.
     * @param boolean $overwriteOutput Flag if the file can be overwritten.
     * @return string The output file.
     * @throws \InvalidArgumentException If the file is not writable.
     */
    protected function checkWritableFile($file, $overwriteOutput = false)
    {
        if (file_exists($file) !== true && is_writable(dirname($file)) !== true) {
            throw new InvalidArgumentException('Directory for output file "' . $file . '" is not writable.');
        } elseif (file_exists($file) && $overwriteOutput === false) {
            /* @var $questionHelper \Symfony\Component\Console\Helper\QuestionHelper */
            $questionHelper = $this->getHelperSet()->get('question');
            $question = new ConfirmationQuestion('The file "<info>' . $file . '</info>" exists. Do you want to overwrite it ? <info>[Y]</info>: ', true);
            if (!$questionHelper->ask($this->input, $this->output, $question)) {
                $this->output->writeln('Abort.');
                return null;
            }
        } elseif ($overwriteOutput === true && file_exists($file) && is_writable($file) !== true) {
            throw new InvalidArgumentException('The output file "' . $file . '" is not writable.');
        }

        return $file;
    }
}
