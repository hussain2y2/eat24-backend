<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libraries\GoogleClient;
use Google_Service_Tasks;
use Illuminate\Http\Request;
use Google_Client;

define('STDIN',fopen("php://stdin", 'r'));

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $client = $this->client();
        $service = new \Google_Service_Tasks($client);
        $optParams = array(
            'maxResults' => 10,
        );
        $results = $service->tasklists->listTasklists($optParams);
        dd($results);
    }

    public function client()
    {
        $client = new Google_Client();
        $client->setApplicationName('Google Tasks API PHP Quickstart');
        $client->setScopes(Google_Service_Tasks::TASKS);
        $client->setAuthConfig(public_path('credentials.json'));
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        // Load previously authorized token from a file, if it exists.
        // The file token.json stores the user's access and refresh tokens, and is
        // created automatically when the authorization flow completes for the first
        // time.
        $tokenPath = 'token.json';
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }

        $authCode = '4/0AGHWcrh6LCvkM1VWWUtot0b2bUR1zLKzUMpI4XUhOx7Sgz3DINIl0g';

        // If there is no previous token or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {
                // Request authorization from the user.
                $authUrl = $client->createAuthUrl();
//                printf("Open the following link in your browser:\n%s\n", $authUrl);
//                print 'Enter verification code: ';
//                $authCode = trim(fgets(STDIN));

                // Exchange authorization code for an access token.
                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                $client->setAccessToken($accessToken);

                // Check to see if there was an error.
                if (array_key_exists('error', $accessToken)) {
                    throw new \Exception(join(', ', $accessToken));
                }
            }
            // Save the token to a file.
            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }
            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
        }
        dd($client);
        return $client;
    }
}
