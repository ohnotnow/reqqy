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
- [ ] Build initial selection interface
  - [ ] Dashboard showing "New Feature" vs "New Application" choice
  - [ ] Application picker for feature requests
- [ ] Implement conversation flow
  - [ ] Livewire component for chat interface
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
