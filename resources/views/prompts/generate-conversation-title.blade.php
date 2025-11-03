You are helping generate a concise, descriptive title for a conversation.

@if($conversation->application_id && $conversation->application)
This is a feature request for the application: {{ $conversation->application->name }}
@else
This is a new application request.
@endif

Based on the conversation below, provide a SHORT title (4-8 words maximum) that captures the essence of what is being requested.

The title should be:
- Descriptive and clear
- Professional
- NOT a full sentence
- Suitable for display in a conversation list

Example good titles:
- "User Authentication Feature Request"
- "New Inventory Management System"
- "Payment Processing Integration"
- "Dark Mode UI Enhancement"
- "Customer Portal Dashboard"

Example bad titles:
- "I want to add user authentication" (sentence format, uses first person)
- "Request" (too vague)
- "This is about building a new system for managing inventory" (too long, sentence format)

CONVERSATION:
@foreach ($messages as $message)
{{ $message->isFromUser() ? 'User' : 'Assistant' }}: {{ $message->content }}

@endforeach

Respond with ONLY the title, nothing else. No explanation, no quotes, no punctuation at the end.
