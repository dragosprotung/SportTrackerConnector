<?php

namespace SportTrackerConnector\Tracker\Strava\Command;

use GuzzleHttp\Client;
use GuzzleHttp\Message\ResponseInterface;
use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Get a token from strava.com to access private workouts and be able to upload workouts.
 */
class GetToken extends Command
{

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('strava:get-token')
            ->setDescription('Give access to the application to access your account and generate a token with writing and access to private workouts. Put this token in your configuration file.')
            ->addOption('config-file', 'c', InputOption::VALUE_REQUIRED, 'The configuration file.', getcwd() . DIRECTORY_SEPARATOR . 'config.yaml');
    }

    /**
     * Execute the command.
     *
     * @param InputInterface $input The input.
     * @param OutputInterface $output The output.
     * @throws InvalidArgumentException If the configuration is not specified.
     * @return integer
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configFile = $input->getOption('config-file');
        $config = Yaml::parse(file_get_contents($configFile), true);

        if (!isset($config['strava']['auth'])) {
            throw new InvalidArgumentException('There is no configuration specified for tracker "strava". See example config.');
        }

        $secretToken = $config['strava']['auth']['secretToken'];
        $clientID = $config['strava']['auth']['clientID'];
        $username = $config['strava']['auth']['username'];
        $password = $config['strava']['auth']['password'];

        $httpClient = new Client();
        /** @var \GuzzleHttp\Message\ResponseInterface $response */

        // Do normal login.
        $response = $httpClient->get('https://www.strava.com/login', ['cookies' => true]);
        $authenticityToken = $this->getAuthenticityToken($response);

        // Perform the login to strava.com
        $httpClient->post(
            'https://www.strava.com/session',
            array(
                'cookies' => true,
                'body' => array(
                    'authenticity_token' => $authenticityToken,
                    'email' => $username,
                    'password' => $password
                )
            )
        );

        // Get the authorize page.
        $response = $httpClient->get(
            'https://www.strava.com/oauth/authorize?client_id=' . $clientID . '&response_type=code&redirect_uri=http://localhost&scope=view_private,write&approval_prompt=force',
            ['cookies' => true]
        );
        $authenticityToken = $this->getAuthenticityToken($response);

        // Accept the application.
        $response = $httpClient->post(
            'https://www.strava.com/oauth/accept_application?client_id=' . $clientID . '&response_type=code&redirect_uri=http://localhost&scope=view_private,write',
            array(
                'cookies' => true,
                'allow_redirects' => false, // disable redirects as we do not want Guzzle to redirect to localhost.
                'body' => array(
                    'authenticity_token' => $authenticityToken
                )
            )
        );

        $redirectLocation = $response->getHeader('Location');
        $urlQuery = parse_url($redirectLocation, PHP_URL_QUERY);
        parse_str($urlQuery, $urlQuery);

        $authorizationCode = $urlQuery['code'];

        // Token exchange.
        $response = $httpClient->post(
            'https://www.strava.com/oauth/token',
            array(
                'body' => array(
                    'client_id' => $clientID,
                    'client_secret' => $secretToken,
                    'code' => $authorizationCode
                )
            )
        );

        $jsonResponse = $response->json();
        $code = $jsonResponse['access_token'];

        $output->writeln('Your access token is: <comment>' . $code . '</comment>');

        return 0;
    }

    /**
     * Get the authenticity token from a strava HTML page.
     *
     * @param ResponseInterface $response The HTTP request response.
     * @return string
     */
    private function getAuthenticityToken(ResponseInterface $response)
    {
        $domHTML = new \DOMDocument();
        libxml_use_internal_errors(true);
        $domHTML->loadHTML($response->getBody());
        libxml_use_internal_errors(false);

        $xpath = new \DOMXPath($domHTML);
        $xml = $xpath->query('//input[@name="authenticity_token"]');

        $authenticityToken = trim($xml->item(0)->getAttribute('value'));
        if (empty($authenticityToken)) {
            throw new \RuntimeException('Could not fetch the "authenticity_token" from the authorization page.');
        }

        return $authenticityToken;
    }
}