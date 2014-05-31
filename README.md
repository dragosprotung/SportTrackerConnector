# Fitness Tracking Porting

## Overview

A set of tools to dump / upload / port workouts from / to online fitness trackers (ex: endomondo.com, flow.polar.com).

I create it as i wanted to import all my workouts from flow.polar.com to endomondo.com.
It is extensible enough to support other online fitness trackers, but for the moment only polar & endomondo are supported.

## Installing

Install PHP 5.4 or newer and composer.

```bash
git clone https://github.com/dragosprotung/FitnessTrackingPorting.git
composer.phar install
```

## Usage

You will need to create a config.yaml file and put in your credentials for the services.
You can rename and modify config.example.yaml

Run

```bash
./porting list
```

for a list of commands.
