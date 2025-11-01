# Strategic Review: Reqqy Application Architecture

**Date:** 2025-11-01
**Purpose:** Strategic analysis of business logic, data flow, and conceptual design

---

## Executive Summary

The Reqqy application has a **fundamental conceptual question** about what "Application" represents in the mental model versus what it represents in the data model. Through discussion, we've clarified the vision, but implementation needs to catch up with that vision.

---

## The Core Question: What Is an Application?

### The Confusion

When someone clicks "New Application" and starts describing what they want built, should that create an Application record in the database immediately, eventually, or never?

The answer depends on what "Application" means in your domain.

### The Clarified Vision (Updated)

After extensive discussion, the mental model has evolved:

**Applications = Three distinct categories in a shared catalog**

1. **Internal Applications** - Apps you own/manage in your portfolio
   - Have repos, URLs, can be automated with `.llm.md` fetching
   - Users can request features for these
   - Created manually by admins OR promoted from Proposed state

2. **External Applications** - Third-party SaaS/tools (reference only)
   - Examples: Slack, Notion, existing market solutions
   - Have URLs and descriptions but no repo access
   - LLM uses these to suggest alternatives ("Why not use book-a-room.app?")
   - Users redirected to helpdesk if they try to request features

3. **Proposed Applications** - Ideas from conversations awaiting approval
   - Born from "New Application" conversations
   - Vetted by admin but not built yet
   - Can be promoted to Internal or rejected
   - Keeps Settings clean (separate from production portfolio)

**Key Insight:** All three categories exist in the same `Application` model, differentiated by a `category` field (avoiding reserved keyword `type`).

---

## Current vs Intended User Journeys

### Path 1: New Feature Request (✅ Works correctly)

```
1. User has an idea: "Add shopping cart to MyShop"
2. Clicks "New Feature"
3. Selects "MyShop" from dropdown (global catalog of all Applications)
4. Chats with BA assistant about the feature
5. Signs off
6. Admin reviews conversation + generated PRD
7. ConversationStatus: Pending → InReview → Approved → Completed
8. Dev team implements the feature
```

**Data model:** `Conversation.application_id` points to existing Application

**Status:** ✅ This flow works perfectly as-is

---

### Path 2: New Application Request (⚠️ Needs implementation)

```
1. User has an idea: "A marketplace for vintage guitars"
2. Clicks "New Application"
3. Chats with BA assistant about the concept
   - LLM has context of ALL applications (Internal, External, Proposed)
   - May suggest: "Have you considered GuitarCenter.com?" (External alternative)
4. User continues with their unique vision, signs off
5. Admin reviews conversation + generated PRD
6. Admin approves: ConversationStatus → Approved
7. 🎯 MISSING: Application record auto-created (category = Proposed)
8. Proposed Application appears in Settings → "Proposed Applications" section
9. Admin reviews proposal, clicks "Approve for Development"
10. Application.category changes: Proposed → Internal
11. Dev team builds it over time
12. Admin updates Application: url, repo, status = Active
13. Eventually: ConversationStatus → Completed
```

**Data model:** `Conversation.application_id` starts as `NULL`, gets linked to Proposed Application

**Status:** ⚠️ The conversation part works, but Application lifecycle needs implementation

---

## Access Control & Page Purposes

### HomePage
- **Audience:** All users
- **Purpose:** View your own conversation history (recent requests)
- **Shows:** User's conversations with status, type, dates
- **Current status:** ✅ Exists and works

### Settings Page
- **Audience:** Admin/manager users only
- **Purpose:** Manage the global application catalog (all three categories)
- **Shows:** Three sections/tabs:
  - **Internal Applications** - Production portfolio (can CRUD, set is_automated)
  - **External Applications** - Third-party references (simpler form: name/url/description)
  - **Proposed Applications** - Awaiting promotion (read-only, buttons: Approve/Reject)
- **Current status:** ⚠️ No authorization check - any user can access

