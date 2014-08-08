<?php

namespace SportTrackerConnector\Workout;

use SportTrackerConnector\Workout\Workout\Author;
use SportTrackerConnector\Workout\Workout\Track;

/**
 * A workout.
 */
class Workout
{

    /**
     * The author of a workout.
     *
     * @var \SportTrackerConnector\Workout\Workout\Author
     */
    protected $author;

    /**
     * The tracks of the workout.
     *
     * @var \SportTrackerConnector\Workout\Workout\Track[]
     */
    protected $tracks = array();

    /**
     * Set the author of a workout.
     *
     * @param \SportTrackerConnector\Workout\Workout\Author $author The author.
     */
    public function setAuthor(Author $author)
    {
        $this->author = $author;
    }

    /**
     * Get the author of the workout.
     *
     * @return \SportTrackerConnector\Workout\Workout\Author
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Add a track.
     *
     * @param \SportTrackerConnector\Workout\Workout\Track $track The track to add.
     */
    public function addTrack(Track $track)
    {
        $this->tracks[] = $track;
    }

    /**
     * Set the tracks.
     *
     * @param \SportTrackerConnector\Workout\Workout\Track[] $tracks The tracks to set.
     */
    public function setTracks(array $tracks)
    {
        $this->tracks = array();

        foreach ($tracks as $track) {
            $this->addTrack($track);
        }
    }

    /**
     * Get the tracks.
     *
     * @return \SportTrackerConnector\Workout\Workout\Track[]
     */
    public function getTracks()
    {
        return $this->tracks;
    }
}
