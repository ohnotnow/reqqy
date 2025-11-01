# Reqqy

The aim of this application is to allow users to request new features in existing Laravel applications, or entirely new Laravel applications.

The idea is to let the user talk through the idea with an LLM, capture the requirements, and the further process to end up with well written PRD's and additionally do some research and investigation.

## Core Tech Stack

- Laravel v12
- Livewire v3
- FluxUI (front-end component system)
- Prism (for LLM calls - https://prismphp.com/core-concepts/text-generation.html)
- Pest (for tests)
- TailwindCSS

## User Journey

- User logs in and gets a choice between a feature request or a new application
- If a feature request, they get to pick from the list of existing \App\Models\Application's
- Now we create a new \App\Models\Conversation
- Then we enter a conversation flow - the user first, which goes to the LLM, back and forth until the LLM and User are satisfied the initial 'ask' has been captured.
- The user has a 'Sign off' button they can click at any point to indicate they are happy with the requirements capture conversation and it is over.
- The user can now close the window.

## The Next Step

- The LLM takes the conversation and writes a PRD in a format which will be supplied
- That output is stored as an \App\Models\Document and associated with the conversation.
- Admin users (`is_admin` on the User model) are then emailed to let them know there is something to look at in the system.

## Phase Two

- When asked for a new feature in an existing application - a background agent is started and given access to the cloned repo for the codebase.  There will be some standard documentation for it to read and it will be free to investigate the codebase.  The agent will be tasked with figuring out where the new feature would fit, if the feature already exists (maybe the user just doesn't realise as it's buried away in a menu somewhere).
- That information is passed back to the main PRD writing LLM to inform it with some direct information that will help it figure out the scope and size of the request.
- When asked for a new application - a background agent will start up.  It will be asked to use it's web and research tools to investigate if there is a pre-existing solution that would fit the users requirements.
- The findings of that research are stored as an associated \App\Models\Document and also given to the main LLM to inform it's PRD.

## Next Steps

This is an MVP focused on the core user journey: conversational requirements gathering, LLM-generated PRD creation, and admin notification. The system acts as a capture and generation tool - admins can copy/paste or download documents to take forward in their own workflows. No document versioning or complex revision tracking at this stage.

Remember: You have the laravel boost MCP tool which was written by the creators of Laravel and has excellent documentation for the core tech stack along with other helpful features.

### MVP Development Tasks

- [X] Set up authentication and user management
  - [X] Implement login/registration
  - [X] Add `is_admin` boolean to User model
- [X] Create core models, migrations, factories and local development seeder
  - [X] Application model (for existing Laravel apps)
  - [X] Conversation model
  - [X] Message model (for conversation messages)
  - [X] Document model (for generated PRDs)
  - [X] Seeder created (TestDataSeeder)
- [X] Build initial selection interface
  - [X] Dashboard showing "New Feature" vs "New Application" choice (HomePage component)
  - [X] Application picker for feature requests (Flux flyout modal with searchable select)
  - [X] Unified routing to ConversationPage with optional application_id parameter
- [X] Implement conversation flow
  - [X] ConversationPage Livewire component for chat interface
  - [X] Integration with Prism for LLM calls (via LlmService wrapper)
  - [X] Message persistence and display
  - [X] "Sign Off" button to complete conversation
  - [X] LlmService wrapper class for provider flexibility
- [ ] PRD generation
  - [X] Create PRD template/format (Blade template approach)
  - [X] Background job to process conversation → PRD (GenerateNewApplicationPrdJob)
  - [X] Store generated PRD as Document
  - [ ] Create GenerateFeatureRequestPrdJob
  - [ ] Hook up job dispatch in signOff() method
- [ ] Admin notification and document access
  - [X] DocumentObserver to watch for new Documents
  - [X] Notification class for admin users
  - [X] Make User model Notifiable
  - [ ] Admin view to list conversations/documents
  - [ ] Copy/download functionality for documents
- [ ] Testing and polish
  - [ ] Write Pest tests for core flows
  - [ ] UI/UX refinement with FluxUI
  - [ ] Error handling and edge cases

## Work Done (Session Notes)

### 2025-10-31 - Initial Dashboard Implementation
- ✅ Created HomePage component with two-card layout for "New Feature" and "New Application" choices
- ✅ Implemented Flux flyout modal for application selection (new feature flow)
- ✅ Added searchable Flux select component with Application model integration
- ✅ Created ConversationPage component (placeholder for chat interface)
- ✅ Set up unified routing: both flows redirect to `/conversation` with optional `?application_id=X` query param
- ✅ Cleaned up: removed intermediate NewFeaturePage/NewApplicationPage components in favor of modal approach
- 🎨 UX: Direct click for "New Application", modal popup for "New Feature" to select application first

### 2025-10-31 - Conversation Initialization Logic
- ✅ Implemented ConversationPage mount logic with `#[Url]` attributes for `conversation_id` and `application_id`
- ✅ Added conversations relationship to User model (with proper `HasMany` return type)
- ✅ Conversation creation on first visit, reload-safe with existing conversation lookup
- ✅ Security: Automatic authorization check via `auth()->user()->conversations()->findOrFail()`
- 📝 Ready for next steps: chat UI, message display, and Prism LLM integration

### 2025-10-31 - Chat Interface Implementation
- ✅ Updated Message model with fillable fields, relationships, and helper methods (`isFromUser()`, `isFromLlm()`)
- ✅ Updated Conversation model with messages relationship, `signed_off_at` field, and `isSignedOff()` helper
- ✅ Built full-height chat UI with header, scrollable message area, and fixed input form
- ✅ Implemented message display using Flux callout components:
  - User messages: blue callouts with user-circle icon, right-aligned, 2/3 width
  - Reqqy messages: purple callouts with sparkles icon, left-aligned, 2/3 width
- ✅ Added sendMessage() method with validation and fake LLM response ("Claude is the best")
- ✅ Added signOff() method to mark conversation complete with timestamp
- ✅ Fixed URL persistence: redirect after conversation creation to include `conversation_id` in query params
- ✅ Eager loading messages on mount with `->with('messages')` for performance
- ✅ Removed `.live` modifier from textarea to prevent unnecessary network requests
- 🎨 UX: Clean chat interface with proper message persistence on refresh, sign-off button, and disabled state when signed off
- 📝 Next: Replace fake LLM with real Prism integration

### 2025-10-31 - Sign-Off Flow and Conversation Sharing
- ✅ Added `signed_off_at` column to conversations migration
- ✅ Implemented sign-off message flow:
  - 1-second delay to simulate LLM response (for consistent UX)
  - Friendly thank-you message from Reqqy
  - Message stored in database for conversation history
- ✅ Added shareable conversation link feature:
  - Copyable Flux input with readonly + copyable attributes
  - Displayed in separate callout after sign-off
  - Uses link icon and purple color to match Reqqy branding
  - Sets up foundation for future notifications/updates to the conversation
- ✅ Added 1-second delay to all fake LLM responses for consistency
- 🎨 UX: Professional sign-off experience with clear next steps and easy conversation bookmarking
- 📝 Next: Implement PRD generation via queued jobs

### 2025-10-31 - PRD Generation for New Applications
- ✅ Created Blade-based prompt template system (`resources/views/prompts/new-application-prd.blade.php`)
  - Uses Laravel's templating engine to render LLM prompts
  - Loops through conversation messages with proper formatting
  - Defines comprehensive PRD structure with 9 sections (Executive Summary, Goals, User Personas, Functional Requirements, Non-Functional Requirements, User Stories, Technical Considerations, Out of Scope, Open Questions)
- ✅ Implemented `GenerateNewApplicationPrdJob` queued job
  - Accepts `Conversation` model in constructor
  - Fetches messages chronologically
  - Renders Blade prompt template with conversation context
  - Calls Prism (Claude 3.5 Sonnet) with 4096 max tokens
  - Creates `Document` record with generated PRD content
- ✅ Updated Document model:
  - Added fillable fields (`conversation_id`, `name`, `content`)
  - Added `conversation()` relationship
- ✅ Updated Conversation model:
  - Added `documents()` relationship
- ✅ Comprehensive Pest tests (`tests/Feature/GenerateNewApplicationPrdJobTest.php`):
  - Tests PRD generation from conversation messages
  - Tests chronological message ordering
  - Tests handling of empty conversations
  - Uses `Prism\Prism\Testing\TextResponseFake` for mocking LLM responses
  - All 3 tests passing with 8 assertions
- ✅ Enabled `RefreshDatabase` trait in Pest configuration
- ✅ All code formatted with Laravel Pint
- 📝 Next: Admin notifications when Documents are created

### 2025-10-31 - Admin Notifications (TDD Implementation)
- ✅ **TDD Approach: RED → GREEN → REFACTOR**
- ✅ Updated tests FIRST with notification assertions (RED phase)
  - Added `Notification::fake()` to existing tests
  - Created dedicated test for multiple admin users
  - Created test for notification content/conversation link
  - Verified 3 tests failed as expected
- ✅ Implemented `NewDocumentCreated` notification class
  - Accepts `Document` model in constructor (uses constructor property promotion)
  - Implements `ShouldQueue` for background processing
  - Dynamic subject line based on request type (Feature Request vs New Application)
  - Generic greeting: "Hello Reqqy Admin!" (avoids complexity with `forenames`/`surname` fields)
  - Action button with route to conversation page
  - Stores document/conversation IDs for reference
- ✅ Created `DocumentObserver` class
  - Watches for `created` events on Document model
  - Queries all admin users (`where('is_admin', true)`)
  - Sends notification to each admin individually
  - Clean, focused implementation following Single Responsibility Principle
- ✅ Registered observer in `AppServiceProvider::boot()`
- ✅ All 5 tests passing with 15 assertions (GREEN phase)
- ✅ Code formatted with Laravel Pint (REFACTOR phase)
- 💡 **Key Learning**: Always check migrations/models for actual field names before using them in code
  - User table has `username`, `email`, `forenames`, `surname` - NOT `name`
  - Generic greetings avoid complexity and potential errors
- 📝 Next: Hook up job dispatch in ConversationPage `signOff()` method, then create `GenerateFeatureRequestPrdJob`

### 2025-11-01 - Settings Page for Application Management
- ✅ Created `SettingsPage` Livewire component with full CRUD functionality
  - Component class: `app/Livewire/SettingsPage.php`
  - Blade view: `resources/views/livewire/settings-page.blade.php`
  - Route: `/settings` (wired up to existing sidebar Settings link)
- ✅ Updated `Application` model with fillable fields and casts
  - Added all database columns to `$fillable` array
  - Added cast for `is_automated` boolean field
- ✅ Implemented full CRUD operations:
  - **Create**: Add new applications via flyout modal form
  - **Read**: List all applications with their details (name, short_description, status, url, repo)
  - **Update**: Edit existing applications (unique modal per application)
  - **Delete**: Delete applications with wire:confirm confirmation dialog
- ✅ UI/UX Features:
  - Flyout modals for create and edit forms (consistent with HomePage pattern)
  - URLs and repos displayed as clickable `flux:link` components with `target="_blank"`
  - "Automated" badge shown for applications with `is_automated = true`
  - Empty state with helpful message when no applications exist
  - Form validation with required fields (name, status) and optional fields
  - Form resets after create/update operations
  - Fixed form state issue: clicking "Add Application" now resets form fields
- ✅ Comprehensive test coverage (18 tests, 78 assertions)
  - Test file: `tests/Feature/Livewire/SettingsPageTest.php`
  - Following team conventions: using Eloquent models instead of database assertions
  - Using `$model->fresh()` to verify changes, `Model::find()` to check existence
  - Tests cover: rendering, display, create, update, delete, validation, error handling, field variations
  - All tests passing
- ✅ All code formatted with Laravel Pint
- 📝 Next: Continue with remaining MVP tasks (hook up job dispatch, create GenerateFeatureRequestPrdJob)

### 2025-11-01 - Automated Application Overview System
- ✅ Implemented automated `.llm.md` file fetching for application overviews
- ✅ Updated `GetApplicationInfo` Artisan command (`app/Console/Commands/GetApplicationInfo.php`)
  - Added `--app-id=<int>` option to process a specific application
  - Added `--all-apps` flag to process all automated applications
  - Error handling: sets overview to `.llm.md does not exist` when file is missing
  - Handles both `file://` local paths and remote repos (GitHub stub for Phase Two)
  - Strips `file://` prefix and trailing slashes from local paths
- ✅ Updated `GetApplicationInfoJob` to use new command signature
  - Job dispatches command with `--app-id` option
  - Implements `ShouldQueue` for background processing
- ✅ Created `ApplicationObserver` (`app/Observers/ApplicationObserver.php`)
  - Dispatches `GetApplicationInfoJob` when automated application is created
  - Dispatches job when application is updated to `is_automated = true`
  - Does not dispatch when `is_automated` unchanged or changed to false
  - Registered in `AppServiceProvider::boot()`
- ✅ Added scheduled command (`routes/console.php`)
  - Daily schedule: `Schedule::command('reqqy:get-application-info --all-apps')->daily()`
  - Keeps all automated applications' overviews synchronized
- ✅ Comprehensive test coverage (13 tests, 23 assertions)
  - **GetApplicationInfoCommandTest** (8 tests, 17 assertions)
    - Tests for both `--app-id` and `--all-apps` options
    - Tests error handling for missing `.llm.md` files
    - Tests file:// path handling and cleanup
    - Tests edge cases (non-existent app, no automated apps, GitHub repos)
  - **ApplicationObserverTest** (5 tests, 6 assertions)
    - Tests job dispatch on create and update
    - Tests no dispatch for non-automated apps
    - Tests no dispatch when `is_automated` unchanged
- ✅ Fixed `SettingsPageTest` failures caused by observer
  - Added `Queue::fake()` to tests that create automated applications
  - Prevents observer from overwriting test data
  - All 37 tests passing with 117 assertions
- ✅ All code formatted with Laravel Pint
- ✅ Manual testing successful
  - Command successfully reads `.llm.md` from project root (4549 characters)
  - Observer correctly dispatches jobs when automated applications are created
- 💡 **System Flow:**
  1. User creates/updates application with `is_automated = true` → Observer dispatches job
  2. Job calls Artisan command to fetch `.llm.md` content
  3. Content populates `overview` field on Application model
  4. Daily scheduled task keeps all automated applications synchronized
- 📝 Next: Hook up job dispatch in ConversationPage `signOff()` method, create `GenerateFeatureRequestPrdJob`

### 2025-11-01 - Real LLM Integration with LlmService
- ✅ Created `LlmService` wrapper class (`app/Services/LlmService.php`)
  - Abstracts Prism integration for future flexibility (preparing for Taylor's official Laravel AI SDK)
  - Litellm-style configuration format: `provider/model` (e.g., `anthropic/claude-3-5-sonnet-20241022`)
  - Configured via `config/reqqy.php` and `REQQY_LLM` environment variable
  - Supports three providers: Anthropic, OpenAI, and OpenRouter
  - Case-insensitive provider names for convenience
- ✅ Comprehensive validation and error handling:
  - Validates config is not empty/null
  - Validates format includes `/` separator
  - Throws helpful exceptions for unsupported providers with clear guidance
  - Error messages direct users to set `REQQY_LLM` in `.env` file
- ✅ Core functionality:
  - `generateResponse()` method accepts collection of Message models
  - Converts messages to Prism format (UserMessage/AssistantMessage)
  - Passes full conversation history for context-aware responses
  - Returns plain text response from LLM
- ✅ Updated `ConversationPage` component:
  - Replaced fake "Claude is the best" responses with real LLM integration
  - Uses LlmService via dependency injection
  - Fetches conversation history ordered chronologically
  - Removed artificial 1-second delay (real LLM responses now)
- ✅ Comprehensive test coverage (17 new tests):
  - **LlmServiceTest** (12 tests, 19 assertions)
    - Tests all three supported providers
    - Tests empty/null config validation
    - Tests missing slash separator
    - Tests unsupported provider detection
    - Tests case-insensitive provider names
    - Tests conversation message handling and ordering
  - **ConversationPageTest** (13 tests, 42 assertions)
    - Full end-to-end testing with Prism::fake()
    - Tests rendering, conversation creation, message flow
    - Tests LLM response integration with conversation history
    - Tests validation, sign-off flow, authorization
  - All 62 tests passing with 178 assertions
- ✅ All code formatted with Laravel Pint
- ✅ Manual browser testing successful - working perfectly first time!
- 💡 **Design Benefits:**
  - Easy to swap LLM providers by changing one env variable
  - Clean abstraction layer makes future SDK migration simple
  - Follows team conventions: simple, readable, well-tested
  - Litellm-style format familiar to developers from Python ecosystem
- 📝 Next: Hook up job dispatch in ConversationPage `signOff()` method, create `GenerateFeatureRequestPrdJob`

### 2025-11-01 - Context-Aware Chat Prompts & Flexible LlmService
- ✅ Created context-aware Business Analyst prompt system (`resources/views/prompts/chat.blade.php`)
  - Preserved excellent BA persona from previous iteration (consultative, conversational tone)
  - Dynamic context sections based on conversation type:
    - **New Application**: Guides user to articulate core problem, target users, essential functionality
    - **Feature Request**: Provides application name, short description, and full `.llm.md` overview
  - Conditional rendering of application details (short_description and overview only if available)
  - LLM receives full context about existing applications to ask informed integration questions
- ✅ Enhanced `LlmService` for maximum reusability:
  - **New flexible signature**: `generateResponse(Conversation $conversation, Collection $messages, ?string $systemPrompt = null)`
  - Optional `$systemPrompt` parameter: pass custom prompt string or null for default chat prompt
  - Renamed `renderSystemPrompt()` → `renderChatPrompt()` for clarity (specific to chat flow)
  - Uses null coalescing operator for elegant default handling
  - Service now reusable across entire application (chat, PRD generation, future use cases)
- ✅ Updated method implementation:
  - `renderChatPrompt()` renders Blade template with conversation + application context
  - Eager loads application relationship to ensure context is available
  - System prompt passed via `->withSystemPrompt()` to Prism
- ✅ Comprehensive test coverage (5 new tests):
  - Tests default chat prompt is used when no custom prompt provided
  - Tests custom prompt acceptance and usage
  - Tests chat prompt rendering for new application requests
  - Tests chat prompt rendering with full application context (name, description, overview)
  - Tests chat prompt rendering with minimal application context (name only)
  - All 67 tests passing with 192 assertions
- ✅ All code formatted with Laravel Pint
- 💡 **Design Benefits:**
  - Chat flow gets context-aware prompts automatically (no code changes needed in ConversationPage)
  - PRD generation can pass custom prompts: `$service->generateResponse($conversation, $messages, $prdPrompt)`
  - Any future LLM interaction can leverage the same service with custom prompts
  - Clean separation of concerns: service handles Prism complexity, callers control prompts
  - Business Analyst prompt ensures high-quality requirements gathering
- 📝 Next: Hook up job dispatch in ConversationPage `signOff()` method, create `GenerateFeatureRequestPrdJob`

### 2025-11-01 - Admin Conversation & Document Management System
- ✅ Created comprehensive admin system for viewing and managing conversations and documents
- ✅ **Database & Model Updates:**
  - Added `status` field to conversations table (default: 'pending')
  - Created `ConversationStatus` enum with 5 states: Pending, InReview, Approved, Rejected, Completed
  - Updated `Conversation` model with status enum casting and fillable fields
  - Created `ConversationPolicy` for admin-only access control
- ✅ **Admin List Page** (`ConversationsAdminPage` - `/admin/conversations`):
  - Lists all conversations ordered by most recent first
  - Shows conversation type (Feature Request vs New Application)
  - Displays color-coded status badges (yellow/blue/green/red/zinc)
  - Shows message and document counts
  - Clickable cards linking to detail view
  - Empty state for no conversations
  - Proper eager loading to avoid N+1 queries
- ✅ **Admin Detail Page** (`ConversationDetailPage` - `/admin/conversations/{id}`):
  - **Summary section**: User details, creation date, type, signed-off status, counts
  - **Status management**: Pill-style radio group for updating conversation status
  - **Conversation history**: Collapsible view showing first 3 messages with "Show full conversation" toggle
  - **Documents section**:
    - Responsive card grid (1/2/3 columns based on screen size)
    - Each card shows document name, creation date, character count badge
    - Click card to open modal with full document content
    - Modal includes download button (icon-only, positioned left to avoid conflict with close button)
    - Download generates `.md` file with proper content-type header
    - Modals rendered outside grid to prevent layout issues
- ✅ **Navigation & Routes:**
  - Added "Conversations" link to sidebar (admin users only)
  - Created admin route group with proper naming (`admin.conversations.index`, `admin.conversations.show`)
- ✅ **Testing:**
  - 24 comprehensive Pest tests (8 for list page, 16 for detail page)
  - Tests cover: rendering, authorization, display, sorting, eager loading, status updates, validation
  - All 91 tests passing with 261 assertions
- ✅ **UI/UX Features:**
  - Used Flux UI components throughout for consistency
  - Hover effects on clickable elements
  - Proper empty states
  - Tooltips on icon buttons
  - Responsive design (mobile to desktop)
  - Dark mode support
- ✅ All code formatted with Laravel Pint
- 💡 **Key Achievements:**
  - Clean separation of concerns with policies
  - Efficient data loading with eager relationships
  - Professional, polished UI matching app design system
  - Scalable architecture (handles 1-100+ conversations easily)
  - Secure download functionality with authorization checks
- 📝 Next: Hook up job dispatch in ConversationPage `signOff()` method, create `GenerateFeatureRequestPrdJob`

## Next Steps - Admin Notifications

### Approach
When a new `Document` is created, we need to notify all admin users in the system. Using an Observer pattern will keep this logic clean and maintainable as the app grows.

### Implementation Tasks

#### 1. DocumentObserver Setup
- [ ] Create `DocumentObserver` class (`php artisan make:observer DocumentObserver --model=Document`)
- [ ] Register observer in `AppServiceProvider` or `EventServiceProvider`
- [ ] Implement `created()` method to handle new Document events

#### 2. Notification System
- [ ] Make `User` model `Notifiable` (add `use Notifiable` trait)
- [ ] Create `NewDocumentCreated` notification class (`php artisan make:notification NewDocumentCreated`)
- [ ] Notification should accept `Document` model in constructor
- [ ] Implement `toMail()` method with:
  - Descriptive but concise message (e.g., "A new PRD has been generated for [New Application/Feature Request]")
  - Link to conversation page where admin can view the conversation and documents
  - Use `route()` helper to generate proper URL
- [ ] Implement `via()` method to return `['mail']` (extensible for Slack/Teams later)
- [ ] Consider adding `toSlack()` or `toMicrosoftTeams()` methods as placeholders for future

#### 3. Observer Logic
- [ ] In `DocumentObserver::created()`, query all admin users: `User::where('is_admin', true)->get()`
- [ ] Loop through admin users and send notification: `$admin->notify(new NewDocumentCreated($document))`
- [ ] Consider using `Notification::send()` for bulk notifications if needed

#### 4. Testing
- [ ] Update `GenerateNewApplicationPrdJobTest` to use `Notification::fake()`
- [ ] Assert that notification is sent to admin users when Document is created
- [ ] Assert that notification is NOT sent to non-admin users
- [ ] Test notification contains correct conversation link
- [ ] Test notification message includes correct context (New Application vs Feature Request)
- [ ] Verify notification is sent for each admin user in the system

#### 5. Additional Considerations
- [ ] Add mail view for notification (resources/views/emails/new-document-created.blade.php) if using Mailable
- [ ] Ensure notification works with queue system (implement `ShouldQueue` if desired)
- [ ] Add config option to enable/disable admin notifications
- [ ] Consider rate limiting or batching if many Documents are created simultaneously

### Future Enhancements (Phase Two)
- Add Slack integration for notifications
- Add Microsoft Teams integration for notifications
- Add in-app notification system (database notifications)
- Allow admins to configure their notification preferences
- Send notification back to the user when PRD is complete (add Reqqy message to conversation)
