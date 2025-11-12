# Conversation Summary & User Memory Features

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

- [ ] **Database Migration**
  - [ ] Add `summary` text column (nullable) to conversations table
  - [ ] Run migration

- [ ] **Create Job**
  - [ ] Create `app/Jobs/GenerateConversationSummaryJob.php`
  - [ ] Use small model (Haiku) for cost efficiency
  - [ ] Accept `Conversation` in constructor
  - [ ] Load conversation messages ordered chronologically
  - [ ] Create Blade prompt template at `resources/views/prompts/generate-conversation-summary.blade.php`
  - [ ] Prompt instruction: "Summarize this conversation in 3-5 sentences focusing on what was requested and key requirements"
  - [ ] Call LlmService with custom prompt and `useSmallModel: true`
  - [ ] Update conversation's `summary` field
  - [ ] Wrap in try-catch with error logging (pattern from `GenerateConversationTitleJob`)

- [ ] **Integrate into Workflow**
  - [ ] Update `app/Listeners/OrchestrateConversationWorkflow.php`
  - [ ] Add `GenerateConversationSummaryJob` to appropriate Bus batch/chain
  - [ ] Run in parallel with PRD generation

- [ ] **UI Display**
  - [ ] Update `resources/views/livewire/conversation-detail-page.blade.php`
  - [ ] Add summary display at top of Summary section (above Type/User/Created info)
  - [ ] Show as callout or highlighted text for visibility
  - [ ] Handle null case gracefully (show "Summary not yet generated" or hide section)

- [ ] **Update Model**
  - [ ] Add `summary` to `$fillable` array in `app/Models/Conversation.php`

- [ ] **Testing**
  - [ ] Create `tests/Feature/GenerateConversationSummaryJobTest.php`
  - [ ] Test: Job generates summary from conversation messages
  - [ ] Test: Summary is stored in conversation record
  - [ ] Test: Empty conversations handled gracefully
  - [ ] Test: LLM errors logged and do not crash
  - [ ] Update existing conversation tests if needed (mock Queue for observer tests)
  - [ ] Test UI displays summary correctly

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

- [ ] **Database**
  - [ ] Create migration for `user_memories` table
    - [ ] `id`, `user_id` (foreign key, unique), `memory_content` (text), `timestamps`
  - [ ] Run migration

- [ ] **Models**
  - [ ] Create `app/Models/UserMemory.php`
  - [ ] Add `belongsTo(User)` relationship
  - [ ] Set `$fillable = ['user_id', 'memory_content']`
  - [ ] Update `app/Models/User.php`
  - [ ] Add `hasOne(UserMemory)` relationship (`public function memory()`)

- [ ] **Create Job**
  - [ ] Create `app/Jobs/UpdateUserMemoryJob.php`
  - [ ] Use small model (Haiku) for cost efficiency
  - [ ] Accept `Conversation` in constructor
  - [ ] Get user from conversation
  - [ ] Load existing user memory (if exists)
  - [ ] Load conversation messages
  - [ ] Create Blade prompt template at `resources/views/prompts/update-user-memory.blade.php`
  - [ ] Prompt instruction: "You're maintaining aide-mémoire notes about this user to help future conversations. Extract patterns, terminology, preferences, domain context - NOT project details. If existing memory exists, UPDATE/REFINE it rather than duplicating. Keep concise (~1000 tokens)."
  - [ ] Pass existing memory + new conversation to LLM
  - [ ] Call LlmService with custom prompt and `useSmallModel: true`
  - [ ] Upsert UserMemory record (update if exists, create if not)
  - [ ] Wrap in try-catch with error logging

- [ ] **Integrate into Workflow**
  - [ ] Update `app/Listeners/OrchestrateConversationWorkflow.php`
  - [ ] Add `UpdateUserMemoryJob` to appropriate Bus batch
  - [ ] Run in parallel with summary/PRD jobs

- [ ] **Chat Integration**
  - [ ] Update `resources/views/prompts/chat.blade.php`
  - [ ] Add conditional section: if user has memory, include it in system prompt
  - [ ] Format like: "Context about this user: {{ $user->memory->memory_content }}"
  - [ ] Ensure conversation loads user relationship with memory: `$conversation->load('user.memory')`
  - [ ] Update `LlmService::renderChatPrompt()` to eager load user.memory if needed

- [ ] **Testing**
  - [ ] Create `tests/Feature/UpdateUserMemoryJobTest.php`
  - [ ] Test: Creates new memory for user without existing memory
  - [ ] Test: Updates existing memory for user with memory
  - [ ] Test: Memory content is actually updated (not just appended)
  - [ ] Test: LLM errors logged and do not crash
  - [ ] Create `tests/Feature/UserMemoryChatIntegrationTest.php`
  - [ ] Test: Chat prompt includes user memory when it exists
  - [ ] Test: Chat prompt works normally when no memory exists
  - [ ] Update existing tests if needed (mock Queue, etc.)

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
Follow the pattern from `GenerateConversationTitleJob`:
- Wrap LLM calls in try-catch
- Log errors with context (user_id, conversation_id, error message)
- Fail gracefully (do not block workflow if summary/memory fails)

### Queue Strategy
All jobs should:
- Implement `ShouldQueue`
- Run on appropriate queue (consider `short` queue for quick LLM tasks)
- Be added to `OrchestrateConversationWorkflow` in parallel where possible

### Testing Strategy
- Use `Prism::fake()` and `TextResponseFake` for LLM responses
- Test both happy paths and error cases
- Ensure existing tests still pass (add `Queue::fake()` where needed)
- Follow team conventions: test with Eloquent models, not raw DB assertions

---

## Success Criteria

### Conversation Summary
- [ ] Admins can see at-a-glance what each conversation was about
- [ ] Summaries are concise (3-5 sentences) and accurate
- [ ] Summary generation does not block sign-off workflow
- [ ] Errors are logged and do not crash the system

### User Memory
- [ ] Memory accurately captures user context across conversations
- [ ] Memory is updated/refined (not just growing indefinitely)
- [ ] Chat prompts include relevant user context when available
- [ ] Users experience continuity ("Reqqy remembers me")
- [ ] Memory stays concise (~1000 tokens)

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
