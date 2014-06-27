<?php

namespace FitnessTrackingPorting\Command;

use FitnessTrackingPorting\Workout\Workout;
use InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Yaml\Yaml;

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
        $configFile = $this->input->getOption('config-file');
        $idWorkout = $this->input->getArgument('id-workout');

        $config = Yaml::parse(file_get_contents($configFile), true);

        $tracker = $this->getTrackerFromCode($this->input->getArgument('tracker'), $config);

        $workout = $tracker->downloadWorkout($idWorkout);

        $this->dumpToFile($workout);
        $this->output->writeln('<info>Dump successfully finished. Output file: ' . $outputFile . '</info>');

        return 0;
    }

    /**
     * Dump a workout to a file.
     *
     * @param Workout $workout The workout to dump.
     * @return boolean
     * @throws InvalidArgumentException If the input file is not readable or the output file is not writable.
     */
    private function dumpToFile(Workout $workout)
    {
        $outputFile = $this->input->getOption('output-file');
        $overwriteOutput = $this->input->getOption('output-overwrite');

        if (file_exists($outputFile) !== true && is_writable(dirname($outputFile)) !== true) {
            throw new InvalidArgumentException('Directory for output file "' . $outputFile . '" is not writable.');
        } elseif (file_exists($outputFile) && $overwriteOutput === false) {
            /* @var $questionHelper \Symfony\Component\Console\Helper\QuestionHelper */
            $questionHelper = $this->getHelperSet()->get('question');
            $question = new ConfirmationQuestion('The file "<info>' . $outputFile . '</info>" exists. Do you want to overwrite it ? <info>[Y]</info>: ', true);
            if (!$questionHelper->ask($this->input, $this->output, $question)) {
                $this->output->writeln('Abort.');
                return 0;
            }
        } elseif ($overwriteOutput === true && file_exists($outputFile) && is_writable($outputFile) !== true) {
            throw new InvalidArgumentException('The output file "' . $outputFile . '" is not writable.');
        }

        $dumper = $this->getDumperFromCode($this->input->getArgument('output-format'));
        return $dumper->dumpToFile($workout, $outputFile, $overwriteOutput);
    }
} 