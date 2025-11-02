# Reqqy README & Codebase Findings

## Features Delivered As Documented
- `README.md:15` promises conversational requirements capture with an AI analyst, and `app/Livewire/ConversationPage.php:15` instantiates the session, streams messages, and hands off to Prism via `app/Services/LlmService.php:19`. The Prism adapter covers provider/model parsing and prompt rendering, while `tests/Feature/Livewire/ConversationPageTest.php:224` fakes Prism to prove real conversation history is sent to the model.
- The README’s sign-off flow (README.md:35-37) is live: `app/Livewire/ConversationPage.php:118` timestamps completion and queues research plus PRD generation jobs based on whether an application is attached. Queue assertions in `tests/Feature/Livewire/ConversationPageTest.php:315` confirm the correct jobs are dispatched for both new-app and feature-request paths.
- Admin review tooling described in `README.md:41-49` exists. `app/Livewire/ConversationsAdminPage.php:12` enforces `viewAny`, and `resources/views/livewire/conversations-admin-page.blade.php:4` surfaces status badges, counts, and navigation. Drilldowns happen through `app/Livewire/ConversationDetailPage.php:28`, which authorizes, validates status changes, and exposes downloads; `tests/Feature/Livewire/ConversationDetailPageTest.php:113` exercises the status picker end to end.
- The three-tier catalog (README.md:18-24) is operational: `app/Livewire/ApplicationsPage.php:14` switches tabs, `resources/views/livewire/applications-page.blade.php:10` renders Internal/External/Proposed groupings, and promotion routes through `Application::promoteToInternal()` at `app/Models/Application.php:35`. Coverage from `tests/Feature/Livewire/ApplicationsPageTest.php:39` ensures listings, CRUD, and promotion validation all reflect correctly.
- Smart context via `.llm.md` ingestion (README.md:147-158) is wired: `app/Observers/ApplicationObserver.php:13` fires `GetApplicationInfoJob`, which shells out to `app/Console/Commands/GetApplicationInfo.php:25`. The command reads local repos or returns TODO stubs for GitHub, and `tests/Feature/GetApplicationInfoCommandTest.php:8` verifies happy paths, failure paths, and edge cases.
- Automatic proposal creation and admin alerts (README.md:44-47) are handled in `app/Observers/ConversationObserver.php:24`, which creates Proposed applications, patches the conversation, and notifies admins through `App\Notifications\NewProposedApplicationCreated`. `tests/Feature/ConversationObserverTest.php:16` proves LLM-based naming, notification fan-out, and the GitHub job trigger logic.

## Partially Implemented / Stubbed Items
- PRD generation currently writes placeholder markdown (`app/Jobs/GenerateNewApplicationPrdJob.php:34`, `app/Jobs/GenerateFeatureRequestPrdJob.php:36`) instead of the LLM-authored documents promised in `README.md:15-19`. The TODO comments and tests (`tests/Feature/GenerateNewApplicationPrdJobTest.php:44`) explicitly expect stub text until end-to-end flows are verified.
- Research automation (README.md:57-64) is represented by `app/Jobs/ResearchAlternativesJob.php:23`, which creates a “Research pending” document and stops there. No agent invocation or repo inspection exists yet.
- GitHub issue creation is logged rather than executed (`app/Jobs/CreateGitHubIssueJob.php:29`), so the README’s “promote proposals to production portfolio” story lacks the final project-management integration.
- Conversation statuses include `Rejected` in `App\ConversationStatus` (app/ConversationStatus.php:9), yet the README’s lifecycle omits it; UI and tests handle it (`resources/views/livewire/conversations-admin-page.blade.php:20`, `tests/Feature/ConversationDetailPageTest.php:96`), but documentation could acknowledge the extra state or the implementation should disable it if unused.

## Access & Security
- Routes wrap everything in auth with an admin enclave (`routes/web.php:7` and `routes/web.php:11`), enforced by `app/Http/Middleware/Admin.php:17`. `tests/Feature/AdminMiddlewareTest.php:4` confirms admins succeed while guests and non-admins hit 403/302.
- Conversations are ACL’d so users see only their threads while admins can view/update all (`app/Policies/ConversationPolicy.php:14`, `app/Policies/ConversationPolicy.php:38`). Unauthorized access is tested in `tests/Feature/Livewire/ConversationPageTest.php:138`.
- Observer registrations live in `app/Providers/AppServiceProvider.php:24`, guaranteeing automation stays active without manual bootstrapping.

