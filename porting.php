<?php

use FitnessTrackingPorting\ConsoleApplication;

$classLoader = require(__DIR__ . '/vendor/autoload.php');

$console = new ConsoleApplication();
$console->run();