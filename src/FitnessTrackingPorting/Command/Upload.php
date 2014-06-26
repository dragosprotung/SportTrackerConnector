<?php

namespace FitnessTrackingPorting\Command;

use FitnessTrackingPorting\Workout\Workout;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;

/**
 * Upload a workout file to a tracker.
 */
class Upload extends AbstractCommand
{

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        parent::configure();
        $cwd = getcwd() . DIRECTORY_SEPARATOR;
        $this->setName('upload')->setDescription('Upload a workout file to a tracker.')
            ->addArgument('tracker', InputArgument::REQUIRED, 'The tracker to upload (ex: polar, endomondo).')
            ->addArgument('workout-file', InputArgument::REQUIRED, 'The path to the workout file to upload.')
            ->addOption('config-file', 'c', InputOption::VALUE_REQUIRED, 'The configuration file.', $cwd . 'config.yaml');
    }

    /**
     * Run the command.
     *
     * @return integer
     */
    protected function runCommand()
    {
        $workoutFile = $this->input->getArgument('workout-file');
        $configFile = $this->input->getOption('config-file');

        $config = Yaml::parse(file_get_contents($configFile), true);

        $tracker = $this->getTrackerFromCode($this->input->getArgument('tracker'), $config);

        $loader = $this->getLoaderFromCode(pathinfo($workoutFile, PATHINFO_EXTENSION));
        $workout = $loader->fromFile($workoutFile);

        $tracker->uploadWorkout($workout);

        $this->output->writeln('<info>Upload successfully done. Workout file: ' . $workoutFile . '</info>');

        return 0;
    }
} 