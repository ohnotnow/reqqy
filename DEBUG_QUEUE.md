# Queue Timeout Debugging Log

**Problem**: `GenerateNewApplicationPrdJob` is timing out at 30 seconds despite configuration changes.

**Date**: 2025-11-10
**Status**: 🔴 STILL FAILING - 30s timeout persists

---

## The Problem

When signing off a conversation, the PRD generation job fails with:
```
2025-11-10 19:23:39 App\Jobs\GenerateNewApplicationPrdJob ......... 30s FAIL
```

The job needs ~60-120 seconds to complete (calling OpenAI/Anthropic with large PRD generation).

---

## Understanding Laravel Queue Timeouts

There are **MULTIPLE LAYERS** of timeouts that all must be aligned:

### Layer 1: PHP `max_execution_time`
- **CLI Mode**: `0` (unlimited) ✅
- **Not the problem**: PHP won't kill the process

### Layer 2: Redis Queue `retry_after`
- **Location**: `config/queue.php` line 71
- **Previous Value**: `90` seconds
- **New Value**: `930` seconds (updated 2025-11-10)
- **Rule**: MUST be greater than longest job timeout
- **Status**: ✅ FIXED

### Layer 3: Horizon Supervisor Timeout
- **Location**: `config/horizon.php` lines 199-239
- **Values**:
  - Default queue: `60` seconds
  - Document-generation queue: `180` seconds (3 minutes)
  - Research queue: `900` seconds (15 minutes)
- **Status**: ✅ CONFIGURED CORRECTLY

### Layer 4: Individual Job Timeout
- **Location**: Can be set on individual job classes with `public $timeout = X;`
- **Current**: Not set (inherits from Horizon)
- **Status**: ✅ Not needed (inheriting 180s from Horizon)

---

## What We've Tried

### ✅ Attempt 1: Fixed Max Tokens
**Issue**: Jobs were failing with "max tokens exceeded" error.
**Solution**:
- Updated `LlmService` to use `->withMaxTokens($maxTokens)`
- Set default to 100,000 tokens
- Added config: `config/reqqy.max_tokens.prd = 100000`
**Result**: ✅ FIXED - No more token errors

### ✅ Attempt 2: Created Dedicated Queues
**Issue**: All jobs using default 60s timeout.
**Solution**:
- Created `document-generation` queue (180s timeout)
- Created `research` queue (900s timeout)
- Updated jobs to use `$this->onQueue('queue-name')`
**Files Changed**:
- `config/horizon.php` - Added supervisor-documents and supervisor-research
- `GenerateNewApplicationPrdJob.php` - Line 20: `$this->onQueue('document-generation')`
- `GenerateFeatureRequestPrdJob.php` - Line 20: `$this->onQueue('document-generation')`
- `ResearchAlternativesJob.php` - Line 18: `$this->onQueue('research')`
- `TechnicalAssessmentJob.php` - Line 19: `$this->onQueue('research')`
**Result**: ✅ CONFIGURED - But still seeing 30s timeout

### ✅ Attempt 3: Fixed Redis `retry_after`
**Issue**: `retry_after` (90s) was less than job timeout (180s), causing Redis to abandon jobs prematurely.
**Solution**:
- Updated `config/queue.php` line 71
- Changed from: `'retry_after' => (int) env('REDIS_QUEUE_RETRY_AFTER', 90)`
- Changed to: `'retry_after' => (int) env('REDIS_QUEUE_RETRY_AFTER', 930)`
**Result**: ✅ FIXED - But still seeing 30s timeout (?!)

### ⏳ Attempt 4: Restart Horizon
**Action Required**: Run `lando artisan horizon:terminate`
**Status**: ⚠️ NEEDS TO BE DONE
**Why**: Config changes only take effect after Horizon restarts

---

## Current Configuration State

### Horizon Config (`config/horizon.php`)
```php
'defaults' => [
    'supervisor-1' => [
        'queue' => ['default'],
        'timeout' => 60,
    ],
    'supervisor-documents' => [
        'queue' => ['document-generation'],
        'timeout' => 180,  // 3 minutes
    ],
    'supervisor-research' => [
        'queue' => ['research'],
        'timeout' => 900,  // 15 minutes
    ],
],
```

### Queue Config (`config/queue.php`)
```php
'redis' => [
    'retry_after' => (int) env('REDIS_QUEUE_RETRY_AFTER', 930),
],
```

### Job Configuration
```php
// GenerateNewApplicationPrdJob.php (line 17-21)
public function __construct(
    public Conversation $conversation
) {
    $this->onQueue('document-generation');
}
```

---

## Mystery: Why Still 30 Seconds?

**Theories to investigate**:

1. **Horizon hasn't restarted** ⚠️ MOST LIKELY
   - Old Horizon process still running with old config
   - **Next step**: `lando artisan horizon:terminate` and verify new supervisors are running

2. **Different timeout source**
   - Some other config or Docker timeout we haven't found
   - Check Lando/Docker container timeouts
   - Check nginx/php-fpm timeouts (if applicable)

3. **Job isn't actually using the queue**
   - Verify in Horizon UI that job appears in `document-generation` queue
   - Check logs to confirm which queue it's running on

4. **Environment variable override**
   - Check `.env` file for any queue timeout settings
   - `REDIS_QUEUE_RETRY_AFTER` or similar

5. **Lando-specific timeout**
   - Check `.lando.yml` for PHP timeout settings
   - Check Docker container resource limits

---

## Debugging Tools & Commands

### Check Horizon Status
```bash
lando artisan horizon:status
```

### Restart Horizon
```bash
lando artisan horizon:terminate
# Horizon will auto-restart
```

### Check Queue in Horizon UI
Visit: `/horizon` and look for:
- Active supervisors (should see 3: default, documents, research)
- Which queue the job is running in
- Actual timeout value being used

### Check Config Values
```bash
lando artisan tinker
>>> config('horizon.defaults.supervisor-documents.timeout')
=> 180
>>> config('queue.connections.redis.retry_after')
=> 930
```

### Manual Job Dispatch (Testing)
```bash
lando artisan tinker
>>> $conversation = App\Models\Conversation::find(12);
>>> App\Jobs\GenerateNewApplicationPrdJob::dispatch($conversation);
```

### Check Logs
```bash
lando artisan tinker
>>> App\Models\Conversation::find(12)->messages()->count();
# Should show how many messages to process
```

---

## Logging Added

We added detailed logging to both PRD jobs:
```php
Log::info('Generating New Application PRD', [
    'conversation_id' => $this->conversation->id,
    'system_prompt_chars' => strlen($systemPrompt),
    'message_count' => $messages->count(),
    'total_message_chars' => $totalMessageChars,
    'total_input_chars' => strlen($systemPrompt) + $totalMessageChars,
]);
```

**Check logs**: `storage/logs/laravel.log` for these entries before job fails.

---

## Next Steps (Post-Noodles 🍜)

1. **Restart Horizon** (CRITICAL - do this first!)
   ```bash
   lando artisan horizon:terminate
   ```

2. **Verify Horizon is running new config**
   - Visit `/horizon` dashboard
   - Check that 3 supervisors are active
   - Confirm `supervisor-documents` shows timeout of 180

3. **Re-trigger the job**
   - Use the temporary "Sign Off (Re-trigger)" button on conversation
   - Watch in Horizon UI which queue it goes to

4. **If still 30s timeout**:
   - Check `.lando.yml` for PHP timeouts
   - Check environment variables in `.env`
   - Check Horizon logs: `storage/logs/horizon.log`
   - Check container logs: `lando logs -s appserver`

5. **Consider adding explicit timeout to job**
   ```php
   // In GenerateNewApplicationPrdJob
   public $timeout = 180;
   ```

---

## Related Files

- `config/horizon.php` - Horizon supervisor configuration
- `config/queue.php` - Queue driver configuration
- `app/Jobs/GenerateNewApplicationPrdJob.php` - The failing job
- `app/Jobs/GenerateFeatureRequestPrdJob.php` - Also uses document-generation queue
- `app/Services/LlmService.php` - LLM integration with token limits
- `.lando.yml` - Docker/Lando configuration (check for timeouts)

---

## Questions to Answer

- [ ] Has Horizon been restarted since config changes?
- [ ] Does Horizon UI show 3 supervisors running?
- [ ] Which queue is the job actually running in? (check Horizon UI)
- [ ] Are there any Lando/Docker timeouts overriding these settings?
- [ ] Is there a `.env` override we're missing?

---

**Good luck, Future Billy! Enjoy those noodles! 🌶️🍜**