### Conversation Admin Pages
- **Audience:** Admin users only
- **Purpose:** Review conversations, manage status, access documents
- **Shows:** All conversations from all users
- **Current status:** ✅ Has proper authorization via ConversationPolicy

---

## The Application Lifecycle (Intended)

### For Internal Applications (Manual Creation)
```
Admin creates Application in Settings (category = Internal)
  ↓
Application appears in "New Feature" dropdown
  ↓
Users can request features for it
  ↓
Conversations link to Application via application_id
  ↓
LLM has full context (repo overview, etc.) for feature discussions
```

### For External Applications (Manual Creation)
```
Admin creates Application in Settings (category = External)
  ↓
Application included in LLM context for ALL conversations
  ↓
When user describes similar need, LLM suggests: "Have you considered X?"
  ↓
If user tries "New Feature" on External app → blocked with helpful message
  ↓
User redirected to helpdesk for SaaS feature requests/config
```

### For Proposed Applications (Conversation-Driven - Two-Step Process)
```
STEP 1: Proposal Creation
───────────────────────────
User starts "New Application" conversation
  ↓
Conversation created with application_id = NULL
  ↓
User chats with BA (LLM aware of all Internal/External apps), signs off
  ↓
Admin reviews, changes ConversationStatus to "Approved"
  ↓
🎯 Observer detects status change → auto-creates Application record
  ↓
Application created with:
  - category = Proposed
  - source_conversation_id = X
  - name = (extracted from conversation/PRD)
  - url/repo = NULL (doesn't exist yet)
  ↓
Conversation.application_id updated to link to new Proposed Application
  ↓
Proposed Application appears in Settings → "Proposed Applications" section

STEP 2: Promotion to Internal
──────────────────────────────
Admin reviews proposal in Settings
  ↓
Admin clicks "Approve for Development" button
  ↓
Application.category changes: Proposed → Internal
  ↓
Application moves to "Internal Applications" section
  ↓
Admin fills in additional details (url, repo, is_automated, status)
  ↓
Now appears in "New Feature" dropdown for feature requests
  ↓
Dev team builds it over time
  ↓
Eventually: url/repo updated, status = Active
```

**Alternative: Rejection Flow**
```
Admin reviews proposal in Settings
  ↓
Admin realizes it duplicates existing External app
  ↓
Admin clicks "Reject" button
  ↓
Application record deleted (or marked as rejected)
  ↓
Admin adds note to conversation explaining why
```

---

## Strategic Decisions Made

### Decision 1: Three-Category Application Model
**Choice:** Single `Application` model with `category` field (Internal, External, Proposed) instead of separate models.

**Rationale:**
- LLM needs all applications in single context for suggestions
- Natural lifecycle: Proposed → Internal (same record, just category change)
- Simpler queries and relationships
- Settings page shows all categories with different UI sections

**Implications:**
- Some fields are category-specific (nullable with conditional validation)
- Helper methods determine behavior: `canHaveFeaturesRequested()`, `isProposal()`
- UI must handle three distinct use cases in one Settings page

### Decision 2: Proposed State as Buffer/Staging
**Choice:** Auto-created applications start as "Proposed", require explicit promotion to "Internal".

**Rationale:**
- Prevents Settings pollution with unbuilt apps
- Gives admin control over what enters production portfolio
- "New Feature" dropdown only shows Internal (real apps you can enhance)
- Clear distinction between "approved idea" and "production app"

**Implications:**
- Two-step workflow: approve conversation → review proposal → promote to internal
- Admin explicitly decides when proposal becomes portfolio item
- Can reject proposals without cluttering Internal catalog

### Decision 3: External Applications for LLM Context
**Choice:** Admin can add third-party SaaS/tools as External applications for LLM awareness.

**Rationale:**
- Prevents duplicate requests ("build Slack" when you already use Slack)
- LLM can suggest existing solutions during conversations
- Reduces wasted PRD generation for solved problems

