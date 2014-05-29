<?php

namespace FitnessTrackingPorting\Tracker\Endomondo;

use FitnessTrackingPorting\Tracker\TrackerInterface;
use FitnessTrackingPorting\Workout\Dumper\GPX;
use FitnessTrackingPorting\Workout\Workout;
use Goutte\Client;
use Symfony\Component\DomCrawler\Form;
use BadMethodCallException;

/**
 * Endomondo tracker.
 */
class Endomondo implements TrackerInterface
{

    const ENDOMONDO_URL_ROOT = 'http://www.endomondo.com/';

    const ENDOMONDO_URL_LOGIN = 'https://www.endomondo.com/login';

    /**
     * Username for polar.
     *
     * @var string
     */
    protected $username;

    /**
     * Password for polar.
     *
     * @var string
     */
    protected $password;

    /**
     * The GPX dumper.
     *
     * @var GPX
     */
    protected $dumper;

    /**
     * @param string $username Username for polar.
     * @param string $password Password for polar.
     */
    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
        $this->dumper = new GPX();
    }

    /**
     * Get a new instance using a config array.
     *
     * @param array $config The config for the new instance.
     * @return Endomondo
     */
    public static function fromConfig(array $config)
    {
        return new static($config['username'], $config['password']);
    }

    /**
     * Get the ID of the tracker.
     *
     * @return string
     */
    public static function getID()
    {
        return 'endomondo';
    }

    /**
     * Download a workout.
     *
     * @param integer $idWorkout The ID of the workout to download.
     * @return Workout
     * @throws BadMethodCallException Not yet implemented.
     */
    public function downloadWorkout($idWorkout)
    {
        throw new BadMethodCallException('Downloading a workout from endomondo is not yet implemented.');
    }

    /**
     * Fetch the HTML page of a workout.
     *
     * @param Workout $workout The workout to upload.
     * @return boolean
     */
    public function uploadWorkout(Workout $workout)
    {
        // File needs to have the "gpx" extension.
        $gpxFile = tempnam(sys_get_temp_dir(), 'PolarToEndomondo') . '.gpx';
        $this->dumper->dumpToFile($workout, $gpxFile, true);

        $client = new Client();

        // Login.
        $crawler = $client->request('GET', self::ENDOMONDO_URL_LOGIN);
        $loginFormDOMNode = iterator_to_array($crawler->filter('form'))[0];
        $loginForm = new Form($loginFormDOMNode, self::ENDOMONDO_URL_LOGIN, 'POST');
        $client->submit($loginForm, array('email' => $this->username, 'password' => $this->password));

        // Click "New Workout".
        $client->request('GET', 'http://www.endomondo.com/?wicket:bookmarkablePage=:com.endomondo.web.page.workout.CreateWorkoutPage2');

        // Import from file.
        $client->request(
            'GET',
            'http://www.endomondo.com/?wicket:interface=:2:pageContainer:lowerSection:lowerMain:lowerMainContent:importFileLink::IBehaviorListener:0:'
        );

        // Open the iframe.
        $crawler = $client->request(
            'GET',
            'http://www.endomondo.com/?wicket:interface=:2:pageContainer:lightboxContainer:lightboxContent:iframePage:1:ILinkListener::'
        );

        // Get the form for file upload.
        $uploadFormDOMNode = iterator_to_array($crawler->filter('form'))[0];
        $uploadFormURI = 'http://www.endomondo.com/?wicket:interface=:3:importPanel:wizardStepPanel:uploadForm:uploadSumbit::IActivePageBehaviorListener:0:&amp;wicket:ignoreIfNotActive=true';
        $uploadFormDOMNode->setAttribute('action', $uploadFormURI);
        $uploadForm = new Form($uploadFormDOMNode, $uploadFormURI, 'POST');

        // Upload the file.
        $crawler = $client->submit($uploadForm, array('uploadFile' => $gpxFile));

        // Get the submit file form.
        $data = $crawler->filter('component')->html();
        $data = str_replace('&nbsp;', '', $data);
        $submitUploadFormDOMNode = dom_import_simplexml(simplexml_load_string($data)->xpath('//form')[0]);
        $submitUploadFormURI = 'http://www.endomondo.com/?wicket:interface=:3:importPanel:wizardStepPanel:reviewForm:reviewSumbit:1:IActivePageBehaviorListener:0:&amp;wicket:ignoreIfNotActive=true';
        $submitUploadFormDOMNode->setAttribute('action', $submitUploadFormURI);
        $submitUploadForm = new Form($submitUploadFormDOMNode, $submitUploadFormURI, 'POST');

        // Submit the file.
        $client->submit($submitUploadForm);

        return true;
    }
} 