# Fitness Tracking Porting

## Overview

A set of tools to dump / upload / port workouts from / to online fitness trackers (ex: endomondo.com, flow.polar.com).

I create it as i wanted to import all my workouts from flow.polar.com to endomondo.com.
It is extensible enough to support other online fitness trackers, but for the moment only polar & endomondo are supported.


[![Build Status](https://travis-ci.org/dragosprotung/FitnessTrackingPorting.svg?branch=master)](https://travis-ci.org/dragosprotung/FitnessTrackingPorting)
[![Latest Stable Version](https://poser.pugx.org/dragosprotung/fitness-tracker-porting/v/stable.svg)](https://packagist.org/packages/dragosprotung/fitness-tracker-porting)
[![Dependency Status](https://www.versioneye.com/user/projects/53ab5b00d043f9c171000074/badge.svg?style=flat)](https://www.versioneye.com/user/projects/53ab5b00d043f9c171000074)
[![License](https://poser.pugx.org/dragosprotung/fitness-tracker-porting/license.svg)](https://packagist.org/packages/dragosprotung/fitness-tracker-porting)
[![Total Downloads](https://poser.pugx.org/dragosprotung/fitness-tracker-porting/downloads.svg)](https://packagist.org/packages/dragosprotung/fitness-tracker-porting)

## Installing

Install PHP 5.4 or newer and composer.

```bash
git clone https://github.com/dragosprotung/FitnessTrackingPorting.git
composer.phar install
```

## Usage

You will need to create a config.yaml file and put in your credentials for the services.
You can rename and modify config.example.yaml

Available commands:

* dump     Fetch a workout from a tracker and save it to a file.
* sync     Sync a workout from one tracker to another.
* upload   Upload a workout file to a tracker.