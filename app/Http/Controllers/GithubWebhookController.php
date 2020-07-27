<?php

namespace App\Http\Controllers;

use App\Services\Discord;
use Dotenv\Dotenv;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Symfony\Component\Process\Process;


class GithubWebhookController extends Controller
{
    /**
     * @var array
     */
    protected $payload;

    /**
     * @var Discord
     */
    protected $_discord;


    public function receive(Request $request)
    {
        $githubPayload = $request->getContent();
        $githubHash = $request->header('X-Hub-Signature');

        $localToken = env('APP_DEPLOY_SECRET');
        $localHash = 'sha1=' . hash_hmac('sha1', $githubPayload, $localToken, false);

        if (!hash_equals($githubHash, $localHash)) {
            return response('Authorization Error', 403);
        }

        // Log info
        $payload = json_decode($request->input('payload'), true);
        $this->payload = $payload;


        if (Arr::get($payload, 'action') === 'closed' && Arr::get($payload, 'pull_request.merged')) {
            $prodBranch = env('APP_DEPLOY_PRODUCTION_BRANCH');
            $devBranch = env('APP_DEPLOY_DEVELOPMENT_BRANCH');

            if (Arr::get($payload, 'pull_request.base.ref') === $prodBranch) {
                // production
                $sourcePath = env('APP_DEPLOY_PRODUCTION_PATH');
                return $this->build($sourcePath);
            }

            if (Arr::get($payload, 'pull_request.base.ref') === $devBranch) {
                // Development
                $sourcePath = env('APP_DEPLOY_DEVELOPMENT_PATH');
                return $this->build($sourcePath);
            }

        }


        echo 'Nothing todo!', PHP_EOL;

        return response('Nothing Todo!', 201);
    }

    protected function build($sourcePath)
    {
        echo 'START BUILD', PHP_EOL;

        $this->sendToLog('START building `' . Arr::get($this->payload, 'pull_request.base.ref') . '`');

        echo 'Path: ', $sourcePath, PHP_EOL;

        $dotEnv = Dotenv::createImmutable($sourcePath);
        $envVars = $dotEnv->safeLoad();

        $process = new Process(['/bin/bash', base_path('scripts/deploy.sh'), $sourcePath], null, $envVars);
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });

        $this->sendToLog('COMPLETED building `' . Arr::get($this->payload, 'pull_request.base.ref') . '`');

        echo 'COMPLETED BUILD', PHP_EOL;
    }

    protected function sendToLog($msg)
    {
        try {

            $this->getDiscordService()->sendMessage($msg);

        } catch (\Exception $e) {
            //
        }
    }

    protected function getDiscordService()
    {
        if ($this->_discord === null) {
            $this->_discord = new Discord(env('LOG_DISCORD_WEBHOOK_URL'));
        }

        return $this->_discord;
    }

}
