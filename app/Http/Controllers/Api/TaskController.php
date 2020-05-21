<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libraries\GoogleClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|null
     */
    public function index(Request $request)
    {
        $client = GoogleClient::getClient();

        $tokenPath = 'token.json';

        if (file_exists(storage_path("app/$tokenPath"))) {
            $accessToken = json_decode(file_get_contents(storage_path("app/$tokenPath")), true);
            $client->setAccessToken($accessToken);
        }

        if ($client->isAccessTokenExpired()) {
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {
                if ($request->has('code')) {
                    $accessToken = $client->fetchAccessTokenWithAuthCode($request->get('code'));
                    $client->setAccessToken($accessToken);
                } else {
                    $authUrl = $client->createAuthUrl();
                    return response()->json(['login_url' => $authUrl], 200);
                }
            }

            if (!Storage::exists($tokenPath)) {
                Storage::put($tokenPath, json_encode($client->getAccessToken()));
            }
        }

        $service = new \Google_Service_Tasks($client);
        $response = null;

        switch ($request->get('state')) {
            case 'LIST':
                $results = $service->tasklists->listTasklists();

                $response = response()->json([
                    'taskList' => $results->getItems(),
                ], 200);
                break;
            case 'TASKS':
                $taskList = $request->get('taskList');
                $tasks = $service->tasks->listTasks($taskList)->getItems();

                $response = response()->json([
                    'tasks' => $tasks
                ], 200);
                break;
            case 'DELETE':
                $taskList = $request->get('taskList');
                $taskId = $request->get('task');
                $service->tasks->delete($taskList, $taskId);
                $tasks = $service->tasks->listTasks($taskList)->getItems();

                $response = response()->json([
                    'tasks' => $tasks
                ], 200);
                break;
            case 'INSERT':
                $taskList = $request->get('taskList');
                $task = new \Google_Service_Tasks_Task();
                $task->title = $request->get('title');
                $task->notes = $request->get('notes');
                $service->tasks->insert($taskList, $task);

                $tasks = $service->tasks->listTasks($taskList)->getItems();

                $response = response()->json([
                    'tasks' => $tasks
                ], 200);
                break;
        }

        return $response;
    }
}
