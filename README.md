# Reqqy

**AI-Powered Requirements Gathering for Applications**

Reqqy is a web-based Laravel application that streamlines the process of capturing feature requests and new application ideas through conversational AI. Users chat with an intelligent Business Analyst assistant powered by LLMs, and the system automatically generates professional Product Requirement Documents (PRDs) for review and implementation.

---

## What Does Reqqy Do?

Reqqy solves a common problem: gathering clear, comprehensive requirements from stakeholders who may not think in technical terms. Instead of lengthy forms or ambiguous emails, users have a natural conversation with an AI assistant that asks the right questions, explores edge cases, and produces structured documentation ready for development teams.

### Key Features

- **Conversational Requirements Gathering**: Natural chat interface powered by LLMs (Claude, GPT-4, or OpenRouter)
- **Automatic PRD Generation**: AI-generated Product Requirement Documents from conversation history
- **Three-Tier Application Catalog**:
  - **Internal Apps**: Your portfolio of owned/managed applications
  - **External Apps**: Third-party SaaS references (prevents duplicate requests)
  - **Proposed Apps**: Auto-created from approved conversations, ready for promotion
- **Smart Context Awareness**: LLM has full visibility into your application catalog to suggest existing solutions
- **Admin Workflow**: Review conversations, manage status, promote proposals to production portfolio
- **Automated Application Overviews**: Fetch `.llm.md` files from repos for rich context in feature discussions
- **Multi-LLM Support**: Works with Anthropic, OpenAI, and OpenRouter providers

---

## The User Journey

### For End Users (Feature Requests)

1. Log in and choose: **"New Feature"** or **"New Application"**
2. If requesting a feature, select the application from your catalog
3. Chat naturally with the AI Business Analyst about your idea
4. The assistant asks clarifying questions, explores edge cases, understands requirements
5. When satisfied, click **"Sign Off"** to finalize the conversation
6. Admins receive notification to review your request

### For Admins (Review & Approval)

1. Receive email notification of new conversation/PRD
2. Review conversation history and AI-generated PRD
3. Update conversation status: Pending → In Review → Approved → Completed
4. For new application ideas:
   - System auto-creates a **Proposed Application** when approved
   - Review proposal in Settings
   - Promote to **Internal** or reject as needed
5. For feature requests:
   - PRD is ready for dev team to implement
   - Track progress via conversation status

---

## Tech Stack

- **Laravel 12** - Latest Laravel framework
- **Livewire 3** - Reactive components for interactive UI
- **FluxUI** - Professional UI component library (Free + Pro)
- **Prism** - Multi-LLM integration (Anthropic, OpenAI, OpenRouter)
- **Pest** - Modern PHP testing framework
- **TailwindCSS 4** - Utility-first CSS framework
- **Lando** - Local development environment

---

## Installation

### Prerequisites

