<?php

namespace SportTrackerConnector\Command;

use SportTrackerConnector\Workout\Workout;
use InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Fetch a workout from a tracker and dump it to a file.
 */
class Dump extends AbstractCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        parent::configure();
        $cwd = getcwd() . DIRECTORY_SEPARATOR;
        $this->setName('dump:workout')
            ->setDescription('Fetch a workout from a tracker and save it to a file.')
            ->addArgument('tracker', InputArgument::REQUIRED, 'The tracker to dump from (ex: polar, endomondo).')
            ->addArgument('id-workout', InputArgument::REQUIRED, 'The ID of the workout to dump.')
            ->addArgument('output-format', InputArgument::OPTIONAL, 'The format to dump it.', 'gpx')
            ->addOption('output-file', 'f', InputOption::VALUE_REQUIRED, 'The path to the output file.', $cwd . 'workout.gpx')
            ->addOption('output-overwrite', 'o', InputOption::VALUE_NONE, 'Flag to auto overwrite the file if it already exists.');
    }

    /**
     * Run the command.
     *
     * @return integer
     * @throws InvalidArgumentException If the input file is not readable or the output file is not writable.
     */
    protected function runCommand()
    {
        $outputFile = $this->input->getOption('output-file');
        $overwriteOutput = $this->input->getOption('output-overwrite');
        $outputFile = $this->checkWritableFile($outputFile, $overwriteOutput);
        if ($outputFile === null) {
            return 0;
        }

        $tracker = $this->getTrackerFromCode($this->input->getArgument('tracker'));

        $idWorkout = $this->input->getArgument('id-workout');
        $workout = $tracker->downloadWorkout($idWorkout);

        $dumper = $this->getDumperFromCode($this->input->getArgument('output-format'));
        if ($dumper->dumpToFile($workout, $outputFile, $overwriteOutput) === true) {
            $this->output->writeln('<info>Dump successfully finished. Output file: ' . $outputFile . '</info>');
            return 0;
        } else {
            $this->output->writeln('<error>Could not dump workout</error>');
            return 1;
        }
    }
} 