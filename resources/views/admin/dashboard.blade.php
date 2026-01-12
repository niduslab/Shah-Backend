@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
 {{-- if user auth admin then show admin dashboard --}}
 @if(auth()->user()->user_type == 'admin')
    @include('admin.components.admin-dashboard-content')
 @elseif(auth()->user()->user_type == 'vendor')
    @include('admin.components.vendor-dashboard-content')
 @endif

@endsection
