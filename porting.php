<?php

use Symfony\Component\Console\Application;
use FitnessTrackingPorting\Command\PolarConverter;
use FitnessTrackingPorting\Command\Sync;
use FitnessTrackingPorting\Command\Upload;
use FitnessTrackingPorting\Command\Dump;

$classLoader = require(__DIR__ . '/vendor/autoload.php');

$console = new Application('Fitness tracking porting', '0.1');
$console->add(new Sync());
$console->add(new Upload());
$console->add(new Dump());
$console->run();