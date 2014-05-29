<?php

namespace FitnessTrackingPorting\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Yaml\Yaml;
use FitnessTrackingPorting\Workout\Workout;
use InvalidArgumentException;

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
        $this->setName('dump')->setDescription('Fetch a workout from a tracker and save it to a file.')
            ->addArgument('tracker', InputArgument::REQUIRED, 'The tracker to dump from (ex: polar, endomondo).')
            ->addArgument('id-workout', InputArgument::REQUIRED, 'The ID of the workout to dump.')
            ->addArgument('output-format', InputArgument::OPTIONAL, 'The format to dump it.', 'gpx')
            ->addOption('config-file', 'c', InputOption::VALUE_REQUIRED, 'The configuration file.', $cwd . 'config.yaml')
            ->addOption('output-file', 'o', InputOption::VALUE_REQUIRED, 'The path to the output GPX file.', $cwd . 'workout.gpx')
            ->addOption('output-overwrite', 'w', InputOption::VALUE_NONE, 'Flag if the output file should be overwritten if it exists.');
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
        $outputFile = $input->getOption('output-file');
        $configFile = $input->getOption('config-file');
        $idWorkout = $input->getArgument('id-workout');

        $config = Yaml::parse(file_get_contents($configFile), true);

        $tracker = $this->getTrackerFromCode($input->getArgument('tracker'), $config);

        $workout = $tracker->downloadWorkout($idWorkout);

        $this->dumpToFile($input, $output, $workout);
        $output->writeln('<info>Dump successfully finished. Output file: ' . $outputFile . '</info>');

        return 0;
    }

    /**
     * Dump a workout to a file.
     *
     * @param InputInterface $input The input.
     * @param OutputInterface $output The output.
     * @param Workout $workout The workout to dump.
     * @return boolean
     * @throws InvalidArgumentException If the input file is not readable or the output file is not writable.
     */
    private function dumpToFile(InputInterface $input, OutputInterface $output, Workout $workout)
    {
        $outputFile = $input->getOption('output-file');
        $overwriteOutput = $input->getOption('output-overwrite');

        if (file_exists($outputFile) !== true && is_writable(dirname($outputFile)) !== true) {
            throw new InvalidArgumentException('Directory for output file "' . $outputFile . '" is not writable.');
        } elseif (file_exists($outputFile) && $overwriteOutput === false) {
            /* @var $questionHelper \Symfony\Component\Console\Helper\QuestionHelper */
            $questionHelper = $this->getHelperSet()->get('question');
            $question = new ConfirmationQuestion('The file "<info>' . $outputFile . '</info>" exists. Do you want to overwrite it ? <info>[Y]</info>: ', true);
            if (!$questionHelper->ask($input, $output, $question)) {
                $output->writeln('Abort.');
                return 0;
            }
        } elseif ($overwriteOutput === true && file_exists($outputFile) && is_writable($outputFile) !== true) {
            throw new InvalidArgumentException('The output file "' . $outputFile . '" is not writable.');
        }

        $dumper = $this->getDumperFromCode($input->getArgument('output-format'));
        return $dumper->dumpToFile($workout, $outputFile, $overwriteOutput);
    }
} 