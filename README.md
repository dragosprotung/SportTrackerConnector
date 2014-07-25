# Sport Tracker Connector

## Overview

A set of tools to dump / upload / port workouts from / to online sport trackers (ex: endomondo.com, flow.polar.com).

I create it as i wanted to import all my workouts from flow.polar.com to endomondo.com.
It is extensible enough to support other online sport trackers, but for the moment only polar & endomondo are supported.


[![Build Status](https://travis-ci.org/dragosprotung/SportTrackerConnector.svg?branch=master)](https://travis-ci.org/dragosprotung/SportTrackerConnector)
[![Latest Stable Version](https://poser.pugx.org/dragosprotung/sport-tracker-connector/v/stable.svg)](https://packagist.org/packages/dragosprotung/sport-tracker-connector)
[![Dependency Status](https://www.versioneye.com/user/projects/53d2a96a851c56cb78000175/badge.svg)](https://www.versioneye.com/user/projects/53d2a96a851c56cb78000175)
[![License](https://poser.pugx.org/dragosprotung/sport-tracker-connector/license.svg)](https://packagist.org/packages/dragosprotung/sport-tracker-connector)
[![Total Downloads](https://poser.pugx.org/dragosprotung/sport-tracker-connector/downloads.svg)](https://packagist.org/packages/dragosprotung/sport-tracker-connector)
## Installing

Install PHP 5.4 or newer and composer.

```bash
git clone https://github.com/dragosprotung/SportTrackerConnector.git
composer.phar install
```

## Usage

You will need to create a config.yaml file and put in your credentials for the services.
You can rename and modify config.example.yaml

Available commands:

* dump:workout     Fetch a workout from a tracker and save it to a file (gpx, json, etc).
* dump:multi      Fetch multiple workouts from a date interval with resume functionality.
* upload:sync     Copy a workout / multiple workouts from one tracker to another.
* upload:workout   Upload a workout file to a tracker.