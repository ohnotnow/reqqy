# Conversation Sign-Off Workflow Refactor

## Executive Summary

This document outlines a refactor to consolidate scattered event listener logic into a single orchestrator that uses Laravel's native job batching and chaining features. The refactor improves code clarity, maintainability, and uses semantic queue names.

**Status:** 🟢 Complete

---

## Background & Motivation

### The Problem

Currently, when a user signs off on a conversation, the workflow logic is **distributed across 4 separate event listeners**:

1. `DispatchResearchAlternatives` - Decides to run research for new apps
2. `GenerateNewApplicationPrd` - Decides to generate PRD for new apps
3. `DispatchTechnicalAssessment` - Decides between technical assessment or direct PRD for features
4. `GenerateFeatureRequestPrd` - Listens to a secondary event to generate PRD after assessment

**Issues with Current Architecture:**

- **Distributed responsibility** - No single place to see the complete workflow logic
- **Overlapping conditionals** - Multiple listeners checking `application_id` and `repo` status
- **Hidden flow control** - PATH 3 buried inside `DispatchTechnicalAssessment` listener
- **Hard to reason about** - Need to trace through multiple files to understand what happens
- **Maintenance burden** - Changes to workflow require updating multiple listeners
- **Unclear queue names** - "research" and "document-generation" don't indicate duration/priority

### What We Want

✅ **Single source of truth** - One place to see all three workflow paths
✅ **Clear path selection** - Explicit logic deciding which workflow to use
✅ **Native Laravel patterns** - Use Bus facade for batches and chains
✅ **Semantic queue names** - Queue names that indicate job duration (short/medium/long)
✅ **Maintainable** - Easy to modify workflows without touching multiple files

---

## The Three Workflow Paths

### PATH 1: New Application Request
**Condition:** `conversation.application_id === null`

**Jobs (parallel batch):**
1. `ResearchAlternativesJob` - Research existing solutions (stub for now)
2. `GenerateNewApplicationPrdJob` - Generate comprehensive PRD

**Queue:** long + medium
**Documents Created:** Research, PRD

---

### PATH 2: Feature Request WITH Repository
**Condition:** `conversation.application_id !== null` AND `application.repo !== null`

**Jobs (sequential chain):**
1. `TechnicalAssessmentJob` - Analyze codebase (stub for now)
2. `GenerateFeatureRequestPrdJob` - Generate PRD with technical context

**Queue:** long → medium
**Documents Created:** Technical Assessment, PRD

---

### PATH 3: Feature Request WITHOUT Repository
**Condition:** `conversation.application_id !== null` AND `application.repo === null`

**Jobs (single-job batch):**
1. `GenerateFeatureRequestPrdJob` - Generate PRD directly

**Queue:** medium
**Documents Created:** PRD

---

## Proposed Solution

### Single Orchestrator Listener

**File:** `app/Listeners/OrchestrateConversationWorkflow.php`

```php
<?php

namespace App\Listeners;

use App\Events\ConversationSignedOff;
use App\Jobs\GenerateFeatureRequestPrdJob;
use App\Jobs\GenerateNewApplicationPrdJob;
use App\Jobs\ResearchAlternativesJob;
use App\Jobs\TechnicalAssessmentJob;
use App\Models\Conversation;
use Illuminate\Bus\Batch;
use Illuminate\Foundation\Bus\PendingChain;
use Illuminate\Support\Facades\Bus;

class OrchestrateConversationWorkflow
{
    public function handle(ConversationSignedOff $event): void
    {
        $conversation = $event->conversation;

        $workflow = $this->getAppropriateWorkflow($conversation);

        $workflow->dispatch();
    }

    private function getAppropriateWorkflow(Conversation $conversation): Batch|PendingChain
    {
        return match (true) {
            // PATH 1: New Application - Research + PRD (parallel)
            $conversation->application_id === null => Bus::batch([
                new ResearchAlternativesJob($conversation),
                new GenerateNewApplicationPrdJob($conversation),
            ])->name("[New App] Research & PRD: Conv {$conversation->id}"),

            // PATH 2: Feature Request with repo - Assessment → PRD (sequential)
            $conversation->application?->repo !== null => Bus::chain([
                new TechnicalAssessmentJob($conversation),
                new GenerateFeatureRequestPrdJob($conversation),
            ])->name("[Feature] Assessment → PRD: Conv {$conversation->id}"),

            // PATH 3: Feature Request without repo - PRD only
            default => Bus::batch([
                new GenerateFeatureRequestPrdJob($conversation),
            ])->name("[Feature] PRD Only: Conv {$conversation->id}"),
        };
    }
}
```

### Design Benefits

1. **Single File** - All workflow logic in one place (~40 lines)
2. **Match Expression** - Clear, exhaustive pattern matching (no nested ifs)
3. **Self-Documenting** - Workflow names explain what each path does
4. **Testable** - Can test workflow selection independently
5. **Type-Safe** - Union type `Batch|PendingChain` ensures consistency
6. **Horizon-Friendly** - Descriptive batch/chain names for monitoring
7. **Consistent Structure** - Even single-job workflows use batches

---

## Queue Renaming Strategy

### Current Queues
- `research` - Codebase analysis, web research
- `document-generation` - LLM-based PRD generation
- (default) - Various jobs without explicit queue

### New Semantic Queues

| Queue Name | Duration | Purpose | Example Jobs |
|------------|----------|---------|--------------|
| **short** | < 30 seconds | Quick tasks, notifications | GenerateConversationTitleJob |
| **medium** | 1-5 minutes | LLM generation, API calls | GenerateNewApplicationPrdJob, GenerateFeatureRequestPrdJob |
| **long** | 5-30+ minutes | Heavy processing, research | ResearchAlternativesJob, TechnicalAssessmentJob |

### Benefits

✅ **Intuitive** - Queue name indicates expected duration
✅ **Resource Allocation** - Can assign different worker counts per queue
✅ **Priority Management** - Can prioritize short jobs for responsiveness
✅ **Monitoring** - Easier to spot bottlenecks ("long queue is backed up")

---

## Implementation Checklist

### Phase 1: Create New Orchestrator

- [ ] Create `app/Listeners/OrchestrateConversationWorkflow.php`
  - [ ] Implement `handle(ConversationSignedOff $event)` method
  - [ ] Implement `getAppropriateWorkflow(Conversation $conversation)` with match expression
  - [ ] Add proper type hints (`Batch|PendingChain` return type)
  - [ ] Add workflow names for each path
- [ ] Run `lando php artisan event:list` to verify new listener is auto-discovered
- [ ] Run `lando pint` to format new file

### Phase 2: Update Job Queue Assignments

- [ ] Update `ResearchAlternativesJob` to use `long` queue
- [ ] Update `TechnicalAssessmentJob` to use `long` queue
- [ ] Update `GenerateNewApplicationPrdJob` to use `medium` queue
- [ ] Update `GenerateFeatureRequestPrdJob` to use `medium` queue
- [ ] Update `GenerateConversationTitleJob` to use `short` queue (if not already)
- [ ] Remove `->onQueue()` calls from all listener dispatch statements (jobs control their queues)
- [ ] Run `lando pint` to format updated files

### Phase 3: Remove Technical Assessment Event

- [ ] Remove `TechnicalAssessmentCompleted` event dispatch from `TechnicalAssessmentJob`
  - [ ] Delete line that fires event (approximately line 75)
  - [ ] Remove `use App\Events\TechnicalAssessmentCompleted;` import
- [ ] Delete `app/Events/TechnicalAssessmentCompleted.php` file
- [ ] Run `lando pint` on modified files

### Phase 4: Delete Old Listeners

- [ ] Delete `app/Listeners/DispatchResearchAlternatives.php`
- [ ] Delete `app/Listeners/GenerateNewApplicationPrd.php`
- [ ] Delete `app/Listeners/DispatchTechnicalAssessment.php`
- [ ] Delete `app/Listeners/GenerateFeatureRequestPrd.php`
- [ ] Run `lando php artisan event:list` to verify only new orchestrator listener remains

### Phase 5: Update Tests

- [ ] Update `tests/Feature/Events/ConversationSignedOffTest.php`
  - [ ] Replace listener-specific assertions with batch/chain assertions
  - [ ] Test PATH 1: Assert batch with 2 jobs dispatched
  - [ ] Test PATH 2: Assert chain with 2 jobs dispatched
  - [ ] Test PATH 3: Assert batch with 1 job dispatched
  - [ ] Test workflow names are correct
  - [ ] Remove tests for deleted listeners
- [ ] Run `lando artisan test --filter=ConversationSignedOffTest` to verify tests pass
- [ ] Update other test files that fake queues or expect old listeners
  - [ ] Search for references to deleted listener class names
  - [ ] Update mocks/fakes as needed
- [ ] Run full test suite: `lando artisan test`
- [ ] Run `lando pint` on test files

### Phase 6: Update Queue Configuration

- [ ] Update `config/queue.php` if needed (add comments for new queues)
- [ ] Update `config/horizon.php` if using Horizon
  - [ ] Add `short`, `medium`, `long` queue definitions
  - [ ] Remove/update `research` and `document-generation` references
  - [ ] Set appropriate worker counts per queue (e.g., short: 5, medium: 3, long: 1)
- [ ] Update any supervisor configs or deployment scripts
- [ ] Document queue purposes in comments

### Phase 7: Documentation & Cleanup

- [ ] Update `PROJECT_PLAN.md` with refactor completion note
- [ ] Search codebase for any remaining references to:
  - [ ] Old listener class names
  - [ ] `TechnicalAssessmentCompleted` event
  - [ ] Old queue names (`research`, `document-generation`)
- [ ] Update any developer documentation or README files
- [ ] Run `lando pint` on entire codebase as final cleanup
- [ ] Mark this document status as 🟢 Complete

### Phase 8: Verification

- [ ] Manually test PATH 1: Create new application conversation, sign off, verify batch in Horizon
- [ ] Manually test PATH 2: Create feature request for app WITH repo, sign off, verify chain in Horizon
- [ ] Manually test PATH 3: Create feature request for app WITHOUT repo, sign off, verify batch in Horizon
- [ ] Check Horizon for successful job execution on correct queues
- [ ] Verify documents are created as expected
- [ ] Verify admin notifications still work

---

## Files Affected

### New Files
- `app/Listeners/OrchestrateConversationWorkflow.php` ✨ NEW

### Modified Files
- `app/Jobs/ResearchAlternativesJob.php` - Update queue assignment
- `app/Jobs/TechnicalAssessmentJob.php` - Remove event dispatch, update queue
- `app/Jobs/GenerateNewApplicationPrdJob.php` - Update queue assignment
- `app/Jobs/GenerateFeatureRequestPrdJob.php` - Update queue assignment
- `app/Jobs/GenerateConversationTitleJob.php` - Update queue assignment (if needed)
- `tests/Feature/Events/ConversationSignedOffTest.php` - Update assertions for batches/chains
- `config/queue.php` - Add comments for new queue names (optional)
- `config/horizon.php` - Update queue configuration (if using Horizon)

### Deleted Files
- `app/Listeners/DispatchResearchAlternatives.php` 🗑️ DELETE
- `app/Listeners/GenerateNewApplicationPrd.php` 🗑️ DELETE
- `app/Listeners/DispatchTechnicalAssessment.php` 🗑️ DELETE
- `app/Listeners/GenerateFeatureRequestPrd.php` 🗑️ DELETE
- `app/Events/TechnicalAssessmentCompleted.php` 🗑️ DELETE

**Net Change:** -5 files, -150+ lines of scattered logic, +1 file, +40 lines of clear orchestration

---

## Testing Strategy

### Unit Tests
**Test File:** `tests/Feature/Listeners/OrchestrateConversationWorkflowTest.php` (NEW)

- Test workflow selection for each path:
  - `it selects new application workflow when application_id is null`
  - `it selects feature with repo workflow when application has repo`
  - `it selects feature without repo workflow when application has no repo`
- Test that correct jobs are included in batch/chain:
  - Assert batch contains ResearchAlternativesJob and GenerateNewApplicationPrdJob
  - Assert chain contains TechnicalAssessmentJob then GenerateFeatureRequestPrdJob
  - Assert batch contains GenerateFeatureRequestPrdJob only
