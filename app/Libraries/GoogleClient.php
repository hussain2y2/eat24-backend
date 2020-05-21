<?php

namespace App\Libraries;

use Google_Client;
use Illuminate\Http\Request;

class GoogleClient
{
    /**
     * @param Request $request
     * @return string
     */
    public static function getClient()
    {
        $client = new Google_Client();
        $client->setClientId(env('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $client->setApplicationName('EAT 24/7');
        $client->setRedirectUri(env('GOOGLE_REDIRECT_URL'));
        $client->addScope('https://www.googleapis.com/auth/tasks');

        return $client;
    }
}
