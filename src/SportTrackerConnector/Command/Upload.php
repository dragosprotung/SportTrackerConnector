<?php

namespace SportTrackerConnector\Command;

use Symfony\Component\Console\Input\InputArgument;

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
        $this->setName('upload:workout')
            ->setDescription('Upload a workout file to a tracker.')
            ->addArgument('tracker', InputArgument::REQUIRED, 'The tracker to upload (ex: polar, endomondo).')
            ->addArgument('workout-file', InputArgument::REQUIRED, 'The path to the workout file to upload.');
    }

    /**
     * Run the command.
     *
     * @return integer
     */
    protected function runCommand()
    {
        $workoutFile = $this->input->getArgument('workout-file');

        $loader = $this->getLoaderFromCode(pathinfo($workoutFile, PATHINFO_EXTENSION));
        $workout = $loader->fromFile($workoutFile);

        $tracker = $this->getTrackerFromCode($this->input->getArgument('tracker'));
        $tracker->uploadWorkout($workout);

        $this->output->writeln('<info>Upload successfully done. Workout file: ' . $workoutFile . '</info>');

        return 0;
    }
} 