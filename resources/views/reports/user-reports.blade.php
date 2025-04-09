@extends('layouts.app')

@section('title', 'My Team Reports')

@section('page-title', 'My Team Reports')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Reports</h1>
        
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
                View Current Sprint Report
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    @if(empty($reportsBySprint))
        <div class="bg-white shadow rounded-lg p-6 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No reports available for your teams</h3>
            <p class="text-gray-500 mb-4">Create a new report to start tracking your team's progress.</p>
            <div class="flex justify-center space-x-4">
                <a href="{{ route('backlog.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
                    View Backlog
                </a>
                <a href="{{ route('story.points.report') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    View Current Sprint Report
                </a>
            </div>
        </div>
    @else
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <ul class="divide-y divide-gray-200">
                @foreach($reportsBySprint as $sprintId => $sprintData)
                    <li>
                        <div class="block hover:bg-gray-50">
                            <div class="px-4 py-4 sm:px-6">
                                <div class="flex items-center justify-between">
                                    <div class="truncate">
                                        <div class="flex text-sm">
                                            <p class="font-medium text-primary-600 truncate">Sprint #{{ $sprintData['sprint']->sprint_number }}</p>
                                            <p class="ml-1 flex-shrink-0 font-normal text-gray-500">
                                                ({{ \App\Helpers\DateHelper::formatSprintDate($sprintData['sprint']->start_date) }} - {{ \App\Helpers\DateHelper::formatSprintDate($sprintData['sprint']->end_date) }})
                                            </p>
                                        </div>
                                        <div class="mt-2">
                                            @foreach($sprintData['teams'] as $teamName => $teamReports)
                                                <div class="flex items-center text-sm text-gray-500 mb-1">
                                                    <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                    </svg>
                                                    <span class="font-medium">{{ $teamName }}</span>
                                                    <span class="ml-2">({{ count($teamReports) }} {{ Str::plural('report', count($teamReports)) }})</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="flex flex-shrink-0 ml-2 space-x-2">
                                        <a href="{{ route('sprints.show', $sprintId) }}" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-primary-700 bg-primary-100 hover:bg-primary-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                            View Reports
                                        </a>
                                    </div>
                                </div>
                                <!-- Progress bar -->
                                <div class="mt-4">
                                    <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                                        <span>Sprint Progress</span>
                                        <span>{{ number_format($sprintData['sprint']->progress_percentage, 1) }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div class="bg-primary-600 h-2.5 rounded-full" style="width: {{ $sprintData['sprint']->progress_percentage }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
@endsection