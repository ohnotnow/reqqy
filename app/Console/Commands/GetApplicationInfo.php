<?php

namespace App\Console\Commands;

use App\Models\Application;
use Illuminate\Console\Command;

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
            $repoUri = str_replace('file://', '', $repoUri);
            // trim trailing slash
            $repoUri = rtrim($repoUri, '/');
        }

        $overview = $this->getOverviewFromLocalPath($repoUri);
        return $overview;
    }

    private function getOverviewFromLocalPath(string $repoUri): string
    {
        try {
            $contents = file_get_contents("$repoUri/.llm.md");
            return $contents;
        } catch (\Exception $e) {
            return "Error getting overview from local path: {$e->getMessage()}";
        }
    }
}
