@extends('layouts.app')

@section('title', 'Sprint Reports')

@section('page-title', 'Sprint Reports')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">All Sprints</h1>
        
        <div class="flex space-x-3">
            <a href="{{ route('backlog.index') }}" class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                View Backlog
            </a>
            
            <a href="{{ route('story.points.report') }}" class="bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Create New Report
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    @if(count($sprints) > 0)
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <ul class="divide-y divide-gray-200">
                @foreach($sprints as $sprint)
                    <li>
                        <div class="block hover:bg-gray-50">
                            <div class="px-4 py-4 sm:px-6">
                                <div class="flex items-center justify-between">
                                    <div class="truncate">
                                        <div class="flex text-sm">
                                            <p class="font-medium text-primary-600 truncate">Sprint #{{ $sprint->sprint_number }}</p>
                                            <p class="ml-1 flex-shrink-0 font-normal text-gray-500">
                                                ({{ $sprint->formatted_start_date }} - {{ $sprint->formatted_end_date }})
                                            </p>
                                        </div>
                                        <div class="mt-2 flex">
                                            <div class="flex items-center text-sm text-gray-500 mr-4">
                                                <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                </svg>
                                                <span>{{ $sprint->reports->count() }} {{ Str::plural('report', $sprint->reports->count()) }}</span>
                                            </div>
                                            <div class="flex items-center text-sm text-gray-500">
                                                <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span>{{ ucfirst($sprint->status) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex flex-shrink-0 ml-2">
                                        <a href="{{ route('sprints.show', $sprint->id) }}" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-primary-700 bg-primary-100 hover:bg-primary-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                            View Reports
                                        </a>
                                    </div>
                                </div>
                                <!-- Progress bar -->
                                <div class="mt-4">
                                    <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                                        <span>Sprint Progress</span>
                                        <span>{{ number_format($sprint->progress_percentage, 1) }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div class="bg-primary-600 h-2.5 rounded-full" style="width: {{ $sprint->progress_percentage }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @else
        <div class="bg-white shadow rounded-lg p-6 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No sprint data available</h3>
            <p class="text-gray-500 mb-4">Configure your sprint settings and generate reports to track your sprint progress.</p>
            <a href="{{ route('settings.sprint') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                Configure Sprint Settings
            </a>
        </div>
    @endif
</div>
@endsection 