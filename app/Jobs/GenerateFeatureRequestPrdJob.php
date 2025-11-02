<?php

namespace App\Jobs;

use App\Models\Conversation;
use App\Models\Document;
use App\Services\LlmService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateFeatureRequestPrdJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Conversation $conversation
    ) {}

    public function handle(LlmService $llmService): void
    {
        // TODO: Re-enable LLM call once end-to-end workflow is verified
        // $messages = $this->conversation->messages()
        //     ->orderBy('created_at')
        //     ->get();
        //
        // $systemPrompt = view('prompts.feature-request-prd')->render();
        //
        // $content = $llmService->generateResponse(
        //     conversation: $this->conversation,
        //     messages: $messages,
        //     systemPrompt: $systemPrompt
        // );

        $applicationName = $this->conversation->application?->name ?? 'Unknown Application';

        $stubContent = <<<MARKDOWN
# Feature Request Document

## Application
{$applicationName}

## Feature Summary
This is a stub feature request document that will be replaced with an LLM-generated document once the workflow is verified.

## Problem Statement
What problem does this feature solve?

## Proposed Solution
How should this feature work?

## User Stories
- As a user, I want...
- So that I can...

## Acceptance Criteria
- Criteria 1
- Criteria 2
- Criteria 3

## Technical Considerations
- Integration points with existing features
- Database changes required
- API changes required

## UI/UX Requirements
- Mockups/wireframes needed
- User flow description

## Testing Requirements
- Unit tests
- Feature tests
- Manual testing steps

## Out of Scope
- What this feature will NOT include

## Open Questions
- Question 1
- Question 2

**Status:** LLM generation pending - this is a stub document
MARKDOWN;

        Document::create([
            'conversation_id' => $this->conversation->id,
            'name' => 'Feature Request Document',
            'content' => $stubContent,
        ]);
    }
}
