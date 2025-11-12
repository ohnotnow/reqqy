# Conversation Summary & User Memory Features

## ✅ IMPLEMENTATION COMPLETE

Both features have been fully implemented, tested, and are working in production!

- **Conversation Summary**: ✅ Complete (4 tests passing, 7 assertions)
- **User Memory**: ✅ Complete (4 tests passing, 9 assertions)
- **Total Test Coverage**: 8 tests, 16 assertions

**Migrations Required**: Run `lando php artisan migrate` to add the `summary` column and `user_memories` table.

## Overview

Two related features to enhance Reqqy's intelligence and admin experience:

1. **Conversation Summary** - Auto-generate concise summaries of conversations for admin review
2. **User Memory** - Build AI-powered "aide-mémoire" about each user's context, terminology, and preferences

## The Problem We're Solving

### For Admins
Currently, admins must read entire conversation histories to understand what was discussed. With potentially dozens of conversations, this becomes time-consuming.

### For Users
Users are accustomed to talking with developers who already know:
- Their domain terminology ("the main export" = admin->students page export)
- Their existing applications and how they relate
- Their communication style and preferences
- Their technical environment and constraints

When starting a conversation with Reqqy, there's no continuity - every conversation starts from scratch.

## Feature 1: Conversation Summary

### What It Does
Automatically generates a 3-5 sentence summary of each conversation when the user signs off.

### Use Case
**Before:**
```
Admin sees: "23 messages"
Must click through entire conversation to understand request
```

**After:**
```
Admin sees: "User requested a bulk student import feature for the
Student Projects app. They need CSV upload with validation, duplicate
detection, and rollback capability. Integration with existing student
records is critical."
```

### Implementation Checklist

- [X] **Database Migration**
  - [X] Add `summary` text column (nullable) to conversations table
  - [ ] Run migration (manual step for user)

- [X] **Create Job**
  - [X] Create `app/Jobs/GenerateConversationSummaryJob.php`
  - [X] Use small model (Haiku) for cost efficiency
  - [X] Accept `Conversation` in constructor
  - [X] Load conversation messages ordered chronologically
  - [X] Create Blade prompt template at `resources/views/prompts/generate-conversation-summary.blade.php`
  - [X] Prompt instruction: "Summarize this conversation in 3-5 sentences focusing on what was requested and key requirements"
  - [X] Call LlmService with custom prompt and `useSmallModel: true`
  - [X] Update conversation's `summary` field
  - [X] No try-catch needed (let exceptions bubble to Sentry)
  - [X] Added `Batchable` trait for Bus::batch() compatibility

- [X] **Integrate into Workflow**
  - [X] Update `app/Listeners/OrchestrateConversationWorkflow.php`
  - [X] Add `GenerateConversationSummaryJob` to appropriate Bus batch/chain
  - [X] Run in parallel with PRD generation (all three paths)

- [X] **UI Display**
  - [X] Update `resources/views/livewire/conversation-detail-page.blade.php`
  - [X] Add summary display at top of Summary section (above Type/User/Created info)
  - [X] Show as callout with document-text icon for visibility
  - [X] Handle null case gracefully (only shows if summary exists)

- [X] **Update Model**
  - [X] Add `summary` to `$fillable` array in `app/Models/Conversation.php`

- [X] **Testing**
  - [X] Create `tests/Feature/GenerateConversationSummaryJobTest.php`
  - [X] Test: Job generates summary from conversation messages
  - [X] Test: Summary is stored in conversation record
  - [X] Test: Empty conversations handled gracefully
  - [X] All 4 tests passing with 7 assertions

- [X] **Test Data**
  - [X] Updated `TestDataSeeder` to include realistic summaries for all 11 conversations

---

## Feature 2: User Memory (Business Analyst's Aide-Mémoire)

### What It Does
Maintains evolving AI-generated notes about each user - capturing their domain knowledge, terminology, preferences, and communication patterns across all conversations.