- PHP 8.4+
- Composer
- Node.js 18+
- [Lando](https://lando.dev/) (recommended for local development)
- Database (MySQL/PostgreSQL/SQLite)

### Setup

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd reqqy
   ```

2. **Install dependencies**
   ```bash
   lando composer install
   npm install
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   lando php artisan key:generate
   ```

4. **Configure LLM providers** (in `.env`)
   ```env
   # Default model for conversations (powerful)
   REQQY_LLM=anthropic/claude-3-5-sonnet-20241022

   # Small/cheap model for quick tasks (name extraction, etc.)
   REQQY_LLM_SMALL=anthropic/claude-3-haiku-20240307

   # Provider API keys
   ANTHROPIC_API_KEY=your-key-here
   OPENAI_API_KEY=your-key-here
   OPENROUTER_API_KEY=your-key-here
   ```

5. **Run migrations and seed test data**
   ```bash
   lando php artisan migrate
   lando php artisan db:seed --class=TestDataSeeder
   ```

6. **Build frontend assets**
   ```bash
   npm run build
   # Or for development with watch mode:
   npm run dev
   ```

7. **Start the application**
   ```bash
   lando start
   ```

Visit `https://reqqy.lndo.site` (or your configured Lando URL)

---

## Configuration

### LLM Provider Setup

Reqqy uses a litellm-style configuration format: `provider/model`

**Supported Providers:**
- `anthropic/claude-3-5-sonnet-20241022` (recommended for conversations)
- `anthropic/claude-3-haiku-20240307` (recommended for quick tasks)
- `openai/gpt-4`
- `openai/gpt-4-turbo`
- `openrouter/anthropic/claude-3.5-sonnet`

Configure in `config/reqqy.php` or via environment variables.

### Automated Application Overviews

For applications with repositories, Reqqy can automatically fetch `.llm.md` files for context:

1. Create `.llm.md` in your application's repo root
2. In Reqqy Settings, mark the application as `is_automated = true`
3. Set the `repo` field to `file:///path/to/repo` (local) or GitHub URL
4. Run manually: `lando php artisan reqqy:get-application-info --app-id=1`
5. Or wait for daily scheduled sync: `reqqy:get-application-info --all-apps`

The LLM will have full application context during feature request conversations.

---

## Usage

### Creating Your First Admin User

```bash
lando tinker
```

```php
$user = User::factory()->create([
    'username' => 'admin',
    'email' => 'admin@example.com',
    'is_admin' => true,
]);
```

### Running Tests

```bash
# Run all tests
lando php artisan test

# Run specific test file
lando php artisan test tests/Feature/ConversationObserverTest.php

# Run with filter
lando php artisan test --filter="it creates proposed application"
```

### Code Formatting

```bash
# Format all code
lando vendor/bin/pint

# Format specific files
lando vendor/bin/pint app/Services/LlmService.php
```

---

## Architecture

### The Three-Category Application Model

Reqqy treats "Applications" as a three-category software catalog:

**Internal Applications**
- Apps you own/manage in your portfolio
- Have repos, URLs, can be automated with `.llm.md` fetching
- Users can request features for these
- Created manually by admins OR promoted from Proposed state

**External Applications**
- Third-party SaaS/tools (reference only)
- Examples: Slack, Notion, existing market solutions
- LLM uses these to suggest alternatives during conversations
- Users cannot request features (redirected to helpdesk)

**Proposed Applications**
- Ideas from "New Application" conversations
- Auto-created when admin approves a new app conversation
- Staging area before entering production portfolio
- Can be promoted to Internal or rejected

### Key Workflows

**Feature Request Flow** (for Internal apps)
```
User selects Internal app → Conversation → Sign Off → PRD Generated
→ Admin Reviews → Status Updated → Dev Team Implements
```

**New Application Flow** (becomes Proposed)
```
User describes idea → Conversation → Sign Off → Admin Approves Status
→ System Auto-Creates Proposed Application → Admin Reviews in Settings
→ Promotes to Internal OR Rejects → (if promoted) Now available for feature requests
```

**Smart Name Extraction**
When creating Proposed applications, Reqqy uses a small/cheap LLM model to intelligently extract application names from conversations, with fallback to text truncation if needed.

---

## Development

### Project Structure

```
app/
├── Livewire/               # Livewire components (pages)
├── Models/                 # Eloquent models
├── Services/               # Business logic (LlmService, etc.)
├── Observers/              # Model observers (auto-creation logic)
├── Notifications/          # Email notifications
└── Console/Commands/       # Artisan commands

resources/
├── views/
│   ├── livewire/          # Livewire component views
│   ├── prompts/           # LLM prompt templates
│   └── components/        # Blade components
└── js/                    # Frontend assets

tests/
├── Feature/               # Feature tests (primary)
└── Unit/                  # Unit tests (minimal)
```

### Key Services

**LlmService** (`app/Services/LlmService.php`)
- Abstraction layer over Prism for LLM calls
- Supports multiple providers and models
- Renders chat prompts with application context
- Accepts custom system prompts for different use cases

**ConversationObserver** (`app/Observers/ConversationObserver.php`)
- Watches for conversation status changes to "Approved"
- Auto-creates Proposed applications for new app conversations
- Uses LLM for smart name extraction with fallback strategy
- Notifies admins of new proposals

---

## Testing Philosophy

Reqqy follows a feature-test-first approach:

- **Feature tests** verify complete user workflows
- **Test data** uses factories and seeders (`TestDataSeeder`)
- **LLM mocking** with `Prism::fake()` for fast, deterministic tests
- **Coverage** includes happy paths, failure paths, and edge cases

All tests must pass before merging:
```bash
lando php artisan test
# Tests:    105 passed (281 assertions)
```

---

## Roadmap

### MVP (Current Focus)
- ✅ Conversational requirements gathering with LLM
- ✅ Automatic PRD generation
- ✅ Three-category application catalog
- ✅ Admin review and approval workflow
- ✅ Smart application name extraction
- ⏳ Hook up PRD generation on conversation sign-off
- ⏳ Settings UI refactor (tabs for three categories)
- ⏳ LLM context integration (pass all apps to chat prompts)

### Phase Two (Future)
- Background research agent for codebase analysis
- Web research agent to find existing solutions
- Slack/Teams notification integration
- In-app notification system
- Document versioning and revision tracking
- User notification when PRD is complete

---

## Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Write tests for your changes
4. Ensure all tests pass (`lando php artisan test`)
5. Format code with Pint (`lando vendor/bin/pint`)
6. Commit your changes (`git commit -m 'Add amazing feature'`)
7. Push to the branch (`git push origin feature/amazing-feature`)
8. Open a Pull Request

---

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

## Acknowledgments

Built with:
- [Laravel](https://laravel.com) - The PHP Framework for Web Artisans
- [Livewire](https://livewire.laravel.com) - A full-stack framework for Laravel
- [FluxUI](https://fluxui.dev) - Beautiful UI components for Livewire
- [Prism](https://prismphp.com) - Laravel package for LLM integration
- [Pest](https://pestphp.com) - The elegant PHP testing framework

Special thanks to the Laravel community for their excellent tools and documentation.

---

**Built with ❤️ using Laravel 12, Livewire 3, and Claude AI**
