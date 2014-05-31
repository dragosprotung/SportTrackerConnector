<?php

namespace FitnessTrackingPorting\Workout;

use FitnessTrackingPorting\Workout\Workout\Author;
use FitnessTrackingPorting\Workout\Workout\Track;

/**
 * A workout.
 */
class Workout
{

    /**
     * The author of a workout.
     *
     * @var Author
     */
    protected $author;

    /**
     * The tracks of the workout.
     *
     * @var Track[]
     */
    protected $tracks = array();

    /**
     * Set the author of a workout.
     *
     * @param Author $author The author.
     */
    public function setAuthor(Author $author)
    {
        $this->author = $author;
    }

    /**
     * Get the author of the workout.
     *
     * @return Author
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Add a track.
     *
     * @param Track $track The track to add.
     */
    public function addTrack(Track $track)
    {
        $this->tracks[] = $track;
    }

    /**
     * Set the tracks.
     *
     * @param Track[] $tracks The tracks to set.
     */
    public function setTracks(array $tracks)
    {
        $this->tracks = $tracks;
    }

    /**
     * Get the tracks.
     *
     * @return Track[]
     */
    public function getTracks()
    {
        return $this->tracks;
    }
} 