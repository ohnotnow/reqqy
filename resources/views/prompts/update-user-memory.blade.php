You are maintaining aide-mémoire notes about {{ $user->username }} to help provide continuity in future conversations with this user.

Your task is to extract and maintain contextual intelligence about THIS USER (not about their projects or requests).

## What to Capture

**Terminology & Language:**
- Special terms the user uses ("the main export", "cohort tracker")
- How they refer to systems, teams, or processes
- Technical vocabulary level and communication style

**Domain Context:**
- What sector/field they work in (education, healthcare, etc.)
- Their role and responsibilities
- Systems and tools they commonly work with

**Preferences & Patterns:**
- How they approach problems (incremental vs. big changes)
- What they value (compliance, simplicity, reporting, etc.)
- Recurring concerns or priorities

**Environment & Constraints:**
- Tech stack or platforms mentioned
- Team composition or organizational structure
- Integration points or dependencies

**Relationships Between Systems:**
- How their applications connect or depend on each other
- Cross-system workflows or data flows

## What NOT to Capture

❌ Project details ("They requested a bulk upload feature for demonstrators")
❌ Specific feature requirements ("The system needs CSV validation")
❌ Implementation specifics ("Should use Laravel and MySQL")
❌ Dates, deadlines, or temporary states

✅ DO capture: "Billy works in higher education IT, manages multiple student-facing systems, values simplicity and maintainability, refers to the student records export as 'the main export'"
❌ DON'T capture: "Billy requested a student import feature on Nov 12th with CSV support"

@if($existingMemory)
## Existing Memory

Below is what we already know about {{ $user->username }}. Your job is to UPDATE and REFINE this memory based on the new conversation, NOT to append or duplicate information.

- Remove outdated information
- Strengthen patterns with more evidence
- Add new insights that weren't previously captured
- Keep it concise and well-organized

EXISTING MEMORY:
{{ $existingMemory }}

@else
## New User

This is the first conversation with {{ $user->username }}. Create initial memory notes from scratch.

@endif

## Recent Conversation

@if($conversation->application_id && $conversation->application)
Context: This was a feature request for {{ $conversation->application->name }}.
@else
Context: This was a new application request.
@endif

@foreach ($messages as $message)
{{ $message->isFromUser() ? 'User' : 'Reqqy' }}: {{ $message->content }}

@endforeach

## Instructions

@if($existingMemory)
Review the existing memory and the new conversation above. Update the memory to reflect any new patterns, terminology, preferences, or context about {{ $user->username }}.

- REFINE existing points rather than duplicating
- REMOVE anything that now seems incorrect or outdated
- ADD genuinely new insights
- Keep the entire memory under ~1000 tokens
- Organize clearly with bullet points or short paragraphs
@else
Based on this first conversation, create initial memory notes about {{ $user->username }}.

- Extract patterns, terminology, domain context
- Keep concise and well-organized
- Focus on the USER, not the project
- Use bullet points or short paragraphs
@endif

Respond with ONLY the updated memory content. No preamble, no quotes, no meta-commentary.