**Implications:**
- External apps appear in LLM prompts but not in "New Feature" dropdown
- Feature requests blocked for External apps, redirect to helpdesk
- Simpler form in Settings (no repo/automation fields)

### Decision 4: Settings Is Admin-Only
**Choice:** Settings page is for admin/manager users to manage the global catalog.

**Implications:**
- Regular users don't access Settings
- Users see their conversations on HomePage instead
- Application catalog is centrally managed
- Need to add authorization check to Settings page

### Decision 5: All Applications Are Global
**Choice:** When requesting features, users can select from ALL Internal applications (no ownership model).

**Implications:**
- Applications table doesn't need `user_id` or ownership
- Any user can request features for any Internal application
- Simpler data model
- Access control happens at conversation level (users own their conversations)

### Decision 6: Auto-Create on Approval as Proposed
**Choice:** When admin approves a "New Application" conversation, automatically create Application record with `category = Proposed`.

**Implications:**
- Reduces manual work for admins
- Creates tight link between conversation and resulting application
- Need observer/listener for ConversationStatus changes
- Need strategy for extracting Application details from conversation
- Admin still controls promotion to Internal (not automatic)

---

## What Works Well (Keep As-Is)

1. **Conversation Model:** Clean separation of concerns, proper relationships
2. **Message Flow:** User vs LLM messages work elegantly with nullable user_id
3. **Document Generation:** PRD generation job architecture is solid
4. **ConversationStatus Enum:** Perfect for tracking approval workflow
5. **Feature Request Flow:** Works end-to-end beautifully
6. **Admin Dashboard:** Great UI for reviewing conversations and documents

---

## What Needs Implementation

### 1. Application Auto-Creation Workflow

**Trigger:** ConversationStatus changes to "Approved" (or "Completed"?) on conversations with `application_id = NULL`

**Action:**
- Create new Application record
- Populate fields (name, description, status, etc.)
- Link Application ↔ Conversation bidirectionally
- Notify admin of new Application creation

**Open Questions:**
- Extract application name from where? (conversation title? first message? PRD document?)
- What should default Application.status be? ("Approved"? "Development"?)
- Should Application.is_automated default to false?
- Should we create the app on "Approved" or wait for "Completed"?

### 2. Settings Page Authorization

**Current:** No authorization - any user can CRUD applications

**Needed:**
- Add admin-only check (middleware or authorize in component)
- Update sidebar to only show Settings link for admin users
- Consider what happens if non-admin navigates directly to /settings

### 3. Application Selection Access Control

**Current:** Application selector in "New Feature" flow

**Verify:**
- Can all users see all applications in the dropdown? (✅ Yes, intended)
- No additional access control needed here

### 4. Conversation Ownership Display

**Enhancement idea:**
- In Settings, for Applications created from conversations, show a link to the source conversation
- Add `Application.conversation_id` to track which conversation spawned this application?
- Or rely on `Conversation.application_id` inverse lookup?

---

## Data Model Implications

### Required Schema Changes

The current schema needs enhancements to support the three-category model:

```php
// Application migration - NEW FIELDS REQUIRED
$table->string('category'); // 'internal', 'external', 'proposed'
$table->foreignId('source_conversation_id')->nullable()->constrained('conversations');

// Existing fields work as-is:
'application_id' // nullable on Conversation - NULL for new app requests
'status'        // enum on Conversation - tracks approval workflow
'signed_off_at' // timestamp on Conversation - when user finished input
```

### Field Usage by Category

**Internal Applications:**
- `category` = 'internal'
- `name`, `short_description`, `overview` - required/recommended
- `url`, `repo` - expected (nullable during development)
- `is_automated` - true if fetching `.llm.md` from repo
- `status` - tracks lifecycle (Development, Active, Deprecated)
- `source_conversation_id` - nullable (manually created vs. promoted from Proposed)

