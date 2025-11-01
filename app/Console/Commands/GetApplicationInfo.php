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
    protected $signature = 'reqqy:get-application-info {--app-id= : The ID of a specific application} {--all-apps : Process all automated applications}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get information about an application from its .llm.md file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('all-apps')) {
            return $this->handleAllApplications();
        }

        if ($appId = $this->option('app-id')) {
            return $this->handleSingleApplication($appId);
        }

        $this->error('You must specify either --app-id or --all-apps');

        return Command::FAILURE;
    }

    private function handleSingleApplication(int $appId): int
    {
        $app = Application::find($appId);

        if (! $app) {
            $this->error("Application with ID {$appId} not found");

            return Command::FAILURE;
        }

        $this->processApplication($app);

        return Command::SUCCESS;
    }

    private function handleAllApplications(): int
    {
        $applications = Application::where('is_automated', true)->get();

        if ($applications->isEmpty()) {
            $this->info('No automated applications found');

            return Command::SUCCESS;
        }

        $this->info("Processing {$applications->count()} automated application(s)...");

        foreach ($applications as $app) {
            $this->processApplication($app);
        }

        $this->info('Done!');

        return Command::SUCCESS;
    }

    private function processApplication(Application $app): void
    {
        $this->info("Getting information about application {$app->name} (#{$app->id})");
        $this->info("Repo URI: {$app->repo}");

        $overview = $this->getOverview($app->repo);
        $app->overview = $overview;
        $app->save();

        $this->info("Updated overview for {$app->name}");
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

        $llmFilePath = "$repoUri/.llm.md";

        if (! File::exists($llmFilePath)) {
            return '.llm.md does not exist';
        }

        return File::get($llmFilePath);
    }

    private function getGithubOverview(string $repoUri): string
    {
        // TODO: Implement this later
        return '';
    }
}
