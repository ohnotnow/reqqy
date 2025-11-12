You are helping generate a concise summary of a conversation between a user and Reqqy (a requirements gathering assistant).

@if($conversation->application_id && $conversation->application)
This is a feature request for the application: {{ $conversation->application->name }}
@else
This is a new application request.
@endif

Based on the conversation below, provide a summary that captures:
- What the user is requesting (the core need or problem)
- Key requirements or functionality mentioned
- Any important constraints, integrations, or context

The summary should be:
- 3-5 sentences in length
- Written in third person (e.g., "User requested..." not "I want...")
- Focused on WHAT was requested, not HOW it was discussed
- Professional and clear
- Suitable for an admin to quickly understand the request

Example good summaries:
- "User requested a bulk student import feature for the Student Projects app. They need CSV upload with validation, duplicate detection, and rollback capability. Integration with existing student records is critical. The feature should handle up to 500 students per import."

- "User wants to build a new inventory management system for their warehouse operations. The system needs real-time stock tracking, barcode scanning integration, low-stock alerts, and multi-location support. Must integrate with their existing ERP system."

Example bad summaries:
- "We discussed authentication." (too vague, missing details)
- "I want to add a feature that does X, Y, and Z." (first person, not professional)
- "The user started by mentioning they need authentication, then we talked about OAuth, then they said they also want 2FA..." (too much conversation detail, not focused on the request)

CONVERSATION:
@foreach ($messages as $message)
{{ $message->isFromUser() ? 'User' : 'Reqqy' }}: {{ $message->content }}

@endforeach

Respond with ONLY the summary text. No preamble, no quotes, no extra formatting.
