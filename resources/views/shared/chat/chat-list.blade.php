
@extends('admin.dashboard')
@section('title', 'NidusCart - Manage Chats')
@section('content')
    <div class="list-group">
        @foreach($chats as $chat)
            <a href="{{ route('admin.chats.show', $chat->id) }}" class="list-group-item list-group-item-action">
                @if($chat->user)
                    {{ $chat->user->fname }} {{ $chat->user->lname }} (User)
                @else
                    Guest ({{ $chat->guest_id }})
                @endif
            </a>
        @endforeach
    </div>
@endsection