- Test workflow names are descriptive

### Integration Tests
**Test File:** `tests/Feature/Events/ConversationSignedOffTest.php` (UPDATE)

**Remember:** Use Laravel ::fake() calls - don't use Mockery or rewrite Laravel's features.

- Test PATH 1: New application conversation
  - Assert `Bus::batch()` called with 2 jobs
  - Assert batch name matches expected pattern
  - Verify both jobs on correct queues after batch dispatch

- Test PATH 2: Feature request with repo
  - Assert `Bus::chain()` called with 2 jobs
  - Assert chain name matches expected pattern
  - Verify jobs execute sequentially

- Test PATH 3: Feature request without repo
  - Assert `Bus::batch()` called with 1 job
  - Assert batch name matches expected pattern

### Manual Testing Checklist

After deployment, manually verify:

1. **PATH 1 Verification:**
   - Create conversation without application
   - Sign off conversation
   - Check Horizon: Verify batch appears with name "[New App] Research & PRD: Conv {id}"
   - Verify both jobs execute (check `long` and `medium` queues)
   - Verify both documents created (Research + PRD)

2. **PATH 2 Verification:**
   - Create conversation for application WITH repo
   - Sign off conversation
   - Check Horizon: Verify chain appears with name "[Feature] Assessment → PRD: Conv {id}"
   - Verify jobs execute sequentially (Technical Assessment completes before PRD starts)
   - Verify both documents created (Technical Assessment + PRD)

3. **PATH 3 Verification:**
   - Create conversation for application WITHOUT repo
   - Sign off conversation
   - Check Horizon: Verify batch appears with name "[Feature] PRD Only: Conv {id}"
   - Verify single job executes on `medium` queue
   - Verify PRD document created

---

## Queue Worker Configuration

**config/horizon.php:**

```php
'environments' => [
    'production' => [
        'supervisor-1' => [
            'connection' => 'redis',
            'queue' => ['short', 'medium', 'long'],
            'balance' => 'auto',
            'processes' => 10,
            'tries' => 3,
            'maxTime' => 300, // 5 minutes for medium jobs
        ],

        // Dedicated workers for long-running jobs
        'supervisor-long' => [
            'connection' => 'redis',
            'queue' => ['long'],
            'processes' => 2,
            'tries' => 1, // Retry manually if fails
            'maxTime' => 1800, // 30 minutes
        ],
    ],
],
```

## Success Metrics

After refactor completion, verify:

- ✅ All 136+ tests passing
- ✅ Reduced file count (net -5 files)
- ✅ Reduced lines of code (>100 lines eliminated)
- ✅ Single point of workflow logic (1 file vs 4 files)
- ✅ Clear batch/chain names in Horizon UI
- ✅ Queue names are semantic and intuitive
- ✅ No performance regression (same job execution times)
- ✅ Documents still created correctly for all paths
- ✅ Admin notifications still triggered

---

## References

### Laravel Documentation
- Available to you using the laravel boost mcp tool

### Project Documentation
- `PROJECT_PLAN.md` - Original implementation notes
- `app/Livewire/ConversationPage.php:136` - Sign-off trigger point
- `tests/Feature/Events/ConversationSignedOffTest.php` - Integration tests

### Related Code
- `app/Events/ConversationSignedOff.php` - Event class
- `app/Models/Conversation.php` - Conversation model
- `app/DocumentType.php` - Document type enum

---

## Questions or Issues?

If you encounter issues during refactor:

1. **Tests failing?** - Check that all queue fakes are updated in tests. Add debug log/dump calls to help you figure the issue out
2. **Jobs not executing?** - Verify workers are listening to new queue names
3. **Batches not appearing in Horizon?** - Ensure Horizon config includes new queues
4. **Event listener not firing?** - Run `php artisan event:list` to verify auto-discovery

---

**Last Updated:** 2025-11-11
**Status:** 🟢 Complete
**Actual Time:** ~2 hours
**Risk Level:** 🟡 Medium (comprehensive tests provided safety net)
