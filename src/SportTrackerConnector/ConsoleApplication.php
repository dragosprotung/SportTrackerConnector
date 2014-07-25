<?php

namespace SportTrackerConnector;

use SportTrackerConnector\Command\Dump;
use SportTrackerConnector\Command\DumpMulti;
use SportTrackerConnector\Command\Upload;
use SportTrackerConnector\Command\UploadSync;
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
        parent::__construct('Sport tracker connector', '0.2');

        $this->add(new Upload());
        $this->add(new UploadSync());
        $this->add(new Dump());
        $this->add(new DumpMulti());
    }
} 