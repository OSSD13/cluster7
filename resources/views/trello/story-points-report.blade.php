@extends('layouts.app')

@section('title', 'Story Points Report')

@section('page-title', 'Story Points Report')

@section('content')
<style>
    @media print {
        body {
            background-color: white;
            color: black;
            font-size: 12pt;
        }
        
        button, .no-print, #fetch-data-btn {
            display: none !important;
        }
        
        .print-full-width {
            width: 100% !important;
            max-width: 100% !important;
        }
        
        .print-break-inside-avoid {
            break-inside: avoid;
        }
        
        .shadow {
            box-shadow: none !important;
        }
        
        /* Ensure tables display properly when printed */
        table {
            border-collapse: collapse;
            width: 100%;
        }
        
        table, th, td {
            border: 1px solid #ddd;
        }
        
        th, td {
            padding: 8px;
            text-align: left;
        }
        
        .print-only { display: block !important; }
        .no-print { display: none !important; }
    }

    /* Hide scrollbar but keep functionality */
    .hide-scrollbar {
        -ms-overflow-style: none;  /* IE and Edge */
        scrollbar-width: none;  /* Firefox */
    }
    .hide-scrollbar::-webkit-scrollbar {
        display: none;  /* Chrome, Safari and Opera */
    }
    
    /* Card styling */
    .bug-card {
        transition: all 0.2s ease-in-out;
        border-left: 4px solid transparent;
    }
    .bug-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
    
    /* Priority colors */
    .priority-high {
        border-left-color: #ef4444;
    }
    .priority-medium {
        border-left-color: #f59e0b;
    }
    .priority-low {
        border-left-color: #10b981;
    }
    .priority-none {
        border-left-color: #d1d5db;
    }
    
    /* Loading spinner */
    .spinner {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        border: 3px solid rgba(0, 0, 0, 0.1);
        border-top-color: #3b82f6;
        animation: spin 1s ease-in-out infinite;
    }
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
</style>