## UX & Livewire Notes
- Home onboarding matches the README’s journey (README.md:32-37): `app/Livewire/HomePage.php:8` fetches internal apps, and the Flux UI modal at `resources/views/livewire/home-page.blade.php:22` offers “New Feature” vs. “New Application” with recent conversation shortcuts. `tests/Feature/Livewire/ConversationPageTest.php:96` ensures new sessions are created for both entry points.
- Admin detail screens provide conversation excerpts, document previews, and markdown downloads (`resources/views/livewire/conversation-detail-page.blade.php:70`). Pending message UX on the chat page is covered by `app/Livewire/ConversationPage.php:206`, ensuring a spinner appears while awaiting an LLM response.
- Application promotion captures required metadata but relies on manual status entry (`app/Livewire/ApplicationsPage.php:92`). Enforcement that only Approved conversations create proposals happens in the observer, not the UI, so admins can promote any proposal once it exists.

## Automation & Notifications
- Every new document (PRD or research) triggers `NewDocumentCreated` emails through `app/Observers/DocumentObserver.php:11`, aligning with the README’s admin alerting. Tests like `tests/Feature/GenerateFeatureRequestPrdJobTest.php:60` and `tests/Feature/ResearchAlternativesJobTest.php:34` demonstrate only admins are notified.
- Proposed application notifications route admins to the conversation detail (`app/Notifications/NewProposedApplicationCreated.php:29`), matching the review loop in the README. 
- Application automation toggles kick off repo syncing (`ApplicationObserver.php:13`) and respect toggling logic when flipping `is_automated`. `tests/Feature/ApplicationObserverTest.php:7` confirms the job dispatch rules.

## Testing Coverage Snapshot
- Conversation flows: Livewire tests cover message validation, LLM handing, sign-off restrictions, and job dispatchers (`tests/Feature/Livewire/ConversationPageTest.php:166`).
- Observers: Proposed-app creation, fallback naming, admin notifications, and GitHub-job gating are exhaustively tested (`tests/Feature/ConversationObserverTest.php:97`).
- LLM adapter: Provider parsing, prompt selection, and error handling (`tests/Feature/LlmServiceTest.php:74`) ensure configuration mistakes surface fast.
- CLI automation: The `.llm.md` importer is backed by unit-style tests with filesystem fakes (`tests/Feature/GetApplicationInfoCommandTest.php:8`).
- Admin UI: Tabs, empty states, modal-driven CRUD, and promotion flows are validated in `tests/Feature/Livewire/ApplicationsPageTest.php:45`.

## Reading Checklist for the Junior
- [ ] `app/Livewire/ConversationPage.php` (chat flow, sign-off, job dispatching)
- [ ] `resources/views/livewire/conversation-page.blade.php` (user-facing conversation UI)
- [ ] `app/Services/LlmService.php` (Prism integration, prompts, provider parsing)
- [ ] `app/Observers/ConversationObserver.php` & `app/Observers/DocumentObserver.php` (automation and notifications)
- [ ] `app/Jobs/GenerateFeatureRequestPrdJob.php` & `app/Jobs/GenerateNewApplicationPrdJob.php` (current PRD stubs)
- [ ] `app/Livewire/ConversationsAdminPage.php` & `resources/views/livewire/conversations-admin-page.blade.php` (admin summaries)
- [ ] `app/Livewire/ConversationDetailPage.php` & view (status updates, document handling)
- [ ] `app/Livewire/ApplicationsPage.php` & view (three-tier application catalog and promotion flow)
- [ ] `tests/Feature/Livewire/ConversationPageTest.php` et al. (how we verify behaviour with Prism fakes)

## Risks & Next Actions
- Replace stub documents with real LLM output once QA is satisfied, and expand tests to assert on structured sections instead of placeholder text.
- Flesh out `ResearchAlternativesJob` and `CreateGitHubIssueJob` to meet the README’s promise of actionable research and repository integration.
- Decide whether the `Rejected` status stays; if so, document the full lifecycle in `README.md` and ensure UI copy reflects it consistently.
- Monitor payload size on `ConversationsAdminPage`—`Conversation::with([...])->get()` at `app/Livewire/ConversationsAdminPage.php:15` loads full histories, which may need pagination for busy deployments.

### Priority Work Items (in order)
1. [ ] Swap the stub content in `GenerateNewApplicationPrdJob` and `GenerateFeatureRequestPrdJob` for real Prism-powered prompts while retaining an easy path to reintroduce temporary stubs when future scaffolding is still under construction.
2. [ ] Implement the planned research agent inside `ResearchAlternativesJob`, preserving a toggleable stub so we can iterate safely.
3. [ ] Finish `CreateGitHubIssueJob` with authenticated GitHub calls (or a service abstraction) to mirror the README workflow.
4. [ ] Update the README to reflect the full status lifecycle (including the implemented `Rejected` state) or adjust the code if that status is truly out of scope.
