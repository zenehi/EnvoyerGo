<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Symfony\Component\Process\Process;


class GithubWebhookController extends Controller
{

    public function receive(Request $request)
    {
        $githubPayload = $request->getContent();
        $githubHash = $request->header('X-Hub-Signature');

        $localToken = env('APP_DEPLOY_SECRET');
        $localHash = 'sha1=' . hash_hmac('sha1', $githubPayload, $localToken, false);

        if (!hash_equals($githubHash, $localHash)) {
            return response()->setStatusCode(403)->setContent('Authorization Error');
        }

        // Log info
        $payload = json_decode($request->input('payload'), true);


        if (Arr::get($payload, 'action') === 'closed' && Arr::get($payload, 'pull_request.merged')) {
            $prodBranch = env('APP_DEPLOY_PRODUCTION_BRANCH');
            $devBranch = env('APP_DEPLOY_DEVELOPMENT_BRANCH');

            if (Arr::get($payload, 'pull_request.base.ref') === $prodBranch) {
                // production
                $sourcePath = env('APP_DEPLOY_PRODUCTION_PATH');
                $this->build($sourcePath);
            }

            if (Arr::get($payload, 'pull_request.base.ref') === $devBranch) {
                // Development
                $sourcePath = env('APP_DEPLOY_DEVELOPMENT_PATH');
                $this->build($sourcePath);
            }

        }

        return '';
    }

    protected function build($sourcePath)
    {
        echo 'START BUILD', PHP_EOL;

        echo 'Path: ', $sourcePath, PHP_EOL;

        $process = new Process([base_path('scripts/deploy.sh'), $sourcePath]);
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });

        echo 'COMPLETED BUILD', PHP_EOL;
    }

}