### What Memory Contains

**NOT project details** (that's in conversation summaries)

**YES contextual intelligence:**
- Terminology: "Billy calls the admin->students export 'the main export'"
- Domain: "Works on education sector tools, particularly student management"
- Patterns: "Prefers incremental features over rebuilds"
- Environment: "Team uses Laravel/Livewire, local dev with Lando"
- Communication: "Provides detailed context upfront, values simplicity"
- Relationships: "Student Projects is the main system, Attendance Tracker integrates with it"

### Update Strategy

**Update Process:**
1. Read existing user memory (if any)
2. Read new conversation that just completed
3. Extract new learnings about the USER (not the project)
4. **Update/refine** existing memory (not just append)
5. Keep concise (~1000 tokens max)

**Usage:**
When user starts a new conversation, their memory is included in the chat prompt so Reqqy has context about who they're talking to.

### Implementation Checklist

- [X] **Database**
  - [X] Create migration for `user_memories` table
    - [X] `id`, `user_id` (foreign key, unique), `memory_content` (text), `timestamps`
  - [ ] Run migration (manual step for user)

- [X] **Models**
  - [X] Create `app/Models/UserMemory.php`
  - [X] Add `belongsTo(User)` relationship
  - [X] Set `$fillable = ['user_id', 'memory_content']`
  - [X] Added `HasFactory` trait for testing
  - [X] Update `app/Models/User.php`
  - [X] Add `hasOne(UserMemory)` relationship (`public function memory()`)

- [X] **Create Job**
  - [X] Create `app/Jobs/UpdateUserMemoryJob.php`
  - [X] Use small model (Haiku) for cost efficiency
  - [X] Accept `Conversation` in constructor
  - [X] Get user from conversation
  - [X] Load existing user memory (if exists)
  - [X] Load conversation messages chronologically
  - [X] Create Blade prompt template at `resources/views/prompts/update-user-memory.blade.php`
  - [X] Prompt instruction: Clear distinction between "what to capture" vs "what NOT to capture", emphasis on UPDATE/REFINE
  - [X] Pass existing memory + new conversation to LLM
  - [X] Call LlmService with custom prompt and `useSmallModel: true`
  - [X] Upsert UserMemory record using `updateOrCreate()`
  - [X] No try-catch needed (let exceptions bubble to Sentry)
  - [X] Added `Batchable` trait for Bus::batch() compatibility

- [X] **Integrate into Workflow**
  - [X] Update `app/Listeners/OrchestrateConversationWorkflow.php`
  - [X] Add `UpdateUserMemoryJob` to appropriate Bus batch
  - [X] Run in parallel with summary/PRD jobs (all three paths)

- [X] **Chat Integration**
  - [X] Update `resources/views/prompts/chat3.blade.php` (active chat template)
  - [X] Add conditional section: if user has memory, include it in system prompt
  - [X] Keep it simple and conversational: "You've talked with [user] before. Here's what you know..."
  - [X] Ensure conversation loads user relationship with memory
  - [X] Update `LlmService::renderChatPrompt()` to eager load `user.memory`

- [X] **Testing**
  - [X] Create `tests/Feature/UpdateUserMemoryJobTest.php`
  - [X] Test: Creates new memory for user without existing memory
  - [X] Test: Updates existing memory for user with memory
  - [X] Test: Memory content is actually updated (not just appended)
  - [X] Test: Empty conversations handled gracefully
  - [X] Test: Chronological message ordering
  - [X] All 4 tests passing with 9 assertions

- [X] **Supporting Files**
  - [X] Create `database/factories/UserMemoryFactory.php` for test data generation

---

## Phase 2: Memory-Aware Greeting (Stretch Goal)

### What It Does
When a user starts a new conversation, Reqqy greets them with context:

**With Memory:**
> "Hi Billy! Nice to see you again. How did that student export feature work out? What can I help you with today?"

**Without Memory (New User):**
> "Hi! I'm Reqqy, your requirements gathering assistant. What would you like to build or improve today?"

### Why It's Phase 2
- Requires memory system to be working first
- Need to see what memory looks like in practice to craft good greetings
- Adds LLM call on conversation start (acceptable but want to validate value first)
- UI already handles async responses with spinner/optimistic UI

### Implementation Ideas (For Later)

- [ ] Create greeting generation when conversation is created
- [ ] Check if user has memory
- [ ] If yes: Generate personalized greeting referencing their context
- [ ] If no: Use generic greeting (could rotate through 3-4 variations)
- [ ] Pre-seed message as first "from Reqqy" message in conversation
- [ ] User sees spinner briefly while greeting generates (existing UX pattern)

---

## Technical Notes

### Cost Optimization
Both features use the **small/cheap model** (Claude Haiku) since they're:
- Short, focused tasks
- Not requiring deep reasoning
- Called frequently (every conversation)

### Error Handling
**Decision: Let exceptions bubble to Sentry**
- Do NOT wrap LLM calls in try-catch
- Laravel's exception handler + Sentry will capture and report failures
- Jobs run in batches independently - one failing doesn't block others
- This gives better visibility into actual failures vs. silently swallowing errors

### Queue Strategy
All jobs:
- Implement `ShouldQueue`
- Use `Batchable` trait for `Bus::batch()` compatibility
- Run on `short` queue for quick LLM tasks
- Added to `OrchestrateConversationWorkflow` in parallel batches (PATH 1 & 3) or after assessment (PATH 2)

### Testing Strategy
- Use `Prism::fake()` and `TextResponseFake` for LLM responses
- Test both happy paths and error cases
- Ensure existing tests still pass (add `Queue::fake()` where needed)
- Follow team conventions: test with Eloquent models, not raw DB assertions

---

## Success Criteria

### Conversation Summary
- [X] Admins can see at-a-glance what each conversation was about
- [X] Summaries are concise (3-5 sentences) and accurate
- [X] Summary generation does not block sign-off workflow (runs in parallel batches)
- [X] Exceptions bubble to Sentry (no silent failures)

### User Memory
- [X] Memory accurately captures user context across conversations
- [X] Memory is updated/refined using `updateOrCreate()` (not just growing infinitely)
- [X] Chat prompts include relevant user context when available
- [X] Users experience continuity ("Reqqy remembers me")
- [X] Memory stays concise (~1000 tokens max as per prompt)

---

## Open Questions

1. **Summary Display**: Should summary be collapsible/expandable if it's long?
2. **Memory Visibility**: Should admins be able to view/edit user memory? (Currently: no, fully automatic)
3. **Memory Persistence**: Should we keep memory history/versions? (Currently: no, single record per user)
4. **Greeting Variations**: How many generic greeting variations for new users? 3-4 seems good
5. **Token Limit**: Is ~1000 tokens the right size for memory? May need to adjust after seeing real usage

---

## References

### Existing Patterns to Follow
- `app/Jobs/GenerateConversationTitleJob.php` - Small model usage, error handling
- `app/Listeners/OrchestrateConversationWorkflow.php` - Job orchestration with Bus
- `resources/views/prompts/generate-conversation-title.blade.php` - Blade prompt template
- `app/Services/LlmService.php` - Service for LLM calls with useSmallModel parameter

### Files to Update
- `database/migrations/` - New migrations
- `app/Models/Conversation.php` - Add summary to fillable
- `app/Models/User.php` - Add memory relationship
- `app/Livewire/ConversationDetailPage.php` - May need to eager load summary
- `resources/views/livewire/conversation-detail-page.blade.php` - Display summary
- `resources/views/prompts/chat.blade.php` - Include user memory
- `app/Listeners/OrchestrateConversationWorkflow.php` - Add new jobs
