<?php

namespace FitnessTrackingPorting;

use FitnessTrackingPorting\Command\Dump;
use FitnessTrackingPorting\Command\Upload;
use FitnessTrackingPorting\Command\UploadSync;
use Symfony\Component\Console\Application;

/**
 * The console application.
 */
class ConsoleApplication extends Application
{

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct('Fitness tracking porting', '0.1');

        $this->add(new Upload());
        $this->add(new UploadSync());
        $this->add(new Dump());
    }
} 