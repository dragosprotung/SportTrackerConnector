<?php

namespace FitnessTrackingPorting\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use FitnessTrackingPorting\Workout\Workout;

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
     * Execute the command.
     *
     * @param InputInterface $input The input.
     * @param OutputInterface $output The output.
     * @return integer
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $workoutFile = $input->getArgument('workout-file');
        $configFile = $input->getOption('config-file');

        $config = Yaml::parse(file_get_contents($configFile), true);

        $tracker = $this->getTrackerFromCode($input->getArgument('tracker'), $config);

        $loader = $this->getLoaderFromCode(pathinfo($workoutFile, PATHINFO_EXTENSION));
        $workout = $loader->fromFile($workoutFile);

        $tracker->uploadWorkout($workout);

        $output->writeln('<info>Upload successfully done. Workout file: ' . $workoutFile . '</info>');

        return 0;
    }
} 