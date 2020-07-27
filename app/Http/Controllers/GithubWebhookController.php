<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        $payload = collect(json_decode($request->input('payload'), true));


        if ($payload->get('action') === 'closed' && $payload->get('pull_request.merged') === true) {

            if ($payload->get('pull_request.base.ref') === 'master') {
                // production
                $this->build();
            }

            if ($payload->get('pull_request.base.ref') === 'development') {
                // Development
                $this->build();
            }

        }

        return '';
    }

    protected function build()
    {
        echo 'START BUILD', PHP_EOL;

        $process = new Process([base_path('deploy/deploy.sh'), base_path()]);
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });

        echo 'COMPLETED BUILD', PHP_EOL;
    }

}