**External Applications:**
- `category` = 'external'
- `name`, `short_description` - required
- `url` - expected (link to external SaaS)
- `repo` - NULL (not applicable)
- `is_automated` - false (can't automate third-party apps)
- `status` - NULL or simple flag (Active/Inactive)
- `source_conversation_id` - NULL (manually created by admin)
- `overview` - optional (for LLM context about what the tool does)

**Proposed Applications:**
- `category` = 'proposed'
- `name`, `short_description` - extracted from conversation/PRD
- `url`, `repo` - NULL (doesn't exist yet)
- `is_automated` - false
- `status` - NULL (not relevant until promoted)
- `source_conversation_id` - required (always from a conversation)
- `overview` - possibly copied from generated PRD

### New Model Methods Required

```php
// Application model
enum ApplicationCategory: string
{
    case Internal = 'internal';
    case External = 'external';
    case Proposed = 'proposed';
}

public function canHaveFeaturesRequested(): bool
{
    return $this->category === ApplicationCategory::Internal;
}

public function isProposal(): bool
{
    return $this->category === ApplicationCategory::Proposed;
}

public function isExternal(): bool
{
    return $this->category === ApplicationCategory::External;
}

public function promoteToInternal(): void
{
    if (!$this->isProposal()) {
        throw new \Exception('Only proposed applications can be promoted');
    }

    $this->category = ApplicationCategory::Internal;
    $this->save();

    // TODO: Notify admins, log event, etc.
}

public function sourceConversation(): BelongsTo
{
    return $this->belongsTo(Conversation::class, 'source_conversation_id');
}
```

---

## Implementation Roadmap

### Phase 1: Data Model & Schema
1. Create migration to add `category` field to applications table
2. Create migration to add `source_conversation_id` to applications table
3. Create `ApplicationCategory` enum (Internal, External, Proposed)
4. Update Application model with category cast and helper methods
5. Update ApplicationFactory with states for each category
6. Update seeders to create sample Internal/External applications
7. Run migrations and verify schema

### Phase 2: Authorization & Access Control
1. Add admin-only authorization to Settings page (Livewire component or policy)
2. Update sidebar navigation to hide Settings from non-admin users
3. Test that regular users can't access /settings directly
4. Verify HomePage shows user's own conversations
5. Add authorization to block "New Feature" for External applications

### Phase 3: Application Auto-Creation (Proposed State)
1. Decide on field extraction strategy (name from conversation/PRD)
2. Create ConversationObserver watching for status changes
3. Implement Application creation logic (category = Proposed)
4. Update Conversation.application_id to link back to Proposed app
5. Add notification to admins about new Proposed application
6. Write comprehensive tests for the Proposed creation lifecycle

### Phase 4: Settings UI Refactor (Three Categories)
1. Refactor SettingsPage to show three sections/tabs
2. Internal Applications section: full CRUD with all fields
3. External Applications section: simplified form (no repo/automation)
4. Proposed Applications section: read-only list with Approve/Reject buttons
5. Implement promote action (Proposed → Internal)
6. Implement reject action (delete or mark rejected)
7. Add "Source Conversation" link for Proposed apps
8. Update tests to cover new UI flows

### Phase 5: LLM Context & Chat Integration
1. Update chat prompt template to include ALL applications (3 categories)
2. Format application list for LLM with category indicators
3. Add instructions for LLM to suggest External alternatives
4. Add instructions for LLM to handle External app feature requests
5. Test that LLM suggestions work as expected
6. Update "New Feature" dropdown to filter: only Internal applications

### Phase 6: Polish & Documentation
1. Update PROJECT_PLAN.md with refined mental model
2. Add UI badges/indicators for application categories
3. Add tooltips/help text explaining three categories
4. Update all tests to cover new authorization and lifecycle
5. Add factory states for all conversation scenarios
6. Write migration guide for existing data (if needed)

---

## Open Questions for Discussion

### 1. Proposed Application Creation Timing ✅ ANSWERED
When should we auto-create the Proposed Application record?

**Decision: On "Approved" ConversationStatus**
- Admin has reviewed and approved the concept
- Creates Proposed application immediately
- Allows admin to review in Settings before promoting to Internal
- Two-step process gives maximum control

### 2. Field Extraction Strategy for Proposed Apps
How do we populate the new Proposed Application's fields?

**Fields to consider:**
- `name` - Required, where to get it?
- `short_description` - Optional, extract from PRD?
- `category` - Set to 'proposed'
- `url` - NULL (doesn't exist yet)
- `repo` - NULL (doesn't exist yet)
- `status` - NULL (not relevant until promoted)
- `is_automated` - false
- `source_conversation_id` - Set to conversation ID
- `overview` - Copy from generated PRD? Leave null?

**Options:**
1. **Minimal approach:** Extract name from first user message or PRD title, leave rest NULL
2. **Smart extraction:** Use LLM to extract name + description from PRD document
3. **Manual completion:** Create with placeholder name, admin fills in details before promoting
4. **Hybrid:** Extract what we can, notify admin to review/complete

**Recommendation:** Start with minimal approach (name only), iterate if needed

### 3. Application Status Field Usage
How should the `status` field work across different categories?

**Current factory uses:** 'plan', 'approved', 'rejected'

**Proposed usage:**
- **Internal Applications:** 'development', 'active', 'deprecated', 'maintenance'
- **External Applications:** NULL or simple 'active'/'inactive'
- **Proposed Applications:** NULL (status not relevant until promoted)

**Question:** Should we create an ApplicationStatus enum? Or keep it flexible string field?

**Recommendation:** Keep as nullable string for now, add enum if patterns emerge

### 4. Notification Strategy
Who should be notified when a new Proposed Application is auto-created?

**Options:**
1. All admin users (similar to Document creation)
2. Just the admin who approved the conversation
3. No notification (just log it)

**Recommendation:** Notify all admins via email (consistent with current pattern)
- Subject: "New Application Proposed: {name}"
- Body: Link to conversation, link to Settings to review proposal

### 5. Rejection Workflow for Proposed Apps
What happens when admin rejects a Proposed application?

**Options:**
1. **Hard delete:** Remove Application record entirely
2. **Soft delete:** Keep record with deleted_at timestamp
3. **Status change:** Add 'rejected' category or status flag
4. **Keep as Proposed:** Just don't promote it, leave it there

**Question:** Do we need audit trail of rejected proposals?

**Recommendation:** Soft delete (Laravel's built-in feature) - preserves history without cluttering UI

### 6. UI Column Name: "category" vs alternatives
Confirming choice to avoid `type` keyword - which alternative?

**Options:**
- `category` ✅ (current choice - clear, no conflicts)
- `kind`
- `app_type`
- `classification`

**Decision:** Use `category` - reads naturally in code and queries

### 7. "New Feature" Dropdown Behavior with External Apps
How should we handle if user tries to select External app?

**Options:**
1. **Don't show them:** Filter to only Internal apps in dropdown
2. **Show but disable:** Display all, disable External with tooltip
3. **Show with warning:** Allow selection, show modal explaining helpdesk process

**Recommendation:** Option 1 - only show Internal apps (clearest UX, prevents confusion)

---

## The Refined Mental Model

### What Is an Application?

**An Application is an entry in the organization's software catalog, with three distinct categories:**

1. **Internal** - Apps you own/manage
   - Managed by admins in Settings
   - Serves as the portfolio for feature requests
   - Can be created manually or promoted from Proposed
   - Represents "we have this" or "we're actively building this"

2. **External** - Third-party SaaS/tools
   - Reference catalog for LLM context
   - Prevents duplicate requests for existing solutions
   - Created manually by admins
   - Represents "this already exists, use it instead of building"

3. **Proposed** - Ideas awaiting approval
   - Auto-created from approved "New Application" conversations
   - Staging area before entering production portfolio
   - Can be promoted to Internal or rejected
   - Represents "we've vetted this idea, now decide if we'll build it"

### What Is a Conversation?

**A Conversation is a requirements-gathering session between a user and the BA assistant.**

- Can be about a new application idea (`application_id = NULL`)
- Can be about a feature for an existing Internal application (`application_id = X`)
- Goes through approval workflow via ConversationStatus
- Generates Documents (PRDs, etc.) upon sign-off
- May spawn a Proposed Application when approved (if new app request)
- LLM has context of ALL applications during chat (for suggestions/alternatives)

### What Are the Relationships?

```
Feature Request Flow (for Internal apps):
──────────────────────────────────────────
Application (category = Internal, exists)
    ← Conversation (links to it via application_id)
    ← LLM has full context (repo overview, etc.)

New Application Flow (becomes Proposed):
────────────────────────────────────────
Conversation (application_id = NULL initially)
    ↓ (user signs off, admin approves ConversationStatus)
Application (auto-created: category = Proposed, source_conversation_id = X)
    ↓ (bidirectional link established)
Conversation (application_id updated to point to Proposed app)
    ↓ (admin reviews in Settings)
Application (promoted: category → Internal)
    ↓ (admin fills in url, repo, sets is_automated)
Application (now available in "New Feature" dropdown)

External Application Flow (manual):
───────────────────────────────────
Admin creates Application (category = External)
    ↓
Included in LLM context for ALL conversations
    ↓
LLM suggests as alternative when relevant
    ↓
Users blocked from requesting features (redirect to helpdesk)
```

---

## Conclusion

The data model is well-positioned for the evolved vision. Through this strategic review, we've identified:

**What's Working:**
- Core conversation and message flow is solid
- Document generation architecture is clean
- Admin review system is in place
- Foundation supports the three-category model

**What Needs Building:**
1. **Schema Enhancement:** Add `category` and `source_conversation_id` fields
2. **Authorization:** Settings page admin-only access control
3. **Lifecycle:** Application auto-creation as Proposed on conversation approval
4. **UI Refactor:** Settings page three-section design (Internal/External/Proposed)
5. **LLM Integration:** Update prompts to include all applications with category awareness
6. **Promotion Workflow:** Proposed → Internal promotion logic in Settings

**Key Architectural Decision:**
Single `Application` model with three categories (Internal, External, Proposed) instead of separate models. This keeps LLM context simple, enables natural lifecycle progression, and maintains clean relationships.

**The Vision:**
Once implemented, the system will elegantly support three workflows:
1. **Feature requests** for existing Internal applications (mostly works now)
2. **New application proposals** that become Proposed, then promoted to Internal (needs implementation)
3. **External application awareness** for LLM to suggest existing solutions (needs implementation)

The architecture remains simple, readable, and scalable. This is an evolution, not a revolution.

---

## Next Steps

### Immediate Decisions Needed
1. ✅ Use `category` field (not `type`)
2. ✅ Three categories: Internal, External, Proposed
3. ✅ Auto-create on "Approved" status as Proposed
4. ❓ Field extraction strategy (minimal vs smart)
5. ❓ Rejection workflow (soft delete vs hard delete)
6. ❓ Status field usage (nullable string vs enum)

### Implementation Order
1. **Phase 1:** Schema changes and model updates
2. **Phase 2:** Authorization and access control
3. **Phase 3:** Proposed application auto-creation
4. **Phase 4:** Settings UI refactor for three categories
5. **Phase 5:** LLM context integration
6. **Phase 6:** Polish and documentation

### Success Criteria
- Admin can manage three types of applications in Settings
- Users get LLM suggestions for external alternatives
- New app conversations auto-create Proposed applications
- Admin can promote Proposed → Internal with one click
- "New Feature" dropdown only shows Internal apps
- All workflows have comprehensive test coverage

The foundation is solid. The vision is clear. Time to build! 🚀
