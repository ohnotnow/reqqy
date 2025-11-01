<?php

namespace App\Console\Commands;

use App\Models\Application;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GetApplicationInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reqqy:get-application-info {appId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get information about an application';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $appId = $this->argument('appId');
        $app = Application::findOrFail($appId);

        $repoUri = $app->repo;

        $this->info("Getting information about application {$app->name} (#{$app->id})");
        $this->info("Repo URI: {$repoUri}");

        $overview = $this->getOverview($repoUri);
        $app->overview = $overview;
        $app->save();

        return Command::SUCCESS;
    }

    private function getOverview(string $repoUri): string
    {
        // assume repoUrl is a local path
        if (str_starts_with($repoUri, 'file://')) {
            return $this->getOverviewFromLocalPath($repoUri);
        }

        return $this->getGithubOverview($repoUri);
    }

    private function getOverviewFromLocalPath(string $repoUri): string
    {
        $repoUri = str_replace('file://', '', $repoUri);
        $repoUri = rtrim($repoUri, '/');

        $contents = File::exists("$repoUri/.llm.md") ? File::get("$repoUri/.llm.md") : '';

        return $contents;
    }

    private function getGithubOverview(string $repoUri): string
    {
        // TODO: Implement this later
        return '';
    }
}
