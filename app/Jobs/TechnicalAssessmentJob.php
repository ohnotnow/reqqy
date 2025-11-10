<?php

namespace App\Jobs;

use App\DocumentType;
use App\Events\TechnicalAssessmentCompleted;
use App\Models\Conversation;
use App\Models\Document;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class TechnicalAssessmentJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Conversation $conversation
    ) {
        $this->onQueue('research');
    }

    public function handle(): void
    {
        $application = $this->conversation->application;

        $assessment = [
            'size_estimate' => 'M',
            'confidence' => 0.7,
            'impacted_areas' => [
                [
                    'file' => 'app/Http/Controllers/ReportController.php',
                    'reason' => 'Add new export method',
                    'lines' => '45-80',
                ],
                [
                    'file' => 'app/Jobs/ExportJob.php',
                    'reason' => 'Reuse existing job pattern for XLSX export',
                    'lines' => '1-50',
                ],
            ],
            'risks' => [
                'Memory usage on large datasets',
                'Excel formula support unclear',
            ],
            'unknowns' => [
                'Required Excel features beyond basic export?',
                'Max dataset size users will export?',
            ],
            'assumptions' => [
                'Plain data export, no formatting',
                'Existing queue infrastructure can handle file generation',
                'S3 storage available for generated files',
            ],
            'implementation_notes' => 'Similar to CSV export at ReportController.php:120. Can reuse ExportJob pattern with new XLSX driver. Suggest using Laravel Excel package.',
        ];

        $document = Document::create([
            'conversation_id' => $this->conversation->id,
            'type' => DocumentType::TechnicalAssessment,
            'name' => 'Technical Assessment',
            'content' => json_encode($assessment, JSON_PRETTY_PRINT),
            'metadata' => [
                'model' => config('reqqy.llm.default'),
                'prompt_version' => 'v1.0',
                'generated_at' => now()->toIso8601String(),
                'application_id' => $application?->id,
                'repo_path' => $application?->repo,
            ],
        ]);

        TechnicalAssessmentCompleted::dispatch($document);
    }
}