<div class="max-w-6xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Story Points Report</h1>
        
        <!-- Action Menu -->
        <div class="flex space-x-2">
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="bg-white border border-gray-300 rounded-md px-4 py-2 flex items-center text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                    </svg>
                    Menu
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="open" @click.away="open = false" class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100">
                    <div class="py-1">
                        <!-- Save Report Button with Sprint Auto-Save Hint -->
                        <button id="save-report-btn" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left flex items-center relative group">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                            </svg>
                            Save Report
                            @if(auth()->user()->isAdmin())
                            <div class="hidden group-hover:block absolute z-10 -top-2 left-full ml-2 px-3 py-2 bg-gray-800 text-white text-xs rounded shadow-lg w-64">
                                <span class="relative">
                                    Sprint reports are saved automatically at the end of each week. 
                                    <a href="{{ route('settings.sprint') }}" class="text-blue-300 hover:underline">Configure in Sprint Settings</a>
                                    <span class="absolute -left-7 top-1/2 transform -translate-y-1/2 border-4 border-transparent border-r-gray-800"></span>
                                </span>
                            </div>
                            @endif
                        </button>
                        <button id="refresh-report-btn" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Refresh Data
                        </button>
                        <button id="print-report-btn" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            Print Report
                        </button>
                        <button id="export-csv-btn" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Export to CSV
                        </button>
                    </div>
                    <div class="py-1">
                        <a href="{{ route('saved-reports.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                            </svg>
                            Saved Reports
                        </a>
                        <a href="{{ route('trello.teams.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            View Teams
                        </a>
                        <a href="{{ route('trello.settings.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Trello Settings
                        </a>
                    </div>
                </div>
            </div>
            
            <button id="fetch-data-btn" class="bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Reload Data
            </button>
        </div>
    </div>

    @if(isset($error))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
            <p>{{ $error }}</p>
        </div>
    @endif

    <div id="error-container" class="hidden">
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
            <p id="error-message">Error loading data</p>
        </div>
    </div>

    <div id="role" class="hidden" data-role="{{ auth()->user()->isAdmin() ? 'admin' : 'user' }}"></div>

    <div class="bg-white shadow rounded-lg p-6 mb-6">
        @if(auth()->user()->isAdmin())
            <h2 class="text-lg font-semibold mb-4">Team Board</h2>
            <div class="mb-2 px-3 py-2 bg-blue-50 text-blue-700 rounded-lg text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                As an admin, you can access all available Trello boards. Data will be loaded automatically.
            </div>
            
            @if(count($boards) > 0)
                <div class="mb-4">
                    <select id="board-selector" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                        @foreach($boards as $board)
                            <option value="{{ $board['id'] }}" {{ $board['id'] == $defaultBoardId ? 'selected' : '' }}>
                                {{ $board['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @else
                <div class="mb-4 px-3 py-2 bg-yellow-50 text-yellow-700 rounded-lg text-sm">
                    No Trello boards available. Please configure your Trello API settings first.
                </div>
            @endif
        @elseif($singleTeam)
            <h2 class="text-lg font-semibold mb-4">Your Team</h2>
            <div class="mb-2 px-3 py-2 bg-blue-50 text-blue-700 rounded-lg text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                You are a member of: <span class="font-medium">{{ $boards[0]['name'] }}</span>
                <span class="block mt-1 text-xs">Data is being loaded automatically.</span>
            </div>
            <!-- Hidden board selector with default selection -->
            <input type="hidden" id="board-selector" value="{{ $boards[0]['id'] }}">
        @else
            <h2 class="text-lg font-semibold mb-4">Your Teams</h2>
            <div class="mb-2 px-3 py-2 bg-blue-50 text-blue-700 rounded-lg text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                You are a member of multiple teams. Data will be loaded automatically for the selected team.
            </div>
            
            <div class="mb-4">
                <select id="board-selector" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                    @foreach($boards as $board)
                        <option value="{{ $board['id'] }}" {{ $board['id'] == $defaultBoardId ? 'selected' : '' }}>
                            {{ $board['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif
    </div>

    <div id="loading-indicator" class="hidden">
        <div class="flex justify-center items-center bg-white shadow rounded-lg p-6 mb-6">
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-primary-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Loading data from Trello...</span>
        </div>
    </div>

 

    <div id="main-data-container" class="hidden">
        <div id="story-points-summary" class="hidden">
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <h2 class="text-lg font-semibold mb-4">
                    Sprint Statistics
                
                <!-- Date Display -->
                <div class="mb-3 text-sm text-gray-500 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Report Date: {{ $currentDate ?? Carbon\Carbon::now()->format('F d, Y') }}
                </div>
                
                <!-- Sprint Indicator -->
                <div class="mb-4 bg-gray-50 p-3 rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-primary-100 text-primary-800 mr-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </span>
                            <div>
                                <h3 class="font-medium text-gray-800">
                                    Current Sprint: <span id="current-sprint-number" class="text-primary-600">{{ $currentSprintNumber ?? '-' }}</span>
                                </h3>
                                <p class="text-sm text-gray-500">
                                    <span id="sprint-date-range">{{ $sprintDateRange ?? 'Loading sprint data...' }}</span> 
                                    (<span id="sprint-duration">{{ $sprintDurationDisplay ?? '-' }}</span>)
                                </p>
                                <p class="text-sm text-gray-500 mt-1">
                                    <strong>Next Sprint Report:</strong> <span id="next-report-date">{{ $nextReportDate ?? 'Not available' }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="text-right text-sm">
                            <div class="text-gray-600 font-medium">Day <span id="sprint-day-number">{{ $currentSprintDay ?? '-' }}</span> of <span id="sprint-total-days">{{ $sprintTotalDays ?? '-' }}</span></div>
                            <div class="text-gray-500">
                                <span id="sprint-days-remaining">{{ $daysRemaining ?? '-' }}</span> {{ ($daysRemaining ?? 0) == 1 ? 'day' : 'days' }} remaining
                            </div>
                            <div class="text-gray-500 mt-1 text-xs">
                                Week <span id="current-week-number">{{ $currentWeekNumber ?? '-' }}</span> of the year
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sprint Timeline Visualization -->
                    <div class="mt-4">
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Sprint Timeline</h3>
                        <div class="relative h-16 rounded-lg overflow-hidden bg-gray-100 shadow-inner">
                            <!-- Progress Background with Gradient -->
                            <div id="sprint-progress-bar" class="absolute top-0 left-0 h-full bg-gradient-to-r from-primary-400 to-primary-600" style="width: {{ $sprintProgressPercent ?? 0 }}%"></div>
                            
                            <!-- Day Markers - generate markers for each day -->
                            @php
                                $totalDays = $sprintTotalDays ?? 7;
                                $markerWidth = 100 / $totalDays;
                            @endphp
                            
                            @for ($i = 1; $i <= $totalDays; $i++)
                                <div class="absolute top-0 h-2 border-r border-gray-300" style="left: {{ $markerWidth * $i }}%; width: 1px;"></div>
                                <div class="absolute bottom-1 text-xs text-gray-500 font-medium" style="left: {{ $markerWidth * ($i - 0.5) }}%">{{ $i }}</div>
                            @endfor
                            
                            <!-- Current Day Marker (vertical line and circle) -->
                            @if(($sprintProgressPercent ?? 0) > 0 && ($sprintProgressPercent ?? 0) < 100)
                                <div class="absolute top-0 h-full" style="left: {{ $sprintProgressPercent ?? 0 }}%">
                                    <div class="w-0.5 h-full bg-white shadow-md"></div>
                                    <div class="absolute -top-1.5 -translate-x-1/2 w-5 h-5 rounded-full bg-white shadow-md border-2 border-primary-600 flex items-center justify-center">
                                        <span class="text-primary-700 text-xs font-bold">{{ $daysElapsed ?? 0 }}</span>
                                    </div>
                                </div>
                            @endif
                            
                            <!-- Sprint Number Badge -->
                            <div class="absolute top-0 right-0 bg-primary-600 text-white px-3 py-1 rounded-bl-md text-xs font-bold shadow-sm">
                                Sprint {{ $currentSprintNumber ?? '-' }}
                            </div>
                            
                            <!-- Start Date -->
                            <div class="absolute top-7 left-2 text-xs font-medium text-gray-700 bg-white/70 backdrop-blur-sm px-1 py-0.5 rounded">
                                <span class="text-primary-700">Start:</span> {{ $currentSprintStartDate ?? '-' }}
                            </div>
                            
                            <!-- End Date -->
                            <div class="absolute top-7 right-2 text-xs font-medium text-gray-700 bg-white/70 backdrop-blur-sm px-1 py-0.5 rounded">
                                <span class="text-primary-700">End:</span> {{ $currentSprintEndDate ?? '-' }}
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                    <div class="bg-gray-50 rounded-lg p-4 text-center">
                        <div class="flex items-center justify-between mb-2">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-200 text-gray-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </span>
                            <span class="rounded-full bg-gray-200 text-xs px-2 py-1 text-gray-700 font-semibold">Target</span>
                        </div>
                        <h3 class="text-sm font-medium text-gray-500">Plan Point</h3>
                        <div class="relative">
                            <input type="number" id="plan-points" class="text-2xl font-bold text-gray-800 bg-transparent w-full text-center py-1 border-b border-dashed border-gray-300 focus:outline-none focus:border-primary-500" value="0">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                <button id="edit-plan-points" class="text-gray-400 hover:text-gray-600 pointer-events-auto">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-4 text-center">
                        <div class="flex items-center justify-between mb-2">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-200 text-blue-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </span>
                            <span class="rounded-full bg-blue-200 text-xs px-2 py-1 text-blue-700 font-semibold">Done</span>
                        </div>
                        <h3 class="text-sm font-medium text-blue-500">Actual Point</h3>
                        <p id="actual-points" class="text-2xl font-bold text-blue-600">0</p>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4 text-center">
                        <div class="flex items-center justify-between mb-2">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-200 text-green-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </span>
                            <span class="rounded-full bg-green-200 text-xs px-2 py-1 text-green-700 font-semibold">Remaining</span>
                        </div>
                        <h3 class="text-sm font-medium text-green-500">Remain Percent</h3>
                        <p id="remain-percent" class="text-2xl font-bold text-green-600">0%</p>
                    </div>
                    <div class="bg-purple-50 rounded-lg p-4 text-center">
                        <div class="flex items-center justify-between mb-2">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-purple-200 text-purple-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                                </svg>
                            </span>
                            <span class="rounded-full bg-purple-200 text-xs px-2 py-1 text-purple-700 font-semibold">Progress</span>
                        </div>
                        <h3 class="text-sm font-medium text-purple-500">Percent</h3>
                        <p id="percent-complete" class="text-2xl font-bold text-purple-600">0%</p>
                    </div>
                    <div class="bg-yellow-50 rounded-lg p-4 text-center">
                        <div class="flex items-center justify-between mb-2">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-yellow-200 text-yellow-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </span>
                            <span class="rounded-full bg-yellow-200 text-xs px-2 py-1 text-yellow-700 font-semibold">Current</span>
                        </div>
                        <h3 class="text-sm font-medium text-yellow-500">Point Current Sprint</h3>
                        <p id="current-sprint-points" class="text-2xl font-bold text-yellow-600">0</p>
                    </div>
                    <div class="bg-red-50 rounded-lg p-4 text-center">
                        <div class="flex items-center justify-between mb-2">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-200 text-red-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </span>
                            <span class="rounded-full bg-red-200 text-xs px-2 py-1 text-red-700 font-semibold">Actual</span>
                        </div>
                        <h3 class="text-sm font-medium text-red-500">Actual Point Current Sprint</h3>
                        <p id="actual-current-sprint" class="text-2xl font-bold text-red-600">0</p>
                    </div>
                </div>
            </div>

            <!-- Team Member Points Table -->
            <div id="team-member-points-container" class="bg-white shadow rounded-lg p-6 mb-6">
                <h2 class="text-lg font-semibold mb-4">
                    <span id="points-title">Points from Current Sprint</span>
                    <span id="board-name-display" class="ml-2 text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full hidden">
                        Loading board name...
                    </span>
                    <span id="last-updated" class="text-xs text-gray-500 block mt-1 hidden">
                        Last updated: ...
                    </span>
                </h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="py-3 px-4 border-b text-left">Member</th>
                                <th class="py-3 px-4 border-b text-center">Point Personal</th>
                                <th class="py-3 px-4 border-b text-center">Pass</th>
                                <th class="py-3 px-4 border-b text-center">Bug</th>
                                <th class="py-3 px-4 border-b text-center">Cancel</th>
                                <th class="py-3 px-4 border-b text-center">Final</th>
                                <th class="py-3 px-4 border-b text-center">Pass %</th>
                            </tr>
                        </thead>
                        <tbody id="team-members-table-body">
                            <tr>
                                <td colspan="7" class="py-4 px-4 text-center text-gray-500">Loading team data...</td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-gray-50 font-semibold">
                            <tr>
                                <td class="py-3 px-4 border-t text-left">Totals</td>
                                <td id="total-personal" class="py-3 px-4 border-t text-center">0</td>
                                <td id="total-pass" class="py-3 px-4 border-t text-center">0</td>
                                <td id="total-bug" class="py-3 px-4 border-t text-center">0</td>
                                <td id="total-cancel" class="py-3 px-4 border-t text-center">0</td>
                                <td id="total-final" class="py-3 px-4 border-t text-center">0</td>
                                <td class="py-3 px-4 border-t text-center">-</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Changed from Cards by List to Current Bug -->
            <div class="mt-8">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-gray-800">Current Bug <span id="bug-count" class="text-sm font-normal text-gray-500">0 bugs</span></h2>
                        <div class="text-sm text-gray-500">
                            Total Points: <span id="total-bug-points" class="font-semibold">0</span>
                        </div>
                    </div>
                    
                    <div id="cards-by-list-container" class="hidden relative">
                        <div class="absolute inset-0 flex items-center justify-center" id="cards-loading">
                            <div class="spinner"></div>
                        </div>

                        <div class="overflow-x-auto hide-scrollbar" style="scrollbar-width: none;">
                            <div id="bug-cards-container" class="flex gap-4 p-4 overflow-x-auto min-h-[200px]"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 text-sm text-gray-600">
        <p><strong>Note:</strong> Calculation methods:</p>
        <ul class="list-disc ml-5">
            <li>Pass: Points of cards in "Done" section/list or with "Done"/"Pass" labels</li>
            <li>Bug: Points of cards not in "Done" section and not in "Cancel" section</li>
            <li>Final: Equal to Pass points</li>
            <li>Pass %: (Pass / Point Personal) × 100</li>
        </ul>
    </div>

    <!-- Backlog Section -->
    @if(isset($backlogData) && isset($backlogData['allBugs']) && $backlogData['allBugs']->count() > 0)
    <div class="mt-8">
        <div class="bg-gray-50 shadow rounded-lg p-6 mb-8 border-l-4 border-amber-500">
            <h2 class="text-xl font-semibold mb-2 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-amber-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Backlog ({{ $backlogData['bugCount'] }})
            </h2>
            <p class="text-sm text-gray-600 mb-4">These bugs were carried over from previous sprints.</p>
            
            <div class="mt-4">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bug</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Points</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Team</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sprint</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($backlogData['allBugs'] as $bug)
                                <tr class="hover:bg-amber-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <a href="{{ $bug['url'] ?? '#' }}" class="text-primary-600 hover:text-primary-900" target="_blank">{{ $bug['id'] }}</a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $bug['name'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if(isset($bug['labels']) && is_array($bug['labels']))
                                            @foreach($bug['labels'] as $label)
                                                @if($label !== 'Backlog')
                                                    <span class="px-2 py-1 text-xs font-medium rounded-full 
                                                        {{ $label === 'High' ? 'bg-red-100 text-red-800' : 
                                                        ($label === 'Medium' ? 'bg-yellow-100 text-yellow-800' : 
                                                        'bg-green-100 text-green-800') }}">
                                                        {{ $label }}
                                                    </span>
                                                @endif
                                            @endforeach
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $bug['assigned'] ?? 'Unassigned' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $bug['points'] ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $bug['team'] ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if(isset($bug['sprint_origin']))
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                                Sprint {{ $bug['sprint_origin'] }}
                                            </span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">
                                                Sprint {{ $bug['sprint_number'] ?? '?' }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-100">
                                <th colspan="4" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Bugs: {{ $backlogData['allBugs']->count() }}</th>
                                <th colspan="3" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Points: {{ $backlogData['totalBugPoints'] }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            
            <div class="mt-4 text-right">
                <a href="{{ route('backlog.index') }}" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    View Full Backlog
                </a>
            </div>
        </div>
    </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const boardSelector = document.getElementById('board-selector');
        const fetchDataBtn = document.getElementById('fetch-data-btn');
        const refreshReportBtn = document.getElementById('refresh-report-btn');
        const printReportBtn = document.getElementById('print-report-btn');
        const loadingIndicator = document.getElementById('loading-indicator');
        const storyPointsSummary = document.getElementById('story-points-summary');
        const cardsByListContainer = document.getElementById('cards-by-list-container');
        const planPointsInput = document.getElementById('plan-points');
        let currentBoardId = '';

        // Set the current board ID from the selector's value
        if (boardSelector && boardSelector.value) {
            currentBoardId = boardSelector.value;
        }

        // When the board selector changes, auto-fetch data
        boardSelector.addEventListener('change', function() {
            // Update current board ID
            currentBoardId = this.value;
            
            // Reset UI elements
            document.getElementById('points-title').textContent = 'Points from Current Sprint';
            document.getElementById('board-name-display').classList.add('hidden');
            document.getElementById('last-updated').classList.add('hidden');
            
            // Automatically fetch data when selection changes
            if (currentBoardId) {
                fetchDataBtn.click();
            }
        });

        // Connect the refresh button in the menu to the fetch data button
        if (refreshReportBtn) {
            refreshReportBtn.addEventListener('click', function() {
                fetchDataBtn.click();
            });
        }

        // Add print functionality to the print button
        if (printReportBtn) {
            printReportBtn.addEventListener('click', function() {
                window.print();
            });
        }

        // Auto-fetch data on page load (for all users)
        if (currentBoardId) {
            // Use a slight delay to ensure the DOM is fully loaded
            setTimeout(function() {
                fetchDataBtn.click();
            }, 100);
        }

        fetchDataBtn.addEventListener('click', function() {
            const boardId = boardSelector.value;
            if (!boardId) return;

            // Update current board ID
            currentBoardId = boardId;

            console.clear(); // Clear console for better debugging
            console.log('Fetching data for board ID:', boardId);

            // Show loading toast notification
            showToast('Loading data from Trello...', 'info');

            // Clear any previous error messages
            document.querySelectorAll('.api-error-message').forEach(el => el.remove());

            // IMPORTANT: Completely destroy and recreate the table before fetching new data
            recreateTeamMembersTable();

            // Reset all other data
            clearAllData();

            // Show loading indicator
            loadingIndicator.classList.remove('hidden');
            storyPointsSummary.classList.add('hidden');
            cardsByListContainer.classList.add('hidden');

            // Force browser to make a fresh request with random parameter
            const timestamp = Date.now();
            const randomStr = Math.random().toString(36).substring(7);

            fetch(`/trello/data?board_id=${boardId}&_nocache=${timestamp}-${randomStr}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Cache-Control': 'no-cache, no-store, must-revalidate',
                    'Pragma': 'no-cache',
                    'Expires': '0',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                cache: 'no-store'
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('New API response received:', data);

                if (data.error) {
                    throw new Error(data.error);
                }

                // Update board details before anything else
                updateBoardDetails(data.boardDetails);

                // Update summary statistics
                updateSummaryData(data.storyPoints);

                // Completely rebuild member table with fresh data
                if (data.memberPoints && Array.isArray(data.memberPoints)) {
                    console.log('Member points data received:', data.memberPoints.length, 'members');
                    buildMemberTable(data.memberPoints);
                } else {
                    console.warn('No member points data available');
                    showNoMembersMessage();
                }

                // Update cards data
                if (data.cardsByList) {
                    renderCardsByList(data.cardsByList);
                } else {
                    showNoCardsMessage();
                }

                // Show all containers now that we have data
                storyPointsSummary.classList.remove('hidden');
                cardsByListContainer.classList.remove('hidden');

                // Hide loading indicator
                loadingIndicator.classList.add('hidden');
                
                // Show success toast
                showToast('Data loaded successfully', 'success');
            })
            .catch(error => {
                console.error('Fetch failed:', error);
                loadingIndicator.classList.add('hidden');

                showErrorMessage(error.message || 'Unknown error fetching data');
                
                // Show error toast
                showToast('Failed to load data: ' + error.message, 'error');
            });
        });

        // Function to completely destroy and recreate the table to prevent stale DOM
        function recreateTeamMembersTable() {
            const tableContainer = document.querySelector('#team-member-points-container .overflow-x-auto');
            const oldTable = tableContainer.querySelector('table');

            // Create a brand new table
            const newTable = document.createElement('table');
            newTable.className = 'min-w-full bg-white border border-gray-200';
            newTable.innerHTML = `
                <thead>
                    <tr class="bg-gray-100">
                        <th class="py-3 px-4 border-b text-left">Member</th>
                        <th class="py-3 px-4 border-b text-center">Point Personal</th>
                        <th class="py-3 px-4 border-b text-center">Pass</th>
                        <th class="py-3 px-4 border-b text-center">Bug</th>
                        <th class="py-3 px-4 border-b text-center">Cancel</th>
                        <th class="py-3 px-4 border-b text-center">Final</th>
                        <th class="py-3 px-4 border-b text-center">Pass %</th>
                    </tr>
                </thead>
                <tbody id="team-members-table-body">
                    <tr>
                        <td colspan="7" class="py-4 px-4 text-center text-gray-500">Loading team data...</td>
                    </tr>
                </tbody>
                <tfoot class="bg-gray-50 font-semibold">
                    <tr>
                        <td class="py-3 px-4 border-t text-left">Totals</td>
                        <td id="total-personal" class="py-3 px-4 border-t text-center">0</td>
                        <td id="total-pass" class="py-3 px-4 border-t text-center">0</td>
                        <td id="total-bug" class="py-3 px-4 border-t text-center">0</td>
                        <td id="total-cancel" class="py-3 px-4 border-t text-center">0</td>
                        <td id="total-final" class="py-3 px-4 border-t text-center">0</td>
                        <td class="py-3 px-4 border-t text-center">-</td>
                    </tr>
                </tfoot>
            `;

            // Replace old table with new one
            tableContainer.removeChild(oldTable);
            tableContainer.appendChild(newTable);
        }

        function updateBoardDetails(boardDetails) {
            if (!boardDetails) return;

            const pointsTitle = document.getElementById('points-title');
            const boardNameDisplay = document.getElementById('board-name-display');
            const lastUpdated = document.getElementById('last-updated');

            // Update the title with board name
            if (boardDetails.name) {
                pointsTitle.textContent = `Points from ${boardDetails.name}`;
                boardNameDisplay.textContent = boardDetails.name;
                boardNameDisplay.classList.remove('hidden');
            }

            // Update last activity date
            if (boardDetails.dateLastActivity) {
                lastUpdated.textContent = `Last updated: ${new Date(boardDetails.dateLastActivity).toLocaleString()}`;
                lastUpdated.classList.remove('hidden');
            }
        }

        function updateSummaryData(storyPoints) {
            if (!storyPoints) return;

            // Check if we have a saved plan point value for this board
            const savedPlanPoints = localStorage.getItem(`planPoints_${currentBoardId}`);

            if (savedPlanPoints) {
                // Use the saved value if it exists
                planPointsInput.value = savedPlanPoints;
            } else {
                // Initialize with total points from API on first fetch
                planPointsInput.value = storyPoints.total || 0;
                // Save this initial value
                if (currentBoardId) {
                    localStorage.setItem(`planPoints_${currentBoardId}`, planPointsInput.value);
                }
            }

            // Note: We'll update the Actual Point in buildMemberTable when we have the final point total
            // We're still initializing it here to ensure it's reset if needed
            document.getElementById('actual-points').textContent = '0';
            
            // Calculate values for other metrics based on plan points
            const planPoints = parseFloat(planPointsInput.value) || 0;
            
            // Other calculations will be updated once we have the actual point from team data
            document.getElementById('remain-percent').textContent = '0%';
            document.getElementById('percent-complete').textContent = '0%';
        }

        function buildMemberTable(members) {
            // Get a fresh reference to the table body
            const tableBody = document.getElementById('team-members-table-body');

            // Clear the table completely
            tableBody.innerHTML = '';

            if (!members || members.length === 0) {
                showNoMembersMessage();
                return;
            }

            // Calculate totals for all metrics
            let totals = {
                personal: 0,
                pass: 0,
                bug: 0,
                cancel: 0,
                final: 0
            };

            // Build each row with fresh data
            members.forEach(member => {
                // Extract numeric values safely
                const pointPersonal = parseFloat(member.pointPersonal || 0);
                const passPoint = parseFloat(member.passPoint || 0);
                const bugPoint = parseFloat(member.bugPoint || 0);
                const cancelPoint = parseFloat(member.cancelPoint || 0);
                const finalPoint = parseFloat(member.finalPoint || 0);

                // Update running totals
                totals.personal += pointPersonal;
                totals.pass += passPoint;
                totals.bug += bugPoint;
                totals.cancel += cancelPoint;
                totals.final += finalPoint;

                // Calculate pass percentage: (passPoint / pointPersonal) * 100
                const passPercentage = pointPersonal > 0
                    ? Math.round((passPoint / pointPersonal) * 100)
                    : 0;

                // Higher pass percentage is better (>= 80% is good)
                const passPercentageClass = passPercentage >= 80 ? 'text-green-600' : 'text-red-600';

                // Create a new row for this member
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50';
                row.dataset.memberId = member.id;

                // Build the row HTML
                row.innerHTML = `
                    <td class="py-3 px-4 border-b">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                                <span class="text-blue-800 font-bold">${member.fullName ? member.fullName.charAt(0) : '?'}</span>
                            </div>
                            <div>
                                <div class="font-medium">${member.fullName || 'Unknown'}</div>
                                <div class="text-gray-500 text-xs">@${member.username || ''}</div>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-4 border-b text-center font-semibold">${pointPersonal.toFixed(1)}</td>
                    <td class="py-3 px-4 border-b text-center text-green-600">${passPoint.toFixed(1)}</td>
                    <td class="py-3 px-4 border-b text-center text-red-600">${bugPoint.toFixed(1)}</td>
                    <td class="py-3 px-4 border-b text-center text-orange-600">${cancelPoint.toFixed(1)}</td>
                    <td class="py-3 px-4 border-b text-center font-bold">${finalPoint.toFixed(1)}</td>
                    <td class="py-3 px-4 border-b text-center">
                        <div class="inline-block w-12 text-center font-medium ${passPercentageClass}">
                            ${passPercentage}%
                        </div>
                    </td>
                `;

                // Add the row to the table
                tableBody.appendChild(row);
            });

            // Update footer totals with fresh values
            document.getElementById('total-personal').textContent = totals.personal.toFixed(1);
            document.getElementById('total-pass').textContent = totals.pass.toFixed(1);
            document.getElementById('total-bug').textContent = totals.bug.toFixed(1);
            document.getElementById('total-cancel').textContent = totals.cancel.toFixed(1);
            document.getElementById('total-final').textContent = totals.final.toFixed(1);

            // 5. Point Current Sprint (sum of member personal points)
            document.getElementById('current-sprint-points').textContent = totals.personal.toFixed(1);
            
            // 6. Actual Point Current Sprint (sum of final points)
            document.getElementById('actual-current-sprint').textContent = totals.final.toFixed(1);
            
            // Now update the Actual Point using the total final points
            // 2. Actual Point = Final Point + Backlog Point + Extra Point
            // (Backlog and Extra are set to 0 as requested)
            const actualPoints = totals.final + 0 + 0; // Use totals.final instead of completedPoints
            document.getElementById('actual-points').textContent = actualPoints.toFixed(1);
            
            // Now recalculate remaining metrics based on the actual point from team data
            const planPoints = parseFloat(planPointsInput.value) || 0;
            
            // 3. Remain Percent = (Plan Point - Actual Point) / Plan Point * 100
            let remainPercent = 0;
            if (planPoints > 0) {
                remainPercent = Math.round(((planPoints - actualPoints) / planPoints) * 100);
            }
            document.getElementById('remain-percent').textContent = `${remainPercent}%`;
            
            // 4. Percent = Actual / Plan Point * 100
            let percentComplete = 0;
            if (planPoints > 0) {
                percentComplete = Math.round((actualPoints / planPoints) * 100);
            }
            document.getElementById('percent-complete').textContent = `${percentComplete}%`;

            console.log('Team member table updated with', members.length, 'members');
        }

        function showNoMembersMessage() {
            document.getElementById('team-members-table-body').innerHTML = `
                <tr><td colspan="7" class="py-4 px-4 text-center text-gray-500">
                    No team members found with story points in this board.
                </td></tr>
            `;

            // Also reset totals
            document.getElementById('total-personal').textContent = '0.0';
            document.getElementById('total-pass').textContent = '0.0';
            document.getElementById('total-bug').textContent = '0.0';
            document.getElementById('total-cancel').textContent = '0.0';
            document.getElementById('total-final').textContent = '0.0';
        }

        function clearAllData() {
            // Reset all data displays to empty/zero but don't clear the planPointsInput
            // as it may contain user-edited values (will be reset properly in updateSummaryData)
            document.getElementById('actual-points').textContent = '0';
            document.getElementById('remain-percent').textContent = '0%';
            document.getElementById('percent-complete').textContent = '0%';
            document.getElementById('current-sprint-points').textContent = '0';
            document.getElementById('actual-current-sprint').textContent = '0';

            // Reset team members table
            document.getElementById('team-members-table-body').innerHTML = '';
            document.getElementById('total-personal').textContent = '0';
            document.getElementById('total-pass').textContent = '0';
            document.getElementById('total-bug').textContent = '0';
            document.getElementById('total-cancel').textContent = '0';
            document.getElementById('total-final').textContent = '0';

            // Reset cards list
            document.getElementById('bug-cards-container').innerHTML = '';

            // Reset title and metadata
            document.getElementById('points-title').textContent = 'Points from Current Sprint';
            document.getElementById('board-name-display').classList.add('hidden');
            document.getElementById('last-updated').classList.add('hidden');
        }

        function showErrorMessage(message) {
            const errorMessage = document.createElement('div');
            errorMessage.className = 'bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded api-error-message';
            errorMessage.innerHTML = `
                <h3 class="font-bold">Error fetching data</h3>
                <p>${message}</p>
                <p class="mt-2 text-sm">Try reloading the page or selecting a different board.</p>
            `;
            document.querySelector('.max-w-7xl').prepend(errorMessage);
        }

        function showNoCardsMessage() {
            document.getElementById('bug-cards-container').innerHTML = `
                <div class="flex items-center justify-center min-w-full py-10 text-yellow-600 bg-yellow-50 rounded-lg">
                    No bug cards with story points found in this board.
                </div>
            `;
            cardsByListContainer.classList.remove('hidden');
        }

        // Completely rewritten cards renderer
        function renderCardsByList(listsData) {
            const container = document.getElementById('bug-cards-container');
            container.innerHTML = '';

            // Array to hold all bug cards across all lists
            let bugCards = [];

            // Track done and cancelled list names (same logic as server-side)
            const doneListNames = [];
            const cancelListNames = [];

            // First, identify done and cancel lists
            for (const [listName, listData] of Object.entries(listsData)) {
                const lowerListName = listName.toLowerCase();
                if (lowerListName.includes('done') || lowerListName.includes('complete') ||
                    lowerListName.includes('finished') || lowerListName.includes('pass')) {
                    doneListNames.push(listName);
                } else if (lowerListName.includes('cancel') || lowerListName.includes('cancelled') ||
                          lowerListName.includes('canceled')) {
                    cancelListNames.push(listName);
                }
            }

            // Process all lists and collect bug cards using the same logic as in TrelloController.php
            for (const [listName, listData] of Object.entries(listsData)) {
                if (listData.cards && listData.cards.length > 0) {
                    listData.cards.forEach(card => {
                        // Check if this is a bug card using same logic as server-side
                        let isDone = false;
                        let isCancelled = false;

                        // First check if card is in a Done list
                        if (doneListNames.includes(listName)) {
                            isDone = true;
                        }
                        // Then check if card is in a Cancel list
                        else if (cancelListNames.includes(listName)) {
                            isCancelled = true;
                        }

                        // If not determined by list, check labels as backup
                        if (!isDone && !isCancelled && card.labels && card.labels.length > 0) {
                            for (const label of card.labels) {
                                const labelName = label.name.toLowerCase();
                                if (labelName === 'done' || labelName === 'pass') {
                                    isDone = true;
                                    break;
                                } else if (labelName === 'cancel' || labelName === 'cancelled') {
                                    isCancelled = true;
                                    break;
                                }
                            }
                        }

                        // Add to bugCards if it's not done and not cancelled and card.points > 0
                        if (!isDone && !isCancelled && card.points > 0) {
                            bugCards.push({
                                ...card,
                                listName: listName
                            });
                        }
                    });
                }
            }

            // Update bug count in the title
            document.getElementById('bug-count').textContent = `${bugCards.length} bug${bugCards.length !== 1 ? 's' : ''}`;

            // Calculate total bug points
            let totalBugPoints = 0;
            bugCards.forEach(card => {
                totalBugPoints += card.points || 0;
            });
            document.getElementById('total-bug-points').textContent = totalBugPoints;

            // If no bug cards found
            if (bugCards.length === 0) {
                container.innerHTML = `
                    <div class="flex items-center justify-center min-w-full py-10 text-gray-500 rounded-lg bg-gray-50">
                        No bug cards found in this board.
                    </div>
                `;
                cardsByListContainer.classList.remove('hidden');
                return;
            }

            // Determine priority class based on card properties
            function getPriorityClass(card) {
                // Check if card has priority labels
                if (card.labels && card.labels.length > 0) {
                    for (const label of card.labels) {
                        const labelName = label.name.toLowerCase();
                        if (labelName.includes('high') || labelName.includes('urgent') || labelName.includes('critical')) {
                            return 'priority-high';
                        } else if (labelName.includes('medium') || labelName.includes('normal')) {
                            return 'priority-medium';
                        } else if (labelName.includes('low')) {
                            return 'priority-low';
                        }
                    }
                }
                
                // If no priority label, try to determine by points
                if (card.points >= 5) {
                    return 'priority-high';
                } else if (card.points >= 3) {
                    return 'priority-medium';
                } else if (card.points > 0) {
                    return 'priority-low';
                }
                
                return 'priority-none';
            }

            // Render each bug card in the new card layout
            bugCards.forEach(card => {
                const priorityClass = getPriorityClass(card);
                const cardElement = document.createElement('div');
                cardElement.className = `bug-card bg-white rounded-lg shadow-md min-w-[300px] max-w-[300px] ${priorityClass}`;
                
                // Extract member names if available
                let memberNames = 'Not assigned';
                let memberAvatars = '';
                if (card.members && card.members.length > 0) {
                    memberNames = card.members.map(member => member.fullName || member.username).join(', ');
                    
                    // Create member avatars
                    memberAvatars = '<div class="flex -space-x-2 overflow-hidden">';
                    card.members.forEach((member, index) => {
                        if (index < 3) { // Limit to 3 avatars to save space
                            const initial = (member.fullName || member.username || '?').charAt(0).toUpperCase();
                            memberAvatars += `
                                <div class="inline-block h-6 w-6 rounded-full bg-blue-100 text-blue-800 flex items-center justify-center text-xs font-bold ring-2 ring-white">
                                    ${initial}
                                </div>
                            `;
                        }
                    });
                    
                    if (card.members.length > 3) {
                        memberAvatars += `
                            <div class="inline-block h-6 w-6 rounded-full bg-gray-200 text-gray-600 flex items-center justify-center text-xs font-bold ring-2 ring-white">
                                +${card.members.length - 3}
                            </div>
                        `;
                    }
                    
                    memberAvatars += '</div>';
                } else {
                    memberAvatars = `
                        <div class="inline-block h-6 w-6 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center text-xs font-bold">
                            ?
                        </div>
                    `;
                }

                // Create a formatted display for labels
                let labelDisplay = '';
                if (card.labels && card.labels.length > 0) {
                    labelDisplay = '<div class="flex flex-wrap gap-1 mt-2">';
                    card.labels.forEach(label => {
                        const colorClass = getColorClassForLabel(label.color);

                        labelDisplay += `
                            <span class="text-xs py-0.5 px-1.5 rounded-full font-medium ${colorClass}">
                                ${label.name}
                            </span>
                        `;
                    });
                    labelDisplay += '</div>';
                }

                // Create truncated description with expansion on hover
                const description = card.description || 'No description provided';
                const truncatedDescription = description.length > 100 ? 
                    description.substring(0, 100) + '...' : 
                    description;

                // Build the card HTML
                cardElement.innerHTML = `
                    <div class="p-4 h-full flex flex-col">
                        <div class="flex justify-between items-start">
                            <div class="font-medium text-gray-900 mb-1">${card.name || 'Unnamed Card'}</div>
                            <div class="flex-shrink-0 ml-2">
                                <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-100 bg-red-600 rounded-full">
                                    ${card.points || 0}
                                </span>
                            </div>
                        </div>
                        
                        <div class="text-xs text-gray-500 mb-2">
                            From: ${card.listName}
                        </div>
                        
                        ${labelDisplay}
                        
                        <div class="text-sm text-gray-700 mt-2 overflow-hidden hover:overflow-auto flex-grow">
                            <div class="description-content">${truncatedDescription}</div>
                            <div class="description-full hidden">${description}</div>
                        </div>
                        
                        <div class="mt-3 pt-3 border-t border-gray-100 flex justify-between items-center">
                            <div>
                                ${memberAvatars}
                                <div class="text-xs text-gray-500 mt-1 max-w-[180px] truncate" title="${memberNames}">
                                    ${memberNames}
                                </div>
                            </div>
                            <a href="${card.url || '#'}" target="_blank" class="text-primary-600 text-xs hover:underline flex items-center" title="Open in Trello">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                                View
                            </a>
                        </div>
                    </div>
                `;

                // Add click event to show full description
                cardElement.querySelector('.description-content').addEventListener('click', function() {
                    const fullDescription = cardElement.querySelector('.description-full');
                    const shortDescription = cardElement.querySelector('.description-content');
                    
                    if (fullDescription.classList.contains('hidden')) {
                        fullDescription.classList.remove('hidden');
                        shortDescription.classList.add('hidden');
                    } else {
                        fullDescription.classList.add('hidden');
                        shortDescription.classList.remove('hidden');
                    }
                });

                // Add the card to the container
                container.appendChild(cardElement);
            });

            cardsByListContainer.classList.remove('hidden');
        }

        function getColorClassForLabel(color) {
            switch(color) {
                case 'green':
                    return 'bg-green-100 text-green-800';
                case 'yellow':
                    return 'bg-yellow-100 text-yellow-800';
                case 'orange':
                    return 'bg-orange-100 text-orange-800';
                case 'red':
                    return 'bg-red-100 text-red-800';
                case 'purple':
                    return 'bg-purple-100 text-purple-800';
                case 'blue':
                    return 'bg-blue-100 text-blue-800';
                case 'sky':
                case 'lightblue':
                    return 'bg-sky-100 text-sky-800';
                case 'lime':
                    return 'bg-lime-100 text-lime-800';
                case 'pink':
                    return 'bg-pink-100 text-pink-800';
                case 'black':
                    return 'bg-gray-800 text-white';
                default:
                    return 'bg-gray-100 text-gray-800';
            }
        }

        // Add event listener for plan points editing
        planPointsInput.addEventListener('change', function() {
            // Save the edited value to localStorage with board ID as part of the key
            if (currentBoardId) {
                localStorage.setItem(`planPoints_${currentBoardId}`, this.value);

                // Recalculate metrics based on the new plan points value
                const planPoints = parseFloat(this.value) || 0;
                
                // Get the actual point (which is based on the final points total)
                const actualPoints = parseFloat(document.getElementById('actual-points').textContent) || 0;
                
                // Recalculate remain percent
                let remainPercent = 0;
                if (planPoints > 0) {
                    remainPercent = Math.round(((planPoints - actualPoints) / planPoints) * 100);
                }
                document.getElementById('remain-percent').textContent = `${remainPercent}%`;
                
                // Recalculate percent complete
                let percentComplete = 0;
                if (planPoints > 0) {
                    percentComplete = Math.round((actualPoints / planPoints) * 100);
                }
                document.getElementById('percent-complete').textContent = `${percentComplete}%`;
            }
        });

        // Add export to CSV functionality
        if (document.getElementById('export-csv-btn')) {
            document.getElementById('export-csv-btn').addEventListener('click', function() {
                exportTableToCSV('team-members-report.csv');
            });
        }

        // Function to export table data to CSV file
        function exportTableToCSV(filename) {
            // Get the table
            const table = document.querySelector('#team-member-points-container table');
            if (!table) {
                showToast('No data available to export', 'error');
                return;
            }
            
            const rows = table.querySelectorAll('tr');
            let csv = [];
            
            // Process header row first
            const headerRow = rows[0];
            const headerCols = headerRow.querySelectorAll('th');
            let headerData = [];
            headerCols.forEach(col => {
                headerData.push('"' + col.innerText.trim().replace(/"/g, '""') + '"');
            });
            csv.push(headerData.join(','));
            
            // Process data rows
            for (let i = 1; i < rows.length; i++) {
                // Skip loading message row if present
                if (rows[i].querySelector('td[colspan]')) continue;
                
                const cols = rows[i].querySelectorAll('td');
                let rowData = [];
                
                cols.forEach(col => {
                    // For the member column, just get the name, not the entire HTML
                    if (col.querySelector('.font-medium')) {
                        rowData.push('"' + col.querySelector('.font-medium').innerText.trim().replace(/"/g, '""') + '"');
                    } else {
                        rowData.push('"' + col.innerText.trim().replace(/"/g, '""') + '"');
                    }
                });
                
                csv.push(rowData.join(','));
            }
            
            // Create and download CSV file
            const csvContent = csv.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            
            // Create download link
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', filename);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            showToast('Report exported successfully', 'success');
        }
        
        // Function to show a toast notification
        function showToast(message, type = 'info') {
            // Remove any existing toasts
            const existingToasts = document.querySelectorAll('.toast-notification');
            existingToasts.forEach(toast => toast.remove());
            
            // Create toast container if it doesn't exist
            let toastContainer = document.getElementById('toast-container');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.id = 'toast-container';
                toastContainer.className = 'fixed bottom-4 right-4 z-50';
                document.body.appendChild(toastContainer);
            }
            
            // Create toast element
            const toast = document.createElement('div');
            toast.className = 'toast-notification flex items-center p-4 mb-4 rounded shadow-lg max-w-md transition-opacity';
            
            // Set color based on type
            if (type === 'success') {
                toast.classList.add('bg-green-100', 'text-green-700', 'border-l-4', 'border-green-500');
            } else if (type === 'error') {
                toast.classList.add('bg-red-100', 'text-red-700', 'border-l-4', 'border-red-500');
            } else {
                toast.classList.add('bg-blue-100', 'text-blue-700', 'border-l-4', 'border-blue-500');
            }
            
            // Set toast content
            toast.innerHTML = `
                <div class="flex-shrink-0 mr-3">
                    ${type === 'success' 
                        ? '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>'
                        : type === 'error'
                            ? '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>'
                            : '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>'
                    }
                </div>
                <div>${message}</div>
                <button class="ml-auto text-gray-500 hover:text-gray-800" onclick="this.parentElement.remove()">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            `;
            
            // Add to container
            toastContainer.appendChild(toast);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.classList.add('opacity-0');
                    setTimeout(() => toast.remove(), 300);
                }
            }, 5000);
        }

        // Function to fetch data when board ID changes
        function fetchBoardData() {
            // Hide any previous results
            document.getElementById('error-container').classList.add('hidden');
            document.getElementById('main-data-container').classList.add('hidden');
            document.getElementById('cards-by-list-container').classList.add('hidden');
            
            // Show loading indicators
            document.getElementById('main-loading').classList.remove('hidden');
            
            // Get role from the data attribute
            const role = document.getElementById('role').dataset.role;
            
            // Make API request
            fetch('/trello/data?board_id=' + boardSelector.value)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to fetch board data');
                    }
                    return response.json();
                })
                .then(data => {
                    // Hide loading indicators
                    document.getElementById('main-loading').classList.add('hidden');
                    
                    // Render the data
                    renderBoardData(data);
                    
                    // Show the container
                    document.getElementById('main-data-container').classList.remove('hidden');
                    
                    // Fetch bug cards for all users, not just admins
                    fetchBugCards();
                })
                .catch(error => {
                    document.getElementById('main-loading').classList.add('hidden');
                    showErrorMessage(error.message);
                });
        }
        
        // Function to fetch bug cards data
        function fetchBugCards() {
            // Show loading indicator
            document.getElementById('cards-loading').classList.remove('hidden');
            document.getElementById('cards-by-list-container').classList.remove('hidden');
            
            // Make API request
            fetch('/trello/bug-cards?board_id=' + boardSelector.value)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to fetch bug cards');
                    }
                    return response.json();
                })
                .then(data => {
                    // Hide loading indicator
                    document.getElementById('cards-loading').classList.add('hidden');
                    
                    // Render the cards
                    renderCardsByList(data.listsData);
                })
                .catch(error => {
                    document.getElementById('cards-loading').classList.add('hidden');
                    showErrorMessage(error.message);
                });
        }
        
        // Initial fetch if a board is selected
        if (boardSelector.value) {
            fetchBoardData();
        }
        
        // Add event listener for board selection change
        boardSelector.addEventListener('change', fetchBoardData);

        // Function to fetch sprint information
        function fetchSprintInfo() {
            fetch('/trello/sprint-info')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to fetch sprint information');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Sprint info data:', data);
                    updateSprintInfo(data);
                })
                .catch(error => {
                    console.error('Error fetching sprint info:', error.message);
                    // Still show the sprint UI but with default values
                    document.getElementById('current-sprint-number').textContent = '-';
                    document.getElementById('sprint-date-range').textContent = 'Not available';
                    document.getElementById('sprint-duration').textContent = '-';
                    document.getElementById('sprint-day-number').textContent = '-';
                    document.getElementById('sprint-total-days').textContent = '-';
                    document.getElementById('sprint-days-remaining').textContent = '0 days';
                });
        }
        
        // Function to update sprint information in the UI
        function updateSprintInfo(data) {
            console.log('Updating sprint info with:', data);
            
            // Update sprint number - ensure it's displayed as an integer
            const sprintNumber = data.currentSprintNumber ? parseInt(data.currentSprintNumber) : '-';
            document.getElementById('current-sprint-number').textContent = sprintNumber;
            
            // Update all sprint number instances
            const sprintNumberBadges = document.querySelectorAll('.bg-primary-600.text-white.px-3.py-1.rounded-bl-md.text-xs.font-bold');
            sprintNumberBadges.forEach(badge => {
                badge.textContent = `Sprint ${sprintNumber}`;
            });
            
            // Update sprint date range
            document.getElementById('sprint-date-range').textContent = data.sprintDateRange || 'Not available';
            
            // Update sprint duration
            document.getElementById('sprint-duration').textContent = data.sprintDurationDisplay || '1 Week';
            
            // Update day counter
            document.getElementById('sprint-day-number').textContent = data.currentSprintDay || '-';
            document.getElementById('sprint-total-days').textContent = data.sprintTotalDays || '7';
            
            // Update days remaining
            const daysRemaining = data.daysRemaining || 0;
            const daysRemainingText = daysRemaining === 1 ? 'day' : 'days';
            document.getElementById('sprint-days-remaining').textContent = `${daysRemaining}`;
            
            // Update week number
            const weekNumberEl = document.getElementById('current-week-number');
            if (weekNumberEl) {
                weekNumberEl.textContent = data.currentWeekNumber || '-';
            }
            
            // Update next report date
            const nextReportDateEl = document.getElementById('next-report-date');
            if (nextReportDateEl) {
                nextReportDateEl.textContent = data.nextReportDate || 'Not available';
            }
            
            // Update progress bar width (using ID now)
            const progressBar = document.getElementById('sprint-progress-bar');
            if (progressBar) {
                progressBar.style.width = `${data.sprintProgressPercent || 0}%`;
            }
            
            // Update start and end dates
            const startDateEl = document.querySelector('.top-7.left-2.text-xs.font-medium');
            if (startDateEl) {
                const startPrefix = startDateEl.querySelector('span');
                startDateEl.innerHTML = `<span class="text-primary-700">Start:</span> ${data.currentSprintStartDate || '-'}`;
            }
            
            const endDateEl = document.querySelector('.top-7.right-2.text-xs.font-medium');
            if (endDateEl) {
                const endPrefix = endDateEl.querySelector('span');
                endDateEl.innerHTML = `<span class="text-primary-700">End:</span> ${data.currentSprintEndDate || '-'}`;
            }
            
            // Regenerate day markers based on updated total days
            const totalDays = data.sprintTotalDays || 7;
            const markerWidth = 100 / totalDays;
            
            // Find the timeline container
            const timelineContainer = document.querySelector('.relative.h-16.rounded-lg.overflow-hidden.bg-gray-100.shadow-inner');
            
            if (timelineContainer) {
                // Remove existing day markers
                timelineContainer.querySelectorAll('.border-r.border-gray-300, .bottom-1.text-xs.text-gray-500').forEach(el => {
                    el.remove();
                });
                
                // Add new day markers
                for (let i = 1; i <= totalDays; i++) {
                    // Add line marker
                    const lineMarker = document.createElement('div');
                    lineMarker.className = 'absolute top-0 h-2 border-r border-gray-300';
                    lineMarker.style.left = `${markerWidth * i}%`;
                    lineMarker.style.width = '1px';
                    timelineContainer.appendChild(lineMarker);
                    
                    // Add day number
                    const dayNumber = document.createElement('div');
                    dayNumber.className = 'absolute bottom-1 text-xs text-gray-500 font-medium';
                    dayNumber.style.left = `${markerWidth * (i - 0.5)}%`;
                    dayNumber.textContent = i;
                    timelineContainer.appendChild(dayNumber);
                }
                
                // Remove existing day marker if any
                const existingMarker = timelineContainer.querySelector('.w-0\\.5.h-full.bg-white');
                if (existingMarker && existingMarker.parentElement) {
                    existingMarker.parentElement.remove();
                }
                
                // Add new marker if within the sprint
                const sprintProgressPercent = data.sprintProgressPercent || 0;
                const daysElapsed = data.daysElapsed || 0;
                
                if (sprintProgressPercent > 0 && sprintProgressPercent < 100) {
                    const marker = document.createElement('div');
                    marker.className = 'absolute top-0 h-full';
                    marker.style.left = `${sprintProgressPercent}%`;
                    
                    marker.innerHTML = `
                        <div class="w-0.5 h-full bg-white shadow-md"></div>
                        <div class="absolute -top-1.5 -translate-x-1/2 w-5 h-5 rounded-full bg-white shadow-md border-2 border-primary-600 flex items-center justify-center">
                            <span class="text-primary-700 text-xs font-bold">${daysElapsed}</span>
                        </div>
                    `;
                    
                    timelineContainer.appendChild(marker);
                }
            }
        }

        // Function to render the board data
        function renderBoardData(data) {
            if (data.error) {
                showErrorMessage(data.error);
                return;
            }

            // Update board details
            updateBoardDetails(data.boardDetails);

            // Update summary statistics
            updateSummaryData(data.storyPoints);

            // Rebuild member table
            if (data.memberPoints && Array.isArray(data.memberPoints)) {
                buildMemberTable(data.memberPoints);
            } else {
                showNoMembersMessage();
            }
            
            // Fetch sprint information
            fetchSprintInfo();
        }

        // Add event listener for Save Report button
        const saveReportBtn = document.getElementById('save-report-btn');
        if (saveReportBtn) {
            saveReportBtn.addEventListener('click', function() {
                const boardSelector = document.getElementById('board-selector');
                if (!boardSelector || !boardSelector.value) {
                    showToast('Please select a board first', 'error');
                    return;
                }
                
                const boardId = boardSelector.value;
                const boardName = boardSelector.options[boardSelector.selectedIndex].text;
                
                // Capture the current report data
                const storyPointsData = document.getElementById('story-points-summary');
                const bugCardsContainer = document.getElementById('cards-by-list-container');
                
                // Create structured data for storage
                const reportData = {
                    board_id: boardId,
                    board_name: boardName
                };
                
                if (storyPointsData) {
                    const storyPointsJson = JSON.stringify({
                        summary: {
                            planPoints: parseInt(document.getElementById('plan-points')?.value || '0'),
                            actualPoints: parseInt(document.getElementById('actual-points')?.textContent || '0'),
                            remainPercent: document.getElementById('remain-percent')?.textContent || '0%',
                            percentComplete: document.getElementById('percent-complete')?.textContent || '0%',
                            currentSprintPoints: parseInt(document.getElementById('current-sprint-points')?.textContent || '0'),
                            actualCurrentSprint: parseInt(document.getElementById('actual-current-sprint')?.textContent || '0'),
                            boardName: document.getElementById('board-name-display')?.textContent || '',
                            lastUpdated: document.getElementById('last-updated')?.textContent || ''
                        },
                        teamMembers: Array.from(document.querySelectorAll('#team-members-table-body tr')).map(row => {
                            const cells = row.querySelectorAll('td');
                            if (cells.length >= 7) {
                                return {
                                    name: cells[0].textContent.trim(),
                                    pointPersonal: parseFloat(cells[1].textContent) || 0,
                                    pass: parseFloat(cells[2].textContent) || 0,
                                    bug: parseFloat(cells[3].textContent) || 0,
                                    cancel: parseFloat(cells[4].textContent) || 0,
                                    final: parseFloat(cells[5].textContent) || 0,
                                    passPercent: cells[6].textContent.trim()
                                };
                            }
                            return null;
                        }).filter(x => x !== null),
                        totals: {
                            totalPersonal: parseFloat(document.getElementById('total-personal')?.textContent || '0'),
                            totalPass: parseFloat(document.getElementById('total-pass')?.textContent || '0'),
                            totalBug: parseFloat(document.getElementById('total-bug')?.textContent || '0'),
                            totalCancel: parseFloat(document.getElementById('total-cancel')?.textContent || '0'),
                            totalFinal: parseFloat(document.getElementById('total-final')?.textContent || '0')
                        }
                    });
                    
                    reportData.story_points_data = storyPointsJson;
                }
                
                if (bugCardsContainer) {
                    const bugCardsJson = JSON.stringify({
                        bugCards: Array.from(document.querySelectorAll('.bug-card')).map(card => {
                            const nameElement = card.querySelector('.font-medium.text-gray-900');
                            const pointElement = card.querySelector('.bg-red-600.rounded-full');
                            const listElement = card.querySelector('.text-xs.text-gray-500');
                            const descriptionElement = card.querySelector('.description-content');
                            const memberElement = card.querySelector('.text-xs.text-gray-500.mt-1');
                            
                            return {
                                name: nameElement ? nameElement.textContent.trim() : 'Unnamed Card',
                                points: pointElement ? parseInt(pointElement.textContent) || 0 : 0,
                                list: listElement ? listElement.textContent.replace('From:', '').trim() : '',
                                description: descriptionElement ? descriptionElement.textContent.trim() : '',
                                members: memberElement ? memberElement.textContent.trim() : 'Not assigned',
                                priorityClass: Array.from(card.classList).find(c => c.startsWith('priority-')) || 'priority-none'
                            };
                        }),
                        bugCount: document.getElementById('bug-count')?.textContent || '0 bugs',
                        totalBugPoints: document.getElementById('total-bug-points')?.textContent || '0'
                    });
                    
                    reportData.bug_cards_data = bugCardsJson;
                }
                
                console.log('Saving report data to session:', {
                    hasStoryPoints: !!reportData.story_points_data,
                    hasBugCards: !!reportData.bug_cards_data
                });
                
                // Store board selection in session
                fetch('/save-board-selection', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(reportData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Redirect to create report form
                        window.location.href = '{{ route("saved-reports.create") }}';
                    } else {
                        showToast('Error: ' + (data.message || 'Failed to save board selection'), 'error');
                    }
                })
                .catch(error => {
                    console.error('Error saving board selection:', error);
                    showToast('An error occurred while saving the board selection', 'error');
                });
            });
        }
    });
</script>
@endsection
