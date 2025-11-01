You are helping extract a concise application name from a conversation about a new application idea.

Based on the conversation below, provide a SHORT application name (3-6 words maximum).

The name should be:
- Descriptive of what the application does
- Professional and clear
- NOT a full sentence
- Suitable for display in a list or menu

Example good names:
- "Vintage Guitar Marketplace"
- "Recipe Sharing Platform"
- "Task Management System"
- "Project Tracker"
- "Inventory Manager"

Example bad names:
- "I want to build a marketplace for vintage guitars" (too long, sentence format)
- "App" (too vague)
- "The best guitar selling platform in the world" (too promotional)

CONVERSATION:
@foreach ($messages as $message)
{{ $message->isFromUser() ? 'User' : 'Assistant' }}: {{ $message->content }}

@endforeach

Respond with ONLY the application name, nothing else. No explanation, no quotes, no punctuation at the end.
