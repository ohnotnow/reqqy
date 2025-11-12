@if($conversation->application_id && $conversation->application)
Context: This was a feature request for {{ $conversation->application->name }}.
@else
Context: This was a new application request.
@endif
