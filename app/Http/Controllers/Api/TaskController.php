<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libraries\GoogleClient;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index()
    {
        $client = GoogleClient::getClient();
        $service = new \Google_Service_Tasks($client);
        $optParams = array(
            'maxResults' => 10,
        );
        $results = $service->tasklists->listTasklists($optParams);
        dd($results);
    }
}
