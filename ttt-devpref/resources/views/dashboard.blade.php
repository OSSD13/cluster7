@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <h2 class="text-2xl font-semibold text-gray-900">Welcome, {{ Auth::user()->name }}!</h2>
            <p class="mt-4 text-gray-600">You are successfully logged in to your dashboard.</p>
        </div>
    </div>
</div>
@endsection 