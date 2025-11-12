@foreach ($messages as $message)
{{ $message->isFromUser() ? 'User' : 'Reqqy' }}: {{ $message->content }}

@endforeach
