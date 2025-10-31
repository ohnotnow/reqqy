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
- [ ] Implement conversation flow
  - [ ] ConversationPage Livewire component for chat interface
  - [ ] Integration with Prism for LLM calls
  - [ ] Message persistence and display
  - [ ] "Sign Off" button to complete conversation
- [ ] PRD generation
  - [ ] Create PRD template/format
  - [ ] Background job to process conversation → PRD
  - [ ] Store generated PRD as Document
- [ ] Admin notification and document access
  - [ ] Email notification to admin users
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

## Next Steps - PRD Generation

### Approach
When a user signs off on a conversation, we need to dispatch a queued job to generate a PRD document. The job should:

1. **Determine request type**: Check if `conversation.application_id` is null
   - If null → New Application request → Dispatch `GenerateNewApplicationPrdJob`
   - If not null → Feature Request → Dispatch `GenerateFeatureRequestPrdJob`

2. **Both jobs should**:
   - Read all messages from the conversation
   - Use Prism to generate a structured PRD (initially with fake/mock content)
   - Create a new `Document` record linked to the conversation
   - Store the PRD content in the document

3. **Future enhancements** (Phase Two):
   - `GenerateFeatureRequestPrdJob`: Spawn background agent to investigate codebase
   - `GenerateNewApplicationPrdJob`: Spawn background agent to research existing solutions
   - Email user when PRD is complete (add new Reqqy message to conversation with results)
   - Email admins to notify them of new PRD ready for review

### Implementation Tasks
- [ ] Create `GenerateFeatureRequestPrdJob` queued job
- [ ] Create `GenerateNewApplicationPrdJob` queued job
- [ ] Update `signOff()` method to dispatch appropriate job based on conversation type
- [ ] Create PRD template/format (can be simple markdown initially)
- [ ] Update Document model with proper relationships and fields
- [ ] Add basic PRD generation logic (can use fake content initially)
- [ ] Test job execution and document creation
