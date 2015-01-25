<?php

namespace SportTrackerConnector\Command;

use DateTime;
use InvalidArgumentException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Dump multiple workouts from a tracker.
 */
class DumpMulti extends AbstractCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        parent::configure();
        $cwd = getcwd() . DIRECTORY_SEPARATOR;
        $this->setName('dump:multi')
            ->setDescription('Fetch multiple workouts from a tracker and save each one of them into a folder.')
            ->addArgument(
                'tracker',
                InputArgument::OPTIONAL,
                'The tracker to dump from (ex: polar, endomondo). Optional only if provided a resume list.'
            )
            ->addArgument('output-format', InputArgument::OPTIONAL, 'The format to dump it.', 'tcx')
            ->addOption('output-directory', 'd', InputOption::VALUE_REQUIRED, 'The directory where to dump the workouts.', $cwd . 'dump')
            ->addOption('output-overwrite', 'o', InputOption::VALUE_NONE, 'Flag to auto overwrite the file if it already exists.')
            ->addOption(
                'output-files-list',
                'l',
                InputOption::VALUE_REQUIRED,
                'The file with the list of workouts to dump.',
                $cwd . 'dump' . DIRECTORY_SEPARATOR . 'list.csv'
            )
            ->addOption('date-start', 's', InputOption::VALUE_REQUIRED, 'The start date from where to start dumping workouts', 'today')
            ->addOption('date-end', 'e', InputOption::VALUE_REQUIRED, 'The end date from where to start dumping workouts', 'now')
            ->addOption('list-only', null, InputOption::VALUE_NONE, 'Flag if only the list should be generated and not also processed.')
            ->addOption('resume-list', 'r', InputOption::VALUE_REQUIRED, 'Resume a multi dump from a dump list file.');
    }

    /**
     * Run the command.
     *
     * @return integer
     * @throws InvalidArgumentException If the resume list file is given but can not be read.
     */
    protected function runCommand()
    {
        $resumeList = $this->input->getOption('resume-list');
        if ($resumeList !== null) {
            if (is_readable($resumeList) !== true) {
                throw new InvalidArgumentException('Could not read list file ' . $resumeList);
            }

            $this->processListFile($resumeList);

            return 0;
        }

        $startDate = new DateTime($this->input->getOption('date-start'));
        $endDate = new DateTime($this->input->getOption('date-end'));

        $tracker = $this->getTrackerFromCode($this->input->getArgument('tracker'));

        $this->logger->debug('Listing workouts for interval: ' . $startDate->format(DateTime::W3C) . ' - ' . $endDate->format(DateTime::W3C));
        $workouts = $tracker->listWorkouts($startDate, $endDate);
        $listFile = $this->checkWritableFile($this->input->getOption('output-files-list'), false);
        if ($listFile !== null) {
            $this->logger->debug('Writing list to file ' . $listFile);
            $format = $this->input->getArgument('output-format');
            $outputDirectory = rtrim($this->input->getOption('output-directory'), DIRECTORY_SEPARATOR);
            $filePointer = fopen($listFile, 'w');
            foreach ($workouts as $i => $workout) {
                fputcsv(
                    $filePointer,
                    array(
                        'output-format' => $format,
                        'output-file' => $outputDirectory . DIRECTORY_SEPARATOR . 'workout-' . $i . '.' . $format,
                        'output-overwrite' => (int)$this->input->getOption('output-overwrite'),
                        'tracker' => $tracker->getID(),
                        'id' => $workout->idWorkout,
                        'startDateTime' => $workout->startDateTime->format(DateTime::W3C),
                        'processed' => '0'
                    )
                );
            }
            fclose($filePointer);

            if ($this->input->getOption('list-only') !== true) {
                $this->processListFile($listFile);
            }
        }

        return 0;
    }

    /**
     * Process a list of workouts.
     *
     * @param string $listFile The file to process.
     */
    private function processListFile($listFile)
    {
        $this->logger->debug('Processing list file ' . $listFile);
        $dumpCommand = new Dump();

        $filePointer = fopen($listFile, 'r+');
        while (($line = fgets($filePointer)) !== false) {
            $data = str_getcsv($line);
            if ($data[6] == true) {
                // Skip if the workout has been marked as processed.
                $this->logger->notice('Workout "' . $data[4] . '" is already processed.');
                continue;
            }

            // Import the workout.
            $input = new ArrayInput(
                array(
                    'tracker' => $data[3],
                    'id-workout' => $data[4],
                    'output-format' => $data[0],
                    '--output-file' => $data[1],
                    '--output-overwrite' => $data[2]
                )
            );
            $dumpCommand->run($input, $this->output);

            // Rewind to the begging of the line and
            fseek($filePointer, -mb_strlen($line), SEEK_CUR);
            $data[6] = 1;
            $data = $this->strPutCSV($data);
            fwrite($filePointer, $data . "\n");
        }
        fclose($filePointer);
    }

    /**
     * Put an array in one line of CSV string.
     *
     * https://gist.github.com/johanmeiring/2894568
     *
     * @param array $input The input.
     * @param string $delimiter Delimiter.
     * @param string $enclosure Enclosure.
     * @return string
     */
    private function strPutCSV(array $input, $delimiter = ',', $enclosure = '"')
    {
        $filePointer = fopen('php://temp', 'r+');
        fputcsv($filePointer, $input, $delimiter, $enclosure);
        rewind($filePointer);
        $data = fread($filePointer, 1048576);
        fclose($filePointer);
        return rtrim($data);
    }
}
