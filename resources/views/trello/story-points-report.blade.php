@extends('layouts.app')

@section('title', 'Story Points Report')

@section('page-title', 'Story Points Report')

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        // Add base URL for API endpoints
        const apiBaseUrl = '{{ url('/') }}';
    </script>
    <link rel="stylesheet" href="{{ asset('css/print-report.css') }}" media="print">
    <style>
        @media print {
            body {
                background-color: white;
                color: black;
                font-size: 12pt;
            }

            button,
            .no-print,
            #fetch-data-btn {
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

            table,
            th,
            td {
                border: 1px solid #ddd;
            }

            th,
            td {
                padding: 8px;
                text-align: left;
            }

            .print-only {
                display: block !important;
            }

            .no-print {
                display: none !important;
            }
        }

        /* Hide scrollbar but keep functionality */
        .hide-scrollbar {
            -ms-overflow-style: none;
            /* IE and Edge */
            scrollbar-width: none;
            /* Firefox */
        }

        .hide-scrollbar::-webkit-scrollbar {
            display: none;
            /* Chrome, Safari and Opera */
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
            to {
                transform: rotate(360deg);
            }
        }
    </style>

    <!-- Edit Backlog Task Modal -->
    <div id="edit-backlog-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50" style="display: none;">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Edit Backlog Task</h3>
                <form id="edit-backlog-form" class="space-y-4">
                    <input type="hidden" id="edit-bug-id">
                    <div>
                        <label for="edit-bug-name" class="block text-sm font-medium text-gray-700">Bug Name</label>
                        <input type="text" id="edit-bug-name" name="name" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label for="edit-bug-description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea id="edit-bug-description" name="description" rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500"></textarea>
                    </div>
                    <div>
                        <label for="edit-bug-points" class="block text-sm font-medium text-gray-700">Story Points</label>
                        <input type="number" id="edit-bug-points" name="points" min="0"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div class="mt-4 flex justify-end space-x-2">
                        <button type="button" id="cancel-edit-backlog" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                            Cancel
                        </button>
                        <button type="button" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600" id="deleteBugBtn">
                            Delete
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto">
        @if($error)
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <p>{{ $error }}</p>
            </div>
        @endif

        @if(count($boards) === 0)
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6" role="alert">
                <p>You don't have access to any Trello boards. Please contact your administrator if you believe this is an error.</p>
            </div>
        @else
        <div class="flex justify-between items-start mb-6">
            <div>
                <div class="flex items-center space-x-4 mb-2">
                    <div class="w-12 h-12 rounded-full bg-primary-100 flex justify-center items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    @php
                        // Get the current sprint number
                        $sprintNumber = null;
                        $currentSprint = \App\Models\Sprint::getCurrentSprint();
                        if ($currentSprint) {
                            $sprintNumber = $currentSprint->sprint_number;
                        } else {
                            // Fallback to next sprint number if no current sprint
                            $sprintNumber = \App\Models\Sprint::getNextSprintNumber();
                        }
                    @endphp
                    <div>
                        <div class="flex items-center">
                            <h1 class="text-2xl font-bold">Sprint: {{ $sprintNumber }}</h1>
                            <span
                                class="ml-3 px-3 py-1 text-xs font-medium bg-primary-100 text-primary-800 rounded-full">Report</span>
                        </div>
                        <h2 class="text-xl text-gray-600">Current Sprint Report</h2>
                    </div>
                </div>
            </div>

            <!-- Action Menu -->
            <div class="flex space-x-2 items-center">
                <!-- Board/Team Selector -->
                @if (auth()->user()->isAdmin() || !$singleBoard)
                    <div class="mr-2 flex items-center">
                        <label for="board-selector" class="text-sm font-medium text-gray-700 mr-2">Team:</label>
                        @if (count($boards) > 0)
                            <select id="board-selector"
                                class="rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                                @foreach ($boards as $board)
                                    <option value="{{ $board['id'] }}"
                                        {{ $board['id'] == $defaultBoardId ? 'selected' : '' }}>
                                        {{ $board['name'] }}
                                    </option>
                                @endforeach
                            </select>
                        @else
                            <div class="text-sm px-3 py-2 bg-yellow-50 text-yellow-700 rounded-lg">
                                No Trello boards available. Please configure your Trello API settings first.
                            </div>
                        @endif
                    </div>
                @elseif($singleBoard)
                    <div class="mr-2 flex items-center">
                        <label class="text-sm font-medium text-gray-700 mr-2">Team:</label>
                        <div class="text-sm font-medium">{{ $boards[0]['name'] }}</div>
                        <!-- Hidden board selector with default selection -->
                        <input type="hidden" id="board-selector" value="{{ $boards[0]['id'] }}">
                    </div>
                @endif

                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open"
                        class="bg-white border border-gray-300 rounded-md px-4 py-2 flex items-center text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h7" />
                        </svg>
                        Menu
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="open" @click.away="open = false"
                        class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100">
                        <div class="py-1">
                        @if(auth()->user()->isAdmin())
                            <!-- Save Report Button with Sprint Auto-Save Hint -->
                            <button id="create-new-report-btn"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Create New Report
                                @endif
                            </button>
                            <!-- <button id="refresh-report-btn"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Refresh Now -->
                            </button>
                            <button id="print-report-btn"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                Print Report
                            </button>
                            <!-- <button id="export-csv-btn"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Export to CSV
                            </button> -->
                        </div>
                        @if(auth()->user()->isAdmin())
                        <div class="py-1">
                            {{-- <a href="{{ route('saved-reports.index') }}"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                </svg>
                                Saved Reports
                            </a> --}}
                            @endif
                            <a href="{{ route('trello.teams.index') }}"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                View Teams
                            </a>
                             @if(auth()->user()->isAdmin())
                            <a href="{{ route('trello.settings.index') }}"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Trello Settings
                            </a>
                            @endif
                        </div>
                    </div>
                </div>

                <button id="fetch-data-btn"
                    class="bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Refresh Now
                </button>
            </div>
        </div>

        @if (isset($error))
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

        <div id="loading-indicator" class="hidden">
            <div class="flex justify-center items-center bg-white shadow rounded-lg p-6 mb-6">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-primary-500" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                <span>Loading data from Trello...</span>
            </div>
        </div>

        <div id="main-loading" class="hidden">
            <div class="flex justify-center items-center bg-white shadow rounded-lg p-6 mb-6">
                <div class="spinner mr-3"></div>
                <span>Loading data from Trello...</span>
            </div>
        </div>

        <div id="main-data-container" class="hidden">
            <div id="story-points-summary" class="hidden">
                <div class="bg-white shadow rounded-3xl p-6 mb-6">
                    <h2 class="text-lg font-semibold mb-4 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-primary-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                     </svg>
                     Sprint Statistics
                 </h2>

                    <!-- Date Display -->
                    <div id="sprint-date-range" class=" text-sm text-gray-500 flex items-center">

                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Report Date: {{ $currentDate ?? Carbon\Carbon::now()->format('F d, Y') }}
                    </div>

                    <!-- Last Updated Display -->
                    <div id="last-updated" class="mb-3 hidden"></div>

                    <!-- Sprint Indicator -->
                    <div class="mb-4 bg-gray-50 p-3 rounded-lg">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center">
                                <span
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-primary-100 text-primary-800 mr-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </span>
                                <div>
                                    <h3 class="font-medium text-gray-800">
                                        Current Sprint: <span id="current-sprint-number"
                                            class="text-primary-600">{{ $currentSprintNumber ?? '-' }}</span>
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        <span
                                            id="sprint-date-range">{{ $sprintDateRange ?? 'Loading sprint data...' }}</span>
                                        (<span id="sprint-duration">{{ $sprintDurationDisplay ?? '-' }}</span>)
                                    </p>
                                    <p class="text-sm text-gray-500 mt-1">
                                        <strong>Next Sprint Report:</strong> <span
                                            id="next-report-date">{{ $nextReportDate ?? 'Not available' }}</span>
                                    </p>
                                </div>
                            </div>
                            <div class="text-right text-sm">
                                <div class="text-gray-600 font-medium">Day <span
                                        id="sprint-day-number">{{ $currentSprintDay ?? '-' }}</span> of <span
                                        id="sprint-total-days">{{ $sprintTotalDays ?? '-' }}</span></div>
                                <div class="text-gray-500">
                                    <span id="sprint-days-remaining">{{ $daysRemaining ?? '-' }}</span>
                                    {{ ($daysRemaining ?? 0) == 1 ? 'day' : 'days' }} remaining
                                </div>
                                <div class="text-gray-500 mt-1 text-xs">
                                    Week <span id="current-week-number">{{ $currentWeekNumber ?? '-' }}</span> of the year
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sprint Timeline Visualization -->
                    <div class="mt-4">
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Sprint Timeline</h3>
                        <div class="relative h-16 rounded-lg overflow-hidden bg-gray-100 shadow-inner">
                            <!-- Progress Background with Gradient -->
                            <div id="sprint-progress-bar"
                                class="absolute top-0 left-0 h-full bg-gradient-to-r from-primary-400 to-primary-600"
                                style="width: {{ $sprintProgressPercent ?? 0 }}%"></div>

                            <!-- Day Markers - generate markers for each day -->
                            @php
                                $totalDays = $sprintTotalDays ?? 7;
                                $markerWidth = 100 / $totalDays;
                            @endphp

                            @for ($i = 1; $i <= $totalDays; $i++)
                                <div class="absolute top-0 h-2 border-r border-gray-300"
                                    style="left: {{ $markerWidth * $i }}%; width: 1px;"></div>
                                <div class="absolute bottom-1 text-xs text-gray-500 font-medium"
                                    style="left: {{ $markerWidth * ($i - 0.5) }}%">{{ $i }}</div>
                            @endfor

                            <!-- Current Day Marker (vertical line and circle) -->
                            @if (($sprintProgressPercent ?? 0) > 0 && ($sprintProgressPercent ?? 0) < 100)
                                <div class="absolute top-0 h-full" style="left: {{ $sprintProgressPercent ?? 0 }}%">
                                    <div class="w-0.5 h-full bg-white shadow-md"></div>
                                    <div
                                        class="absolute -top-1.5 -translate-x-1/2 w-5 h-5 rounded-full bg-white shadow-md border-2 border-primary-600 flex items-center justify-center">
                                        <span class="text-primary-700 text-xs font-bold">{{ $daysElapsed ?? 0 }}</span>
                                    </div>
                                </div>
                            @endif

                            <!-- Sprint Number Badge -->
                            <div
                                class="absolute top-0 right-0 bg-primary-600 text-white px-3 py-1 rounded-bl-md text-xs font-bold shadow-sm">
                                Sprint {{ $currentSprintNumber ?? '-' }}
                            </div>

                            <!-- Start Date -->
                            <div
                                class="absolute top-7 left-2 text-xs font-medium text-gray-700 bg-white/70 backdrop-blur-sm px-1 py-0.5 rounded">
                                <span class="text-primary-700">Start:</span> {{ $currentSprintStartDate ?? '-' }}
                            </div>

                            <!-- End Date -->
                            <div
                                class="absolute top-7 right-2 text-xs font-medium text-gray-700 bg-white/70 backdrop-blur-sm px-1 py-0.5 rounded">
                                <span class="text-primary-700">End:</span> {{ $currentSprintEndDate ?? '-' }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Board Name Display -->
                <div id="board-name-display" class="mb-4 hidden">
                    <div class="flex items-center text-sm text-gray-500 mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Board: <span id="board-name" class="font-medium"></span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                    <div class="bg-gray-50 rounded-lg p-4 text-center">
                        <div class="flex items-center justify-between mb-2">
                            <span
                                class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-200 text-gray-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </span>
                            <span
                                class="rounded-full bg-gray-200 text-xs px-2 py-1 text-gray-700 font-semibold">Target</span>
                        </div>
                        <h3 class="text-sm font-medium text-gray-500">Plan Point</h3>
                        <div class="relative">
                            <input type="number" id="plan-points"
                                class="text-2xl font-bold text-gray-800 bg-transparent w-full text-center py-1 border-b border-dashed border-gray-300 focus:outline-none focus:border-primary-500"
                                value="0" readonly>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-2">
                                <button id="edit-plan-points"
                                    class="text-gray-400 hover:text-gray-600 pointer-events-auto">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-4 text-center">
                        <div class="flex items-center justify-between mb-2">
                            <span
                                class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-200 text-blue-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </span>
                            <span
                                class="rounded-full bg-blue-200 text-xs px-2 py-1 text-blue-700 font-semibold">Done</span>
                        </div>
                        <h3 class="text-sm font-medium text-blue-500">Actual Point</h3>
                        <p id="actual-points" class="text-2xl font-bold text-blue-600">0</p>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4 text-center">
                        <div class="flex items-center justify-between mb-2">
                            <span
                                class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-200 text-green-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </span>
                            <span
                                class="rounded-full bg-green-200 text-xs px-2 py-1 text-green-700 font-semibold">Remaining</span>
                        </div>
                        <h3 class="text-sm font-medium text-green-500">Remain Percent</h3>
                        <p id="remain-percent" class="text-2xl font-bold text-green-600">0%</p>
                    </div>
                    <div class="bg-purple-50 rounded-lg p-4 text-center">
                        <div class="flex items-center justify-between mb-2">
                            <span
                                class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-purple-200 text-purple-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                                </svg>
                            </span>
                            <span
                                class="rounded-full bg-purple-200 text-xs px-2 py-1 text-purple-700 font-semibold">Progress</span>
                        </div>
                        <h3 class="text-sm font-medium text-purple-500">Percent</h3>
                        <p id="percent-complete" class="text-2xl font-bold text-purple-600">0%</p>
                    </div>
                    <div class="bg-yellow-50 rounded-lg p-4 text-center">
                        <div class="flex items-center justify-between mb-2">
                            <span
                                class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-yellow-200 text-yellow-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </span>
                            <span
                                class="rounded-full bg-yellow-200 text-xs px-2 py-1 text-yellow-700 font-semibold">Current</span>
                        </div>
                        <h3 class="text-sm font-medium text-yellow-500">Point Current Sprint</h3>
                        <p id="current-sprint-points" class="text-2xl font-bold text-yellow-600">0</p>
                    </div>
                    <div class="bg-red-50 rounded-lg p-4 text-center">
                        <div class="flex items-center justify-between mb-2">
                            <span
                                class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-200 text-red-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </span>
                            <span
                                class="rounded-full bg-red-200 text-xs px-2 py-1 text-red-700 font-semibold">Actual</span>
                        </div>
                        <h3 class="text-sm font-medium text-red-500">Actual Point Current Sprint</h3>
                        <p id="actual-current-sprint" class="text-2xl font-bold text-red-600">0</p>
                    </div>
                </div>
            </div>

            <!-- Team Member Points Table -->
            <div id="team-member-points-container" class="bg-white shadow rounded-3xl p-8 mb-8">
                <div class="flex items-center mb-6 rounded-full">
                    <!-- <svg xmlns="http://www.w3.org/2000/svg" width="30" height="31" fill="currentColor" class="bi bi-bar-chart-line mr-2 text-sky-400" viewBox="0 0 16 16">
                        <path d="M11 2a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v12h.5a.5.5 0 0 1 0 1H.5a.5.5 0 0 1 0-1H1v-3a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3h1V7a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v7h1zm1 12h2V2h-2zm-3 0V7H7v7zm-5 0v-3H2v3z"/>
                    </svg> -->
                    <h2 class="text-xl font-semibold">
                        <span id="points-title" class="rounded-full text-sky-400 bg-sky-100 px-3 py-2">Points from Current Sprint</span>
                        <span id="board-name-display"
                            class="ml-3 text-xs bg-blue-100 text-blue-800 px-3 py-2 rounded-full hidden">
                            Loading board name...
                        </span>
                        <span id="last-updated" class="text-xs text-gray-500 block mt-2 hidden">
                            Last updated: ...
                        </span>
                    </h2>
                </div>
                <div class="overflow-x-auto rounded-xl">
                    <table class="min-w-full bg-white border-gray-200">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="py-3 px-4 border-b text-left">Member</th>
                                <th class="py-3 px-4 border-b text-center">Point Personal</th>
                                <th class="py-3 px-4 border-b text-center">Pass</th>
                                <th class="py-3 px-4 border-b text-center">Bug</th>
                                <th class="py-3 px-4 border-b text-center">Cancel</th>
                                <th class="py-3 px-4 border-b text-center cursor-pointer hover:bg-blue-50"
                                    title="Click to add extra points">Extra</th>
                                <th class="py-3 px-4 border-b text-center">Final</th>
                                <th class="py-3 px-4 border-b text-center">Pass %</th>
                            </tr>
                        </thead>
                        <tbody id="team-members-table-body">
                            <tr>
                                <td colspan="8" class="py-4 px-4 text-center text-gray-500">Loading team data...</td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-gray-50 font-semibold">
                            <tr>
                                <td class="py-3 px-4 border-t text-left">Totals</td>
                                <td id="total-personal" class="py-3 px-4 border-t text-center">0</td>
                                <td id="total-pass" class="py-3 px-4 border-t text-center">0</td>
                                <td id="total-bug" class="py-3 px-4 border-t text-center">0</td>
                                <td id="total-cancel" class="py-3 px-4 border-t text-center">0</td>
                                <td id="total-extra" class="py-3 px-4 border-t text-center">0</td>
                                <td id="total-final" class="py-3 px-4 border-t text-center">0</td>
                                <td class="py-3 px-4 border-t text-center">-</td>
                            </tr>
                        </tfoot>
                    </table>
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
            <!-- Changed from Cards by List to Current Bug -->
            <div class="mt-8">
                <div class="bg-white rounded-3xl shadow-md overflow-hidden">
                    <div class="px-6 py-5 flex justify-between items-center">
                        <div class="flex items-center ">
                            <svg xmlns="http://www.w3.org/2000/svg" width="30" height="31" fill="currentColor" class="bi bi-bug mr-2 text-red-500" viewBox="0 0 16 16">
                                <path d="M4.355.522a.5.5 0 0 1 .623.333l.291.956A5 5 0 0 1 8 1c1.007 0 1.946.298 2.731.811l.29-.956a.5.5 0 1 1 .957.29l-.41 1.352A5 5 0 0 1 13 6h.5a.5.5 0 0 0 .5-.5V5a.5.5 0 0 1 1 0v.5A1.5 1.5 0 0 1 13.5 7H13v1h1.5a.5.5 0 0 1 0 1H13v1h.5a1.5 1.5 0 0 1 1.5 1.5v.5a.5.5 0 1 1-1 0v-.5a.5.5 0 0 0-.5-.5H13a5 5 0 0 1-10 0h-.5a.5.5 0 0 0-.5.5v.5a.5.5 0 1 1-1 0v-.5A1.5 1.5 0 0 1 2.5 10H3V9H1.5a.5.5 0 0 1 0-1H3V7h-.5A1.5 1.5 0 0 1 1 5.5V5a.5.5 0 0 1 1 0v.5a.5.5 0 0 0 .5.5H3c0-1.364.547-2.601 1.432-3.503l-.41-1.352a.5.5 0 0 1 .333-.623M4 7v4a4 4 0 0 0 3.5 3.97V7zm4.5 0v7.97A4 4 0 0 0 12 11V7zM12 6a4 4 0 0 0-1.334-2.982A3.98 3.98 0 0 0 8 2a3.98 3.98 0 0 0-2.667 1.018A4 4 0 0 0 4 6z"/>
                            </svg>
                            <h2 class="rounded-full text-xl text-red-500 bg-red-100 px-2 py-1 font-bold">Current Bug
                                <span id="bug-count"
                                    class="text-sm font-normal text-gray-500">0 bugs
                                </span>
                            </h2>
                        </div>
                        <div class="text-sm text-gray-500 font-medium rounded-full text-red-500 bg-red-100 px-2 py-1">
                            Total Points
                            <span id="total-bug-points" class="font-semibold">0</span>
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

            <!-- Extra Points Modal -->
            <div id="extra-points-modal"
                class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full"
                style="z-index: 1000;">
                <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                    <div class="mt-3">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Add Extra Points</h3>
                        <div class="mt-4">
                            <input type="hidden" id="extra-points-member-id">
                            <input type="hidden" id="extra-points-row-index">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Points</label>
                                <input type="number" id="extra-points-input"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    step="0.5" min="0">
                            </div>
                        </div>
                        <div class="mt-5 flex justify-end space-x-2">
                            <button id="cancel-extra-points"
                                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-300">
                                Cancel
                            </button>
                            <button id="save-extra-points"
                                class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500">
                                Save
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End of Extra Points Modal -->
            <!-- Minor Case Section -->
        <div class="mt-8">
            <div class="bg-white shadow rounded-3xl">
                <div class="px-6 py-5 border-gray-200 flex justify-between items-center">
                    <div class = "flex items-center">
                       <svg xmlns="http://www.w3.org/2000/svg" width="30" height="31" fill="currentColor " class="bi bi-send text-violet-400 " viewBox="0 0 16 16">
                    <path d="M15.854.146a.5.5 0 0 1 .11.54l-5.819 14.547a.75.75 0 0 1-1.329.124l-3.178-4.995L.643 7.184a.75.75 0 0 1 .124-1.33L15.314.037a.5.5 0 0 1 .54.11ZM6.636 10.07l2.761 4.338L14.13 2.576zm6.787-8.201L1.591 6.602l4.339 2.76z"/>
                </svg>
                    <h2 class="ml-2 rounded-full text-xl text-violet-400 bg-violet-100 px-2 py-1 font-bold">Minor Cases <span id="minor-case-count"
                        class="text-sm font-normal text-gray-500">0 cases</span></h2>
                    </div>

                <div class="flex items-center space-x-2">
                    <div class="text-sm text-gray-500 font-medium rounded-full text-violet-500 bg-violet-100 px-2 py-1">
                        Total Points <span id="total-minor-points" class="font-medium ml-1">0</span>
                    </div>
                    <button id="add-minor-case-btn"
                        class="px-3 py-1 bg-emerald-500 text-white rounded-full hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 text-sm flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Add Case
                    </button>
                </div>
            </div>
            <div class="overflow-x-auto ">
                <table class="min-w-full">
                    <thead>
                        <tr class="text-gray-700">
                            <th class="py-3 px-4 text-left">Sprint</th>
                            <th class="py-3 px-4 text-left">Card Detail</th>
                            <th class="py-3 px-4 text-left">Description</th>
                            <th class="py-3 px-4 text-left">Member</th>
                            <th class="py-3 px-4 text-center">Personal Point</th>
                            <th class="py-3 px-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="minor-cases-table-body">
                        <tr id="no-minor-cases-row">
                            <td colspan="6" class="py-4 px-4 text-center text-gray-500">No minor cases found. Click
                                "Add
                                Case" to add one.</td>
                        </tr>
                    </tbody>
                    <tfoot class="font-semibold">
                        <tr>
                            <td colspan="4" class="py-3 px-4 text-right">Total Points:</td>
                            <td id="minor-case-total-points" class="py-3 px-4 text-center">0</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <!-- Minor Case Add/Edit Modal -->
            <div id="minor-case-modal"
                class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full"
                style="z-index: 1000;">
                <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white max-w-md">
                    <div class="mt-3">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="minor-case-modal-title">Add Minor Case
                        </h3>
                        <form id="minor-case-form" class="mt-4">
                            <input type="hidden" id="minor-case-id">
                            <div class="mb-4">
                                <label for="minor-case-sprint"
                                    class="block text-sm font-medium text-gray-700">Sprint</label>
                                <input type="text" id="minor-case-sprint"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    required>
                            </div>
                            <div class="mb-4">
                                <label for="minor-case-card" class="block text-sm font-medium text-gray-700">Card
                                    Detail</label>
                                <input type="text" id="minor-case-card"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    required>
                            </div>
                            <div class="mb-4">
                                <label for="minor-case-description"
                                    class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea id="minor-case-description" rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"></textarea>
                            </div>
                            <div class="mb-4">
                                <label for="minor-case-member"
                                    class="block text-sm font-medium text-gray-700">Member</label>
                                <select id="minor-case-member"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    required>
                                    <option value="">Select a member</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label for="minor-case-points" class="block text-sm font-medium text-gray-700">Personal
                                    Points</label>
                                <input type="number" id="minor-case-points"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    step="0.5" min="0" required>
                            </div>
                            <div class="mt-5 flex justify-end space-x-2">
                                <button type="button" id="cancel-minor-case"
                                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-300">
                                    Cancel
                                </button>
                                <button type="submit" id="save-minor-case"
                                    class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    Save
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Backlog Section -->
            @if ((isset($backlogData) && isset($backlogData['allBugs']) && count($backlogData['allBugs']) > 0))
                <div class="mt-8">
                    <div class="bg-gray-50 shadow rounded-lg p-6 mb-8 border-l-4 border-amber-500">
                        <h2 class="text-xl font-semibold mb-2 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-amber-500 mr-2" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="rounded-full text-xl text-amber-500 bg-amber-100 px-2 py-1 font-bold">
                                Backlog (<span id="backlog-title-count">{{ $backlogData['bugCount'] }}</span>)
                            </span>

                        </h2>
                        <p class="text-sm text-gray-600 mb-4">These bugs were carried over from previous sprints.</p>

                        <div class="mt-4">
                            <div class="overflow-x-auto">
                                <div id="backlog-cards-container"
                                    class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-2">
                                    @foreach ($backlogData['allBugs'] as $bug)
                                        <div class="backlog-bug-card bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow"
                                            data-team="{{ $bug['team'] ?? 'Unknown' }}">
                                            <!-- Card Header with Bug ID and Priority -->
                                            <div class="flex justify-between items-center p-4 border-b border-gray-100">
                                                <div class="flex items-center">
                                                    <!-- Points in circle -->
                                                    <div
                                                        class="w-8 h-8 rounded-full bg-amber-100 text-amber-500 flex items-center justify-center font-bold text-sm mr-3">
                                                        {{ $bug['points'] ?? '-' }}
                                                    </div>
                                                    <!-- Bug ID and Name -->
                                                    <div>
                                                        <a href="{{ $bug['url'] ?? '#' }}"
                                                            class="text-gray-900 font-medium hover:text-primary-600"
                                                            target="_blank">{{ $bug['name'] }}</a>
                                                    </div>
                                                </div>
                                                <!-- Sprint badge -->
                                                <div>
                                                    @if (isset($bug['sprint_origin']))
                                                        <span
                                                            class="px-3 py-1 text-xs rounded-full bg-gray-100 text-gray-600">
                                                            Sprint {{ $bug['sprint_origin'] }}
                                                        </span>
                                                    @else
                                                        <span
                                                            class="px-3 py-1 text-xs rounded-full bg-gray-100 text-gray-600">
                                                            Sprint {{ $bug['sprint_number'] ?? '?' }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Bug Name/Description/button -->
                                            <div class="p-4 grid grid-cols-9">
                                                <div class="col-span-8 text-left h-20 overflow-auto">
                                                    {!! nl2br(e($bug['description'] ?? 'No description available')) !!}
                                                </div>
                                                <div class="col-start-9 flex flex-col space-y-2">
                                                <button type="button"
                                                        class="edit-backlog-task text-[#985E00] bg-[#FFC7B2] hover:bg-[#FFA954] focus:outline-none font-medium rounded-full px-2 py-2 text-center h-8 w-8"
                                                        data-bug-id="{{ $bug['id'] ?? '' }}"
                                                        data-bug-name="{{ $bug['name'] ?? '' }}"
                                                        data-bug-description="{{ $bug['description'] ?? '' }}"
                                                        data-bug-points="{{ $bug['points'] ?? 0 }}">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                        fill="currentColor" class="bi bi-pencil-square"
                                                        viewBox="0 0 16 16">
                                                        <path
                                                            d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z" />
                                                        <path fill-rule="evenodd"
                                                            d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z" />
                                                    </svg>
                                                </button>
                                                </div>
                                            </div>

                                            <!-- Card Footer with Assignment -->
                                            <div class="bg-gray-50 px-4 py-3 flex justify-between items-center">
                                                <div class="flex items-center">
                                                    <span class="text-sm text-gray-500">Assign to:</span>

                                                    <span
                                                        class="ml-2 px-2 py-1 text-xs font-medium rounded-full bg-[#BAF3FF] text-[#13A7FD]">
                                                        {{ $bug['team'] ?? 'Unknown' }}
                                                    </span>

                                                    <span class="ml-2 px-2 py-1 text-xs font-medium rounded-full">
                                                        {{ $bug['assigned'] ?? 'Unassigned' }}
                                                    </span>
                                                </div>

                                                <!-- Status indicator -->
                                                <div>
                                                    <span
                                                        class="px-2 py-1 text-xs font-medium rounded-full bg-[#DDFFEC] text-[#82DF3C]">
                                                        {{ $bug['status'] ?? 'Open' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- No bugs message - hidden by default -->
                            <div id="no-bugs-message"
                                class="hidden bg-yellow-50 text-yellow-700 p-4 rounded-lg text-center">
                                No backlog bugs found for the selected team.
                            </div>

                            <div class="mt-4 text-right">
                                <a href="{{ route('backlog.index') }}"
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    View Full Backlog
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
        <script>
            // Define showToast function before it's used
            window.showToast = function(message, type = 'info') {
                // Simple alert as a fallback
                alert(message);
            };

            document.addEventListener('DOMContentLoaded', function() {
                // Get backlog elements
                const backlogCards = document.querySelectorAll('.backlog-bug-card');
                const backlogTitleCount = document.getElementById('backlog-title-count');
                const noBugsMessage = document.getElementById('no-bugs-message');
                const backlogCardsContainer = document.getElementById('backlog-cards-container');

                // Get board selector
                const boardSelector = document.getElementById('board-selector');

                // Initial filtering based on the selected board
                if (boardSelector) {
                    const selectedOption = boardSelector.options[boardSelector.selectedIndex];
                    if (selectedOption) {
                        const selectedBoardName = selectedOption.text;
                        filterBacklogCards(selectedBoardName);
                    }

                    // Add listener to the board selector for automatic filtering
                    boardSelector.addEventListener('change', function() {
                        const selectedOption = this.options[this.selectedIndex];
                        if (selectedOption) {
                            const selectedBoardName = selectedOption.text;
                            filterBacklogCards(selectedBoardName);
                        }
                    });
                }

                // Function to filter backlog cards based on team
                function filterBacklogCards(team) {
                    let visibleCount = 0;

                    backlogCards.forEach(card => {
                        const cardTeam = card.getAttribute('data-team');

                        if (!team || team === '' || cardTeam === team) {
                            card.style.display = '';
                            visibleCount++;
                        } else {
                            card.style.display = 'none';
                        }
                    });

                    // Update the count in the title
                    backlogTitleCount.textContent = visibleCount;

                    // Show/hide no bugs message
                    if (visibleCount === 0) {
                        noBugsMessage.classList.remove('hidden');
                        backlogCardsContainer.classList.add('hidden');
                    } else {
                        noBugsMessage.classList.add('hidden');
                        backlogCardsContainer.classList.remove('hidden');
                    }
                }
            });
        </script>
        @endif

        <!-- Loading Indicator -->
        <div id="loading-indicator" class="hidden">
            <div class="flex justify-center items-center bg-white shadow rounded-lg p-6 mb-6">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-primary-500" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                <span>Loading data from Trello...</span>
            </div>
        </div>
    </div>
    </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Minor case functionality
            const addMinorCaseBtn = document.getElementById('add-minor-case-btn');
            const minorCaseModal = document.getElementById('minor-case-modal');
            const minorCaseForm = document.getElementById('minor-case-form');
            const cancelMinorCaseBtn = document.getElementById('cancel-minor-case');
            const minorCasesTableBody = document.getElementById('minor-cases-table-body');
            const noMinorCasesRow = document.getElementById('no-minor-cases-row');
            const minorCaseCountSpan = document.getElementById('minor-case-count');
            const totalMinorPointsSpan = document.getElementById('total-minor-points');
            const minorCaseTotalPoints = document.getElementById('minor-case-total-points');

            // Get the current board ID
            const getCurrentBoardId = () => {
                const boardSelector = document.getElementById('board-selector');
                return boardSelector ? boardSelector.value : '';
            };

            // Function to load minor cases from server
            async function loadMinorCasesFromServer() {
                try {
                    // Show loading indicator
                    minorCasesTableBody.innerHTML = `
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <div class="inline-block animate-spin rounded-full h-6 w-6 border-t-2 border-b-2 border-blue-500 mr-2"></div>
                                Loading minor cases...
                            </td>
                        </tr>
                    `;

                const boardId = getCurrentBoardId();
                    if (!boardId) {
                        console.warn('No board ID available');
                        renderMinorCasesTable([]);
                        return;
                    }

                    const response = await fetch(`${apiBaseUrl}/minor-cases/api?board_id=${encodeURIComponent(boardId)}`, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    });

                    if (!response.ok) {
                        const errorText = await response.text();
                        console.error('Server response:', errorText);
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        const text = await response.text();
                        console.error('Unexpected response type:', contentType, 'Response:', text);
                        throw new Error('Server did not return JSON');
                    }

                    const minorCases = await response.json();
                    console.log('Loaded minor cases:', minorCases);

                    renderMinorCasesTable(minorCases.data || []);
                } catch (error) {
                    console.error('Error loading minor cases:', error);
                    alert('Error loading minor cases: ' + error.message);
                    renderMinorCasesTable([]); // Render empty table on error
                }
            }

            // Save minor case
            minorCaseForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                const id = document.getElementById('minor-case-id').value;
                const sprint = document.getElementById('minor-case-sprint').value;
                const card = document.getElementById('minor-case-card').value;
                const description = document.getElementById('minor-case-description').value;
                const member = document.getElementById('minor-case-member').value;
                const points = document.getElementById('minor-case-points').value;
                const boardId = getCurrentBoardId();

                if (!boardId) {
                    alert('Error: No board selected');
                    return;
                }

                try {
                    let response;
                    const data = {
                        board_id: boardId,
                        sprint,
                        card,
                        description,
                        member,
                        points: parseFloat(points)
                    };

                    const headers = {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    };

                    if (id) {
                        // Edit existing case
                        response = await fetch(`${apiBaseUrl}/minor-cases/api/${id}`, {
                            method: 'PUT',
                            headers,
                            body: JSON.stringify(data),
                            credentials: 'same-origin'
                        });
                    } else {
                        // Add new case
                        response = await fetch(`${apiBaseUrl}/minor-cases/api`, {
                            method: 'POST',
                            headers,
                            body: JSON.stringify(data),
                            credentials: 'same-origin'
                        });
                    }

                    if (!response.ok) {
                        const errorText = await response.text();
                        console.error('Server response:', errorText);

                        // Try to parse error response as JSON
                        try {
                            const errorJson = JSON.parse(errorText);
                            if (errorJson.errors) {
                                throw new Error(Object.values(errorJson.errors).flat().join('\n'));
                            }
                        } catch (e) {
                            // If parsing fails, throw the original error
                            throw new Error(`Server error: ${response.status}`);
                        }
                    }

                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        const text = await response.text();
                        console.error('Unexpected response type:', contentType, 'Response:', text);
                        throw new Error('Server did not return JSON');
                    }

                    const responseData = await response.json();

                    // Refresh the minor cases list
                    await loadMinorCasesFromServer();

                    // Close the modal and reset form
                    minorCaseModal.classList.add('hidden');
                    minorCaseForm.reset();

                    // Show success message
                    alert(id ? 'Minor case updated successfully' : 'Minor case created successfully');
                } catch (error) {
                    console.error('Error saving minor case:', error);
                    alert('Error saving minor case: ' + error.message);
                }
            });

            // Modify the renderMinorCasesTable function to handle empty data
            const renderMinorCasesTable = (minorCases) => {
                // Ensure minorCases is an array
                minorCases = Array.isArray(minorCases) ? minorCases : [];

                // Update count and total points
                minorCaseCountSpan.textContent = `${minorCases.length} cases`;

                let totalPoints = 0;
                minorCases.forEach(caseItem => {
                    totalPoints += parseFloat(caseItem.points) || 0;
                });

                if (totalMinorPointsSpan) {
                totalMinorPointsSpan.textContent = totalPoints.toFixed(1);
                }
                if (minorCaseTotalPoints) {
                minorCaseTotalPoints.textContent = totalPoints.toFixed(1);
                }

                // Clear table except for the "no cases" row
                const rows = minorCasesTableBody.querySelectorAll('tr:not(#no-minor-cases-row)');
                rows.forEach(row => row.remove());

                // Show/hide "no cases" row
                if (minorCases.length === 0) {
                    if (noMinorCasesRow) {
                    noMinorCasesRow.style.display = '';
                } else {
                        // Create the "no cases" row if it doesn't exist
                        const noDataRow = document.createElement('tr');
                        noDataRow.id = 'no-minor-cases-row';
                        noDataRow.innerHTML = `
                            <td colspan="6" class="py-4 px-4 text-center text-gray-500">
                                No minor cases found
                            </td>
                        `;
                        minorCasesTableBody.appendChild(noDataRow);
                    }
                } else {
                    if (noMinorCasesRow) {
                    noMinorCasesRow.style.display = 'none';
                    }

                    // Add each case to the table
                    minorCases.forEach((caseItem, index) => {
                        const row = document.createElement('tr');
                        row.className = 'hover:bg-gray-50';
                        row.dataset.id = caseItem.id;

                        row.innerHTML = `
                            <td class="py-3 px-4 border-b">${caseItem.sprint || ''}</td>
                            <td class="py-3 px-4 border-b">${caseItem.card || ''}</td>
                            <td class="py-3 px-4 border-b">
                                <div class="max-h-20 overflow-y-auto">
                                    ${caseItem.description || 'No description'}
                                </div>
                            </td>
                            <td class="py-3 px-4 border-b">${caseItem.member || ''}</td>
                            <td class="py-3 px-4 border-b text-center">${parseFloat(caseItem.points || 0).toFixed(1)}</td>
                            <td class="py-3 px-4 border-b text-center">
                                <div class="flex justify-center space-x-2">
                                    <button class="edit-minor-case text-blue-500 hover:text-blue-700" data-id="${caseItem.id}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button class="delete-minor-case text-red-500 hover:text-red-700" data-id="${caseItem.id}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        `;

                        minorCasesTableBody.appendChild(row);
                    });
                }
            };

            // Function to populate member dropdown based on team members
            function populateMemberDropdown() {
                const memberSelect = document.getElementById('minor-case-member');
                const teamMembersData = window.teamMembersData || [];

                console.log('Populating member dropdown with data:', teamMembersData);

                // Clear existing options except the first one
                while (memberSelect.options.length > 1) {
                    memberSelect.remove(1);
                }

                // If no members data, try to get it from another source
                if (!teamMembersData.length) {
                    // Try to get members from the member table
                    const memberTable = document.getElementById('team-members-table-body');
                    if (memberTable) {
                        const memberRows = memberTable.querySelectorAll('tr');
                        console.log('Trying to get members from table, found rows:', memberRows.length);

                        memberRows.forEach(row => {
                            const memberNameCell = row.querySelector('td:first-child');
                            if (memberNameCell) {
                                const memberNameDiv = memberNameCell.querySelector('.font-medium');
                                if (memberNameDiv) {
                                    const memberName = memberNameDiv.textContent.trim();
                                    if (memberName && memberName !== 'Unknown') {
                                        const option = document.createElement('option');
                                        option.value = memberName;
                                        option.textContent = memberName;
                                        memberSelect.appendChild(option);
                                        console.log('Added member from table:', memberName);
                                    }
                                }
                            }
                        });
                    }
                } else {
                    // Add team members to dropdown from the global data
                    teamMembersData.forEach(member => {
                        if (member.fullName && member.fullName !==
                            'Unknown') { // Use fullName instead of name
                            const option = document.createElement('option');
                            option.value = member.fullName;
                            option.textContent = member.fullName;
                            memberSelect.appendChild(option);
                            console.log('Added member from global data:', member.fullName);
                        }
                    });
                }

                // Sort options alphabetically (excluding the first "Select a member" option)
                const options = Array.from(memberSelect.options).slice(1);
                options.sort((a, b) => a.text.localeCompare(b.text));

                // Remove all options except the first one
                while (memberSelect.options.length > 1) {
                    memberSelect.remove(1);
                }

                // Add sorted options back
                options.forEach(option => memberSelect.add(option));

                console.log('Final dropdown options count:', memberSelect.options.length);
            }

            // Open modal to add new minor case
            addMinorCaseBtn.addEventListener('click', function() {
                // Reset form
                document.getElementById('minor-case-modal-title').textContent = 'Add Minor Case';
                document.getElementById('minor-case-id').value = '';
                minorCaseForm.reset();

                // Auto-fill sprint with current sprint number
                const currentSprintEl = document.getElementById('current-sprint-number');
                if (currentSprintEl) {
                    document.getElementById('minor-case-sprint').value = currentSprintEl.textContent;
                }

                // Populate member dropdown
                populateMemberDropdown();

                minorCaseModal.classList.remove('hidden');
            });

            // Close modal
            cancelMinorCaseBtn.addEventListener('click', function() {
                minorCaseModal.classList.add('hidden');
            });

            // Edit and delete event handlers (using event delegation)
            minorCasesTableBody.addEventListener('click', function(e) {
                const editBtn = e.target.closest('.edit-minor-case');
                const deleteBtn = e.target.closest('.delete-minor-case');

                if (editBtn) {
                    const id = editBtn.dataset.id;
                    // Get the row that contains the data
                    const row = editBtn.closest('tr');

                    if (row) {
                        document.getElementById('minor-case-modal-title').textContent = 'Edit Minor Case';
                        document.getElementById('minor-case-id').value = id;
                        document.getElementById('minor-case-sprint').value = row.cells[0].textContent;
                        document.getElementById('minor-case-card').value = row.cells[1].textContent;
                        document.getElementById('minor-case-description').value = row.cells[2].querySelector('div').textContent.trim() === 'No description' ? '' : row.cells[2].querySelector('div').textContent.trim();

                        // Populate member dropdown before setting the value
                        populateMemberDropdown();

                        const memberSelect = document.getElementById('minor-case-member');
                        const memberValue = row.cells[3].textContent;

                        // If the member doesn't exist in the dropdown, add it
                        let memberExists = false;
                        for (let i = 0; i < memberSelect.options.length; i++) {
                            if (memberSelect.options[i].value === memberValue) {
                                memberExists = true;
                                break;
                            }
                        }

                        if (!memberExists && memberValue) {
                            const option = document.createElement('option');
                            option.value = memberValue;
                            option.textContent = memberValue;
                            memberSelect.appendChild(option);
                        }

                        memberSelect.value = memberValue;
                        document.getElementById('minor-case-points').value = parseFloat(row.cells[4].textContent);

                        minorCaseModal.classList.remove('hidden');
                    }
                } else if (deleteBtn) {
                    // Deletion is handled by the click event listener on minorCasesTableBody
                    return;
                }
            });

            // Listen for board selector changes to reload minor cases
            const boardSelector = document.getElementById('board-selector');
            if (boardSelector) {
                boardSelector.addEventListener('change', loadMinorCasesFromServer);
            }

            // Initial render - load data from server instead of just rendering an empty table
            loadMinorCasesFromServer();

            // Add delete functionality
            minorCasesTableBody.addEventListener('click', async function(e) {
                const deleteBtn = e.target.closest('.delete-minor-case');
                if (deleteBtn) {
                    const id = deleteBtn.dataset.id;
                    if (confirm('Are you sure you want to delete this minor case?')) {
                        try {
                            const response = await fetch(`${apiBaseUrl}/minor-cases/api/${id}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                credentials: 'same-origin'
                            });

                            if (!response.ok) {
                                const errorText = await response.text();
                                console.error('Server response:', errorText);
                                throw new Error(`Server error: ${response.status}`);
                            }

                            // Try to parse as JSON, but don't fail if it's not JSON
                            try {
                                const contentType = response.headers.get('content-type');
                                if (contentType && contentType.includes('application/json')) {
                                    await response.json();
                                }
                            } catch (e) {
                                console.warn('Response was not JSON, but deletion may have succeeded');
                            }

                            // Refresh the minor cases list
                            await loadMinorCasesFromServer();

                            // Show success message
                            alert('Minor case deleted successfully');
                        } catch (error) {
                            console.error('Error deleting minor case:', error);
                            alert('Error deleting minor case: ' + error.message);
                        }
                    }
                }
            });
        });
    </script>
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

            // Backlog data and pagination
            let allBacklogBugs = @json($backlogData['allBugs'] ?? []);
            let filteredBacklogBugs = [];
            let currentPage = 1;
            const itemsPerPage = 20;
            let totalPages = 1;

            // Initialize last fetched indicator
            const lastUpdatedEl = document.getElementById('last-updated');

            // Set the current board ID from the selector's value
            if (boardSelector && boardSelector.value) {
                currentBoardId = boardSelector.value;
            }

            // Initialize backlog table if it exists
            const backlogTableBody = document.getElementById('backlog-table-body');
            if (backlogTableBody) {
                filterBacklogByCurrentTeam();
                renderBacklogTable();
            }

            // Initialize data from cached content if available
            @if (isset($boardData))
                // Populate data from cached content
                const cachedData = @json($boardData);

                // Store the cached data globally so it can be updated
                window.cachedData = cachedData;

                // Update board details
                if (cachedData.boardDetails) {
                    updateBoardDetails(cachedData.boardDetails);
                }

                // Update last fetched time
                updateLastFetched(true, cachedData.lastFetched);

                // Update summary statistics
                if (cachedData.storyPoints) {
                    updateSummaryData(cachedData.storyPoints);
                }

                // Update member data
                if (cachedData.memberPoints && Array.isArray(cachedData.memberPoints)) {
                    buildMemberTable(cachedData.memberPoints);
                }

                // Update cards data
                if (cachedData.cardsByList) {
                    renderCardsByList(cachedData.cardsByList);
                }

                // Show containers
                storyPointsSummary.classList.remove('hidden');
                cardsByListContainer.classList.remove('hidden');
            @endif

            // When the board selector changes, auto-fetch data
            boardSelector.addEventListener('change', function() {
                // Update current board ID
                currentBoardId = this.value;

                // Reset UI elements
                document.getElementById('points-title').textContent = 'Points from Current Sprint';
                document.getElementById('board-name-display').classList.add('hidden');
                document.getElementById('last-updated').classList.add('hidden');

                // Update backlog table with filtered data for the selected team
                if (backlogTableBody) {
                    filterBacklogByCurrentTeam();
                    currentPage = 1;
                    renderBacklogTable();
                }

                // Automatically fetch data when selection changes
                if (currentBoardId) {
                    fetchDataWithoutForceRefresh();
                }
            });

            // Function to filter backlog bugs by the currently selected team
            function filterBacklogByCurrentTeam() {
                if (!allBacklogBugs || allBacklogBugs.length === 0) return;

                // Get the selected board's name
                let selectedBoardName = '';
                const selectedOption = boardSelector.options[boardSelector.selectedIndex];
                if (selectedOption) {
                    selectedBoardName = selectedOption.text;
                }

                // Filter bugs to only show those from the selected team
                if (selectedBoardName) {
                    filteredBacklogBugs = Object.values(allBacklogBugs).filter(bug => bug.team ===
                        selectedBoardName);
                } else {
                    filteredBacklogBugs = Object.values(allBacklogBugs);
                }

                // Update counts and totals
                const totalBugs = filteredBacklogBugs.length;
                const totalPoints = filteredBacklogBugs.reduce((sum, bug) => sum + (parseInt(bug.points) || 0), 0);

                // Update UI elements
                document.getElementById('filtered-bug-count').textContent = totalBugs;
                document.getElementById('filtered-bug-points').textContent = totalPoints;
                document.getElementById('total-items').textContent = totalBugs;

                // Update the count in the heading
                updateBacklogCount(totalBugs);

                // Calculate total pages
                totalPages = Math.ceil(totalBugs / itemsPerPage);

                // Setup pagination numbers
                setupPagination();
            }

            // Function to update backlog count in title
            function updateBacklogCount(count) {
                const backlogTitleCount = document.getElementById('backlog-title-count');
                if (backlogTitleCount) {
                    backlogTitleCount.textContent = count;
                }
            }

            // Function to render the backlog table with paginated data
            function renderBacklogTable() {
                if (!backlogTableBody) return;

                // Clear current table
                backlogTableBody.innerHTML = '';

                // Calculate start and end indices for current page
                const startIndex = (currentPage - 1) * itemsPerPage;
                const endIndex = Math.min(startIndex + itemsPerPage, filteredBacklogBugs.length);

                // Update pagination info
                document.getElementById('page-start').textContent = filteredBacklogBugs.length > 0 ? startIndex +
                    1 : 0;
                document.getElementById('page-end').textContent = endIndex;

                // If no bugs to display
                if (filteredBacklogBugs.length === 0) {
                    const emptyRow = document.createElement('tr');
                    emptyRow.innerHTML = `
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                        No backlog bugs found for the selected team.
                    </td>
                `;
                    backlogTableBody.appendChild(emptyRow);
                    return;
                }

                // Render visible bugs for current page
                for (let i = startIndex; i < endIndex; i++) {
                    const bug = filteredBacklogBugs[i];

                    const row = document.createElement('tr');
                    row.className = 'hover:bg-amber-50';

                    // Format priority labels
                    let priorityLabels = '';
                    if (bug.labels && Array.isArray(bug.labels)) {
                        bug.labels.forEach(label => {
                            if (label !== 'Backlog') {
                                let bgColorClass = 'bg-green-100 text-green-800';
                                if (label === 'High') {
                                    bgColorClass = 'bg-red-100 text-red-800';
                                } else if (label === 'Medium') {
                                    bgColorClass = 'bg-yellow-100 text-yellow-800';
                                }
                                priorityLabels +=
                                    `<span class="px-2 py-1 text-xs font-medium rounded-full ${bgColorClass}">${label}</span> `;
                            }
                        });
                    }

                    // Format sprint badge
                    let sprintBadge = '';
                    if (bug.sprint_origin) {
                        sprintBadge = `
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                            Sprint ${bug.sprint_origin}
                        </span>
                    `;
                    } else {
                        sprintBadge = `
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">
                            Sprint ${bug.sprint_number || '?'}
                        </span>
                    `;
                    }

                    row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <a href="${bug.url || '#'}" class="text-primary-600 hover:text-primary-900" target="_blank">${bug.id}</a>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        ${bug.name}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        ${priorityLabels || '<span class="text-gray-400">-</span>'}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        ${bug.assigned || 'Unassigned'}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        ${bug.points || '-'}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        ${bug.team || '-'}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        ${sprintBadge}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <button class="edit-backlog-task px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800 hover:bg-blue-200"
                            data-bug-id="${bug.id ?? ''}"
                            data-bug-name="${bug.name ?? ''}"
                            data-bug-description="${bug.description ?? ''}"
                            data-bug-points="${bug.points ?? 0}">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                        <button class="delete-backlog-task px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800 hover:bg-red-200"
                            data-bug-id="${bug.id ?? ''}"
                            data-bug-name="${bug.name ?? ''}">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                `;

                    backlogTableBody.appendChild(row);
                }
            }

            // Function to setup pagination
            function setupPagination() {
                // Update pagination numbers
                const paginationNumbers = document.getElementById('pagination-numbers');
                if (paginationNumbers) {
                    paginationNumbers.innerHTML = `Page ${currentPage} of ${totalPages}`;
                }

                // Setup event listeners for pagination buttons
                const prevPage = document.getElementById('prev-page');
                const nextPage = document.getElementById('next-page');
                const prevPageMobile = document.getElementById('prev-page-mobile');
                const nextPageMobile = document.getElementById('next-page-mobile');

                if (prevPage) {
                    prevPage.disabled = currentPage === 1;
                    prevPage.classList.toggle('opacity-50', currentPage === 1);
                    prevPage.addEventListener('click', function() {
                        if (currentPage > 1) {
                            currentPage--;
                            renderBacklogTable();
                            setupPagination();
                        }
                    });
                }

                if (nextPage) {
                    nextPage.disabled = currentPage === totalPages;
                    nextPage.classList.toggle('opacity-50', currentPage === totalPages);
                    nextPage.addEventListener('click', function() {
                        if (currentPage < totalPages) {
                            currentPage++;
                            renderBacklogTable();
                            setupPagination();
                        }
                    });
                }

                // Mobile pagination
                if (prevPageMobile) {
                    prevPageMobile.disabled = currentPage === 1;
                    prevPageMobile.classList.toggle('opacity-50', currentPage === 1);
                    prevPageMobile.addEventListener('click', function() {
                        if (currentPage > 1) {
                            currentPage--;
                            renderBacklogTable();
                            setupPagination();
                        }
                    });
                }

                if (nextPageMobile) {
                    nextPageMobile.disabled = currentPage === totalPages;
                    nextPageMobile.classList.toggle('opacity-50', currentPage === totalPages);
                    nextPageMobile.addEventListener('click', function() {
                        if (currentPage < totalPages) {
                            currentPage++;
                            renderBacklogTable();
                            setupPagination();
                        }
                    });
                }
            }

            // Function to fetch data with force refresh
            function fetchDataWithForceRefresh() {
                fetchDataWithParam('&force_refresh=true');
            }

            // Function to fetch data without force refresh
            function fetchDataWithoutForceRefresh() {
                fetchDataWithParam('');
            }

            // Function to fetch data with custom parameter
            function fetchDataWithParam(customParam) {
                // Store the original force refresh parameter
                const originalButton = fetchDataBtn.cloneNode(true);

                // Perform the fetch with the custom parameter
                fetchDataBtn.setAttribute('data-custom-param', customParam);
                fetchDataBtn.click();

                // Restore the original button
                fetchDataBtn.removeAttribute('data-custom-param');
            }

            // Add event listener to refresh button in dropdown
            if (refreshReportBtn) {
                refreshReportBtn.addEventListener('click', function() {
                    fetchDataWithForceRefresh();
                });
            }

            // Add print functionality to the print button
            if (printReportBtn) {
                printReportBtn.addEventListener('click', function() {
                    // Create a form to submit the data directly to our export endpoint
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route("export.to.csv") }}';
                    form.target = '_blank'; // Open in new window/tab

                    // Add autoprint parameter
                    const autoPrintInput = document.createElement('input');
                    autoPrintInput.type = 'hidden';
                    autoPrintInput.name = 'autoprint';
                    autoPrintInput.value = 'true';
                    form.appendChild(autoPrintInput);

                    // Add CSRF token
                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';
                    form.appendChild(csrfToken);

                    // Add board name
                    const boardNameInput = document.createElement('input');
                    boardNameInput.type = 'hidden';
                    boardNameInput.name = 'board_name';
                    boardNameInput.value = boardSelector.options[boardSelector.selectedIndex].text;
                    form.appendChild(boardNameInput);

                    // Add sprint info
                    const sprintInput = document.createElement('input');
                    sprintInput.type = 'hidden';
                    sprintInput.name = 'sprint';
                    sprintInput.value = document.getElementById('points-title').textContent || 'Current Sprint';
                    form.appendChild(sprintInput);

                    // Add story points data from cached data
                    if (window.cachedData && window.cachedData.storyPoints) {
                        // Get plan points from input field
                        const planPointsValue = parseFloat(document.getElementById('plan-points').value) || 0;

                        // Create a copy of storyPoints with the current plan points value
                        const storyPointsData = {
                            ...window.cachedData.storyPoints,
                            planPoints: planPointsValue
                        };

                        const storyPointsInput = document.createElement('input');
                        storyPointsInput.type = 'hidden';
                        storyPointsInput.name = 'story_points_data';
                        storyPointsInput.value = JSON.stringify(storyPointsData);
                        form.appendChild(storyPointsInput);
                    }

                    // Add bug cards data from cached data
                    if (window.cachedData && window.cachedData.bugCards) {
                        const bugCardsInput = document.createElement('input');
                        bugCardsInput.type = 'hidden';
                        bugCardsInput.name = 'bug_cards_data';
                        bugCardsInput.value = JSON.stringify(window.cachedData.bugCards);
                        form.appendChild(bugCardsInput);
                    }

                    // Add member points data if available
                    if (window.cachedData && window.cachedData.memberPoints) {
                        // Log the member points data for debugging
                        console.log('Member points data before print:', window.cachedData.memberPoints);

                        // Process and enhance member points data to ensure it contains valid extraPoint values
                        const processedMemberPoints = window.cachedData.memberPoints.map(member => {
                            // Check if there's a saved extra point in localStorage
                            let extraPoint = 0;
                            if (member.id) {
                                const storageKey = `extraPoints_${currentBoardId}_${member.id}`;
                                const savedExtraPoint = localStorage.getItem(storageKey);
                                if (savedExtraPoint !== null) {
                                    extraPoint = parseFloat(savedExtraPoint) || 0;
                                } else if (member.extraPoint) {
                                    extraPoint = parseFloat(member.extraPoint) || 0;
                                }
                            }

                            // Create a new object with the updated extraPoint
                            return {
                                ...member,
                                extraPoint: extraPoint
                            };
                        });

                        // Add the processed member points to the form
                        const memberPointsInput = document.createElement('input');
                        memberPointsInput.type = 'hidden';
                        memberPointsInput.name = 'member_points_data';
                        memberPointsInput.value = JSON.stringify(processedMemberPoints);
                        form.appendChild(memberPointsInput);

                        // Extract extra points data for each member
                        const extraPointsData = [];
                        processedMemberPoints.forEach(member => {
                            if (member.extraPoint && parseFloat(member.extraPoint) > 0) {
                                extraPointsData.push({
                                    extra_personal: member.fullName || member.username || 'Unknown',
                                    extra_point: parseFloat(member.extraPoint) || 0
                                });
                            }
                        });

                        console.log('Extracted extra points data:', extraPointsData);

                        // Add extra points data
                        if (extraPointsData.length > 0) {
                            const extraPointsInput = document.createElement('input');
                            extraPointsInput.type = 'hidden';
                            extraPointsInput.name = 'extra_points_data';
                            extraPointsInput.value = JSON.stringify(extraPointsData);
                            form.appendChild(extraPointsInput);
                        }
                    }

                    // Append form to body, submit it, and remove it
                    document.body.appendChild(form);
                    form.submit();
                    document.body.removeChild(form);
                });
            }

            // Function to show a save dialog
            function showSaveDialog(callback) {
                // Create a modal dialog
                const dialog = document.createElement('div');
                dialog.className = 'fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50';
                dialog.id = 'save-export-dialog';

                dialog.innerHTML = `
                    <div class="bg-white rounded-lg shadow-xl p-6 w-96 max-w-full">
                        <h3 class="text-lg font-semibold mb-4">Save Report For Export</h3>
                        <p class="text-sm text-gray-600 mb-4">Enter a name for this report to save it before exporting.</p>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Report Name</label>
                            <input type="text" id="export-report-name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="My Sprint Report">
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button id="cancel-export" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md">Cancel</button>
                            <button id="confirm-export" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-md">Save & Export</button>
                        </div>
                    </div>
                `;

                document.body.appendChild(dialog);

                // Focus the input field
                document.getElementById('export-report-name').focus();

                // Handle cancel
                document.getElementById('cancel-export').addEventListener('click', function() {
                    dialog.remove();
                });

                // Handle confirm
                document.getElementById('confirm-export').addEventListener('click', function() {
                    const reportName = document.getElementById('export-report-name').value.trim();
                    if (reportName) {
                        dialog.remove();
                        callback(reportName);
                    } else {
                        // Show error if no name provided
                        alert('Please enter a name for the report');
                    }
                });

                // Handle enter key
                document.getElementById('export-report-name').addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        const reportName = this.value.trim();
                        if (reportName) {
                            dialog.remove();
                            callback(reportName);
                        } else {
                            alert('Please enter a name for the report');
                        }
                    }
                });
            }

            // Function to save the report and export to template
            function saveReportAndExport(reportName) {
                // Get all the necessary data
                const boardId = boardSelector.value;
                const boardName = boardSelector.options[boardSelector.selectedIndex].text;

                // Check if we have data to save
                if (!window.cachedData) {
                    showToast('No data available to export', 'error');
                    return;
                }

                // Create a loading indicator
                const loadingOverlay = document.createElement('div');
                loadingOverlay.className = 'fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-30';
                loadingOverlay.innerHTML = `
                    <div class="bg-white rounded-lg p-4">
                        <div class="spinner"></div>
                        <p class="mt-2 text-sm text-gray-600">Saving report...</p>
                    </div>
                `;
                document.body.appendChild(loadingOverlay);

                // Create a form data object
                const formData = new FormData();
                formData.append('report_name', reportName);
                formData.append('name', reportName);
                formData.append('board_id', boardId);
                formData.append('board_name', boardName);
                formData.append('notes', 'Generated for export');
                formData.append('story_points_data', JSON.stringify(window.cachedData.storyPoints || {}));
                formData.append('bug_cards_data', JSON.stringify(window.cachedData.cardsByList || {}));

                // Send the data to the server
                fetch('{{ route("report.save") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    // Remove loading overlay
                    loadingOverlay.remove();

                    if (data.success) {
                        // Redirect to the export template URL with the saved report ID
                        const savedReportId = data.savedReportId || data.id;
                        if (savedReportId) {
                            window.location.href = `{{ url('/saved-reports') }}/${savedReportId}/export-template`;
                        } else {
                            // If we can't get the ID, redirect to the saved reports page
                            window.location.href = '{{ route("saved-reports.index") }}';
                        }
                    } else {
                        showToast('Error saving report: ' + (data.error || 'Unknown error'), 'error');
                    }
                })
                .catch(error => {
                    loadingOverlay.remove();
                    console.error('Error saving report:', error);
                    showToast('Error saving report: ' + error.message, 'error');
                });
            }

            // Auto-fetch data on page load (for all users) only if no cached data is available
            if (currentBoardId) {
                @if (!isset($boardData))
                    // Use a slight delay to ensure the DOM is fully loaded
                    setTimeout(function() {
                        // Fetch data without forcing a refresh
                        fetchDataWithoutForceRefresh();
                    }, 100);
                @endif
            }

            // Add event listener to fetch data button
            if (fetchDataBtn) {
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

                // Check if we're using a custom parameter or the default Refresh Now button
                const customParam = this.getAttribute('data-custom-param');
                const forceRefreshParam = customParam !== null ? customParam : '&force_refresh=true';

                    fetch(`${apiBaseUrl}/trello/data?board_id=${boardId}&_nocache=${timestamp}-${randomStr}${forceRefreshParam}`, {
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

                        // Update the last updated time
                        updateLastFetched(data.cached, data.lastFetched);

                        // Update summary statistics
                        updateSummaryData(data.storyPoints);

                        // Update backlog data if available
                        if (data.backlogData) {
                            allBacklogBugs = data.backlogData.allBugs || [];

                            // Update backlog table
                            if (backlogTableBody) {
                                filterBacklogByCurrentTeam();
                                renderBacklogTable();
                            } else {
                                // If no backlog table exists but we have backlog data, update the count
                                updateBacklogCount(allBacklogBugs.length);
                            }
                        }

                        // Update member data
                        if (data.memberPoints && Array.isArray(data.memberPoints)) {
                            console.log('Member points data received:', data.memberPoints.length,
                                'members');

                            // Apply any saved extra points to the member data
                            data.memberPoints.forEach(member => {
                                if (currentBoardId && member.id) {
                                    const savedExtraPoint = parseFloat(localStorage.getItem(
                                            `extraPoints_${currentBoardId}_${member.id}`
                                            )) || 0;
                                    if (savedExtraPoint > 0) {
                                        member.extraPoint = savedExtraPoint;
                                            member.finalPoint = parseFloat(member.passPoint ||
                                                    0) +
                                            savedExtraPoint;
                                    }
                                }
                            });

                                // Store member data globally for the dropdown - log it first
                                console.log('Setting window.teamMembersData with:', data.memberPoints);
                                window.teamMembersData = data.memberPoints;

                            // Also update the global cached data
                            window.cachedData = data;

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
                        if (data.cached) {
                            showToast('Loaded cached data successfully', 'success');
                        } else {
                            showToast('Data refreshed successfully from Trello', 'success');
                        }
                    })
                    .catch(error => {
                        console.error('Fetch failed:', error);
                        loadingIndicator.classList.add('hidden');

                        showErrorMessage(error.message || 'Unknown error fetching data');

                        // Show error toast
                        showToast('Failed to load data: ' + error.message, 'error');
                    });
            });
            }

            // Function to update the last fetched time indication
            function updateLastFetched(cached, lastFetched) {
                const lastUpdatedEl = document.getElementById('last-updated');

                if (lastUpdatedEl) {
                    lastUpdatedEl.innerHTML = `
                    <div class="flex items-center text-xs text-gray-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 ${cached ? 'text-amber-500' : 'text-green-500'}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        ${cached ? 'Using cached data from ' : 'Data refreshed '} ${lastFetched}
                        ${cached ? '<span class="ml-2 text-xs text-primary-600 cursor-pointer underline" id="force-refresh">Refresh Now</span>' : ''}
                    </div>
                `;
                    lastUpdatedEl.classList.remove('hidden');

                    // Add event listener to force refresh link
                    const forceRefreshLink = document.getElementById('force-refresh');
                    if (forceRefreshLink) {
                        forceRefreshLink.addEventListener('click', function() {
                            fetchDataWithForceRefresh();
                        });
                    }
                }
            }

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
                        <th class="py-3 px-4 border-b text-center cursor-pointer hover:bg-blue-50" title="Click to add extra points">Extra</th>
                        <th class="py-3 px-4 border-b text-center">Final</th>
                        <th class="py-3 px-4 border-b text-center">Pass %</th>
                    </tr>
                </thead>
                <tbody id="team-members-table-body">
                    <tr>
                        <td colspan="8" class="py-4 px-4 text-center text-gray-500">Loading team data...</td>
                    </tr>
                </tbody>
                <tfoot class="bg-gray-50 font-semibold">
                    <tr>
                        <td class="py-3 px-4 border-t text-left">Totals</td>
                        <td id="total-personal" class="py-3 px-4 border-t text-center">0</td>
                        <td id="total-pass" class="py-3 px-4 border-t text-center">0</td>
                        <td id="total-bug" class="py-3 px-4 border-t text-center">0</td>
                        <td id="total-cancel" class="py-3 px-4 border-t text-center">0</td>
                        <td id="total-extra" class="py-3 px-4 border-t text-center">0</td>
                        <td id="total-final" class="py-3 px-4 border-t text-center">0</td>
                        <td class="py-3 px-4 border-t text-center">-</td>
                    </tr>
                </tfoot>
                `;

                // Replace the old table with the new one
                if (oldTable) {
                    oldTable.remove();
                }
                tableContainer.appendChild(newTable);

                // Reattach event listeners to the new table
                attachExtraPointsEventListeners();
            }

            // Function to attach extra points event listeners
            function attachExtraPointsEventListeners() {
                const tableBody = document.getElementById('team-members-table-body');
                if (tableBody) {
                    tableBody.addEventListener('click', function(e) {
                        const row = e.target.closest('tr');
                        if (!row) return;

                        const extraCell = row.querySelector('td:nth-child(6)'); // Extra points column
                        if (e.target === extraCell || extraCell.contains(e.target)) {
                            const memberId = row.dataset.memberId;
                            const rowIndex = Array.from(row.parentElement.children).indexOf(row);
                            const currentExtraPoints = parseFloat(extraCell.textContent) || 0;
                            openExtraPointsModal(memberId, rowIndex, currentExtraPoints);
                        }
                    });
                }
            }

            function updateBoardDetails(boardDetails) {
                if (!boardDetails) return;

                const pointsTitle = document.getElementById('points-title');
                const boardNameDisplay = document.getElementById('board-name-display');
                const lastUpdated = document.getElementById('last-updated');

                // Get current sprint number
                const currentSprintNumberElement = document.getElementById('current-sprint-number');
                const sprintNumber = currentSprintNumberElement ? currentSprintNumberElement.textContent : '';

                // Update the title with sprint number and board name
                if (boardDetails.name) {
                    pointsTitle.textContent = `Sprint ${sprintNumber} - ${boardDetails.name}`;
                    boardNameDisplay.textContent = boardDetails.name;
                    boardNameDisplay.classList.remove('hidden');
                }

                // Update last activity date
                if (boardDetails.dateLastActivity) {
                    lastUpdated.textContent =
                        `Last updated: ${new Date(boardDetails.dateLastActivity).toLocaleString()}`;
                    lastUpdated.classList.remove('hidden');
                }
            }

            function updateSummaryData(storyPoints) {
                if (!storyPoints) return;

                // ตรวจสอบว่ามีค่า plan point จากฐานข้อมูลหรือไม่
                const boardSelector = document.getElementById('board-selector');
                const currentBoardId = boardSelector ? boardSelector.value : '';

                if (currentBoardId) {
                    // ใช้ AJAX เพื่อโหลดค่า plan point จากฐานข้อมูล
                    fetch(`{{ url('/trello/get-plan-point') }}?board_id=${currentBoardId}`, {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.plan_point !== null && data.plan_point !== undefined) {
                            // ใช้ค่าจากฐานข้อมูล
                            console.log('Loaded plan point from database:', data.plan_point);
                            planPointsInput.value = data.plan_point;

                            // อัปเดต localStorage
                            localStorage.setItem(`planPoints_${currentBoardId}`, data.plan_point);
                            localStorage.setItem(`planPointEdited_${currentBoardId}`, 'true');

                            // อัปเดต cached data
                            if (window.cachedData && window.cachedData.storyPoints) {
                                window.cachedData.storyPoints.planPoints = parseFloat(data.plan_point);
                            }

                            // อัปเดตการคำนวณอื่นๆ
                            updateMetricsBasedOnPlanPoints();

                            // เพิ่มการเรียกใช้ updateTotals เพื่อให้แน่ใจว่ามีการคำนวณค่าทั้งหมด
                            if (typeof updateTotals === 'function') {
                                updateTotals();
                            }
                        } else {
                            // ถ้าไม่มีข้อมูลในฐานข้อมูล ให้ใช้ logic เดิม
                            fallbackToPreviousLogic();
                        }
                    })
                    .catch(error => {
                        console.error('Error loading plan point from database:', error);
                        // ถ้าเกิดข้อผิดพลาด ให้ใช้ logic เดิม
                        fallbackToPreviousLogic();
                    });
                } else {
                    // ถ้าไม่มี board ID ให้ใช้ logic เดิม
                    fallbackToPreviousLogic();
                }

                // ฟังก์ชันสำหรับใช้ logic เดิมเมื่อไม่มีข้อมูลจากฐานข้อมูล
                function fallbackToPreviousLogic() {
                // Check if we have a saved plan point value for this board
                const savedPlanPoints = localStorage.getItem(`planPoints_${currentBoardId}`);

                    // Check if this is the first data load or a refresh
                    const isFirstLoad = !localStorage.getItem(`dataLoaded_${currentBoardId}`);

                    // Mark that data has been loaded for this board
                    if (currentBoardId && isFirstLoad) {
                        localStorage.setItem(`dataLoaded_${currentBoardId}`, 'true');
                    }

                if (savedPlanPoints) {
                    // Use the saved value if it exists
                    planPointsInput.value = savedPlanPoints;
                } else {
                    // Calculate total personal points from team members
                    const totalPersonalPoints = document.getElementById('total-personal')?.textContent || "0";

                        // Always use team member points total as the default value for plan points (if available)
                    if (parseFloat(totalPersonalPoints) > 0) {
                        planPointsInput.value = totalPersonalPoints;

                            // Save this initial value if it's the first load
                            if (currentBoardId && isFirstLoad) {
                                localStorage.setItem(`planPoints_${currentBoardId}`, totalPersonalPoints);

                                // Also update cached data if available
                                if (window.cachedData && window.cachedData.storyPoints) {
                                    window.cachedData.storyPoints.planPoints = parseFloat(totalPersonalPoints);
                                    console.log('Set initial plan points to total personal points:', totalPersonalPoints);
                                }
                            }
                    } else {
                            // Only fall back to total points from API if we don't have team member data
                        planPointsInput.value = storyPoints.total || 0;

                    // Save this initial value
                            if (currentBoardId && isFirstLoad) {
                        localStorage.setItem(`planPoints_${currentBoardId}`, planPointsInput.value);
                            }
                        }
                    }

                    // อัปเดตการคำนวณอื่นๆ
                    updateMetricsBasedOnPlanPoints();

                    // เพิ่มการเรียกใช้ updateTotals เพื่อให้แน่ใจว่ามีการคำนวณค่าทั้งหมด
                    if (typeof updateTotals === 'function') {
                        updateTotals();
                    }
                }

                // ฟังก์ชันอัปเดตการคำนวณต่างๆ
                function updateMetricsBasedOnPlanPoints() {
                // Note: We'll update the Actual Point in buildMemberTable when we have the final point total
                // We're still initializing it here to ensure it's reset if needed
                document.getElementById('actual-points').textContent = '0';

                // Calculate values for other metrics based on plan points
                const planPoints = parseFloat(planPointsInput.value) || 0;

                // Other calculations will be updated once we have the actual point from team data
                document.getElementById('remain-percent').textContent = '0%';
                document.getElementById('percent-complete').textContent = '0%';
                }
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
                    extra: 0,
                    final: 0
                };

                // Build each row with fresh data
                members.forEach(member => {
                    // Check if we have saved extra points for this member
                    const savedExtraPoint = currentBoardId && member.id ?
                        parseFloat(localStorage.getItem(`extraPoints_${currentBoardId}_${member.id}`)) ||
                        0 : 0;

                    // Extract numeric values safely
                    const pointPersonal = parseFloat(member.pointPersonal || 0);
                    const passPoint = parseFloat(member.passPoint || 0);
                    const bugPoint = parseFloat(member.bugPoint || 0);
                    const cancelPoint = parseFloat(member.cancelPoint || 0);

                    // Use saved extra point value if available, otherwise use the one from the data
                    const extraPoint = savedExtraPoint || parseFloat(member.extraPoint || 0);

                    // Recalculate final point which should equal pass points only
                    const finalPoint = passPoint;

                    // Update running totals
                    totals.personal += pointPersonal;
                    totals.pass += passPoint;
                    totals.bug += bugPoint;
                    totals.cancel += cancelPoint;
                    totals.extra += extraPoint;
                    totals.final += finalPoint;

                    // Calculate pass percentage: (passPoint / pointPersonal) * 100
                    const passPercentage = pointPersonal > 0 ?
                        Math.round((passPoint / pointPersonal) * 100) :
                        0;

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
                    <td class="py-3 px-4 border-b text-center text-blue-600 cursor-pointer hover:bg-blue-50">${extraPoint.toFixed(1)}</td>
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
                document.getElementById('total-extra').textContent = totals.extra.toFixed(1);
                document.getElementById('total-final').textContent = totals.final.toFixed(1);

                // 5. Point Current Sprint (sum of member personal points)
                document.getElementById('current-sprint-points').textContent = totals.personal.toFixed(1);

                // 6. Actual Point Current Sprint (sum of final points + extra points)
                document.getElementById('actual-current-sprint').textContent = (totals.final + totals.extra).toFixed(1);

                // Check if we need to update plan points to match total personal points
                // ตรวจสอบว่าเราควรอัปเดต plan point จาก total personal หรือไม่
                const planPointsInput = document.getElementById('plan-points');
                const savedPlanPoints = localStorage.getItem(`planPoints_${currentBoardId}`);
                const isPlanPointManuallySet = localStorage.getItem(`planPointEdited_${currentBoardId}`);

                // ถ้ายังไม่มีการแก้ไขค่า plan point ด้วยตนเอง และมีการเปลี่ยนแปลงใน total personal
                if (!isPlanPointManuallySet && (!savedPlanPoints || parseFloat(totals.personal.toFixed(1)) !== parseFloat(savedPlanPoints))) {
                    // อัปเดตค่า plan point ให้เท่ากับ total personal
                    planPointsInput.value = totals.personal.toFixed(1);

                    // บันทึกค่าใหม่ลง localStorage
                    if (currentBoardId) {
                        localStorage.setItem(`planPoints_${currentBoardId}`, totals.personal.toFixed(1));

                        // อัปเดต cachedData ด้วย
                        if (window.cachedData && window.cachedData.storyPoints) {
                            window.cachedData.storyPoints.planPoints = totals.personal;
                            console.log('Updated plan points to match new total personal points:', totals.personal);
                        }
                    }
                }

                // Now update the Actual Point using the total final points
                // 2. Actual Point = Final Point (not +Extra Point)
                const actualPoints = totals.final; // Use totals.final instead of completedPoints
                document.getElementById('actual-points').textContent = actualPoints.toFixed(1);

                // Now recalculate remaining metrics based on the actual point from team data
                const planPoints = parseFloat(planPointsInput.value) || 0;

                // 3. Remain Percent = (Plan Point - Actual Point) / Plan Point * 100
                let remainPercent = 0;
                if (planPoints > 0) {
                    remainPercent = Math.round(((planPoints - actualPoints) / planPoints) * 100);
                }
                document.getElementById('remain-percent').textContent = `${remainPercent}%`;

                // 4. Percent Complete = (Actual Point / Plan Point) * 100
                let percentComplete = 0;
                if (planPoints > 0) {
                    percentComplete = Math.round((actualPoints / planPoints) * 100);
                }
                document.getElementById('percent-complete').textContent = `${percentComplete}%`;

                console.log('Team member table updated with', members.length, 'members');

                // เรียกใช้ updateTotals() เพื่อให้มั่นใจว่ามีการคำนวณค่าทั้งหมดเมื่อข้อมูลใหม่เข้ามา
                // นี่จะช่วยแก้ปัญหาการที่ค่า Actual Point, Remain Percent และ Percent Complete ไม่คำนวณหลังโหลดข้อมูล
                updateTotals();
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
                document.getElementById('total-extra').textContent = '0.0';
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
                document.getElementById('total-extra').textContent = '0';
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
                errorMessage.className =
                    'bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded api-error-message';
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
                document.getElementById('bug-count').textContent =
                    `${bugCards.length} bug${bugCards.length !== 1 ? 's' : ''}`;

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
                            if (labelName.includes('high') || labelName.includes('urgent') || labelName.includes(
                                    'critical')) {
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
                    cardElement.className =
                        `bug-card bg-white rounded-lg shadow-md min-w-[300px] max-w-[300px] ${priorityClass}`;

                    // Extract member names if available
                    let memberNames = 'Not assigned';
                    let memberAvatars = '';
                    if (card.members && card.members.length > 0) {
                        memberNames = card.members.map(member => member.fullName || member.username).join(
                            ', ');

                        // Create member avatars
                        memberAvatars = '<div class="flex -space-x-2 overflow-hidden">';
                        card.members.forEach((member, index) => {
                            if (index < 3) { // Limit to 3 avatars to save space
                                const initial = (member.fullName || member.username || '?').charAt(
                                    0).toUpperCase();
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
                switch (color) {
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
                    const actualPoints = parseFloat(document.getElementById('actual-points').textContent) ||
                        0;

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

            // Add event listener for edit plan points button to make input editable
            const editPlanPointsBtn = document.getElementById('edit-plan-points');
            if (editPlanPointsBtn) {
                editPlanPointsBtn.addEventListener('click', function() {
                    // สลับสถานะ readonly
                    planPointsInput.readOnly = !planPointsInput.readOnly;

                    if (!planPointsInput.readOnly) {
                        // เปิดให้แก้ไข
                        planPointsInput.classList.add('bg-yellow-50');
                        planPointsInput.focus();
                    } else {
                        // บันทึกค่าใหม่
                        planPointsInput.classList.remove('bg-yellow-50');

                        // อัปเดตค่าใน localStorage และตั้งค่า flag ว่ามีการแก้ไขด้วยตนเอง
                        if (currentBoardId) {
                            localStorage.setItem(`planPoints_${currentBoardId}`, planPointsInput.value);
                            localStorage.setItem(`planPointEdited_${currentBoardId}`, 'true');

                            // บันทึกค่าลงฐานข้อมูลผ่าน API
                            const boardSelector = document.getElementById('board-selector');
                            const boardName = boardSelector ? boardSelector.options[boardSelector.selectedIndex].text : '';

                            fetch('{{ route("trello.save.plan.point") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    board_id: currentBoardId,
                                    board_name: boardName,
                                    plan_point: parseFloat(planPointsInput.value) || 0
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    showToast('Plan point saved successfully', 'success');

                                    // อัปเดต cachedData
                                    if (window.cachedData && window.cachedData.storyPoints) {
                                        window.cachedData.storyPoints.planPoints = parseFloat(planPointsInput.value) || 0;
                                    }

                                    // อัปเดตการคำนวณที่เกี่ยวข้อง
                                    updateTotals();
                                } else {
                                    showToast('Error saving plan point', 'error');
                                }
                            })
                            .catch(error => {
                                console.error('Error saving plan point:', error);
                                showToast('Error saving plan point', 'error');
                            });
                        }
                    }
                });

                // ตรวจจับการกด Enter เพื่อบันทึก
                planPointsInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' && !planPointsInput.readOnly) {
                        editPlanPointsBtn.click(); // จำลองการคลิกปุ่ม edit เพื่อบันทึก
                    }
                });
            }

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

                    const cols = rows[i.querySelectorAll('td')];
                    let rowData = [];

                    cols.forEach(col => {
                        // For the member column, just get the name, not the entire HTML
                        if (col.querySelector('.font-medium')) {
                            rowData.push('"' + col.querySelector('.font-medium').innerText.trim().replace(
                                /"/g, '""') + '"');
                        } else {
                            rowData.push('"' + col.innerText.trim().replace(/"/g, '""') + '"');
                        }
                    });

                    csv.push(rowData.join(','));
                }

                // Create and download CSV file
                const csvContent = csv.join('\n');
                const blob = new Blob([csvContent], {
                    type: 'text/csv;charset=utf-8;'
                });

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
                toast.className =
                    'toast-notification flex items-center p-4 mb-4 rounded shadow-lg max-w-md transition-opacity';

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
                fetch(`${apiBaseUrl}/trello/data?board_id=` + boardSelector.value)
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
                fetch(`${apiBaseUrl}/trello/bug-cards?board_id=` + boardSelector.value)
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
                fetch(`${apiBaseUrl}/trello/sprint-info`)
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
                const sprintNumberBadges = document.querySelectorAll(
                    '.bg-primary-600.text-white.px-3.py-1.rounded-bl-md.text-xs.font-bold');
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
                    startDateEl.innerHTML =
                        `<span class="text-primary-700">Start:</span> ${data.currentSprintStartDate || '-'}`;
                }

                const endDateEl = document.querySelector('.top-7.right-2.text-xs.font-medium');
                if (endDateEl) {
                    const endPrefix = endDateEl.querySelector('span');
                    endDateEl.innerHTML =
                        `<span class="text-primary-700">End:</span> ${data.currentSprintEndDate || '-'}`;
                }

                // Regenerate day markers based on updated total days
                const totalDays = data.sprintTotalDays || 7;
                const markerWidth = 100 / totalDays;

                // Find the timeline container
                const timelineContainer = document.querySelector(
                    '.relative.h-16.rounded-lg.overflow-hidden.bg-gray-100.shadow-inner');

                if (timelineContainer) {
                    // Remove existing day markers
                    timelineContainer.querySelectorAll('.border-r.border-gray-300, .bottom-1.text-xs.text-gray-500')
                        .forEach(el => {
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

                // Update backlog data if available
                if (data.backlogData) {
                    updateBacklogTable(data.backlogData);
                }

                // Fetch sprint information
                fetchSprintInfo();
            }

            // Function to update the backlog table with new data
            function updateBacklogTable(backlogData) {
                // Update the total points badge
                const backlogTotalPoints = document.getElementById('backlogTotalPoints');
                if (backlogTotalPoints) {
                    backlogTotalPoints.textContent = backlogData.totalBugPoints || '0';
                }

                // Get the table body
                const tableBody = document.querySelector('#backlog-table tbody');
                if (!tableBody) return;

                // Clear existing rows
                tableBody.innerHTML = '';

                // Check if we have bugs to display
                if (!backlogData.allBugs || backlogData.allBugs.length === 0) {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                No backlog tasks found.
                            </td>
                        </tr>
                    `;
                    return;
                }

                // Add new rows
                Object.values(backlogData.allBugs).forEach(bug => {
                    const row = document.createElement('tr');
                    row.className = 'hover:bg-gray-50';

                    row.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="text-sm font-medium text-gray-900">
                                    <a href="${bug.url || '#'}" target="_blank" class="hover:text-primary-600">
                                        ${bug.name}
                                    </a>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                ${bug.team}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            ${bug.points || '-'}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            Sprint ${bug.sprint_origin || bug.sprint_number || '?'}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${bug.status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}">
                                ${bug.status ? bug.status.charAt(0).toUpperCase() + bug.status.slice(1) : 'In Progress'}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <button class="edit-backlog-task px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800 hover:bg-blue-200"
                                data-bug-id="${bug.id ?? ''}"
                                data-bug-name="${bug.name ?? ''}"
                                data-bug-description="${bug.description ?? ''}"
                                data-bug-points="${bug.points ?? 0}">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            <button class="delete-backlog-task px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800 hover:bg-red-200"
                                data-bug-id="${bug.id ?? ''}"
                                data-bug-name="${bug.name ?? ''}">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    `;

                    tableBody.appendChild(row);
                });
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
                                planPoints: parseInt(document.getElementById('plan-points')
                                    ?.value || '0'),
                                actualPoints: parseInt(document.getElementById('actual-points')
                                    ?.textContent || '0'),
                                remainPercent: document.getElementById('remain-percent')
                                    ?.textContent || '0%',
                                percentComplete: document.getElementById('percent-complete')
                                    ?.textContent || '0%',
                                currentSprintPoints: parseInt(document.getElementById(
                                    'current-sprint-points')?.textContent || '0'),
                                actualCurrentSprint: parseInt(document.getElementById(
                                    'actual-current-sprint')?.textContent || '0'),
                                boardName: document.getElementById('board-name-display')
                                    ?.textContent || '',
                                lastUpdated: document.getElementById('last-updated')?.textContent ||
                                    ''
                            },
                            teamMembers: Array.from(document.querySelectorAll(
                                '#team-members-table-body tr')).map(row => {
                                const cells = row.querySelectorAll('td');
                                if (cells.length >= 7) {
                                    return {
                                        name: cells[0].textContent.trim(),
                                        pointPersonal: parseFloat(cells[1].textContent) ||
                                            0,
                                        pass: parseFloat(cells[2].textContent) || 0,
                                        bug: parseFloat(cells[3].textContent) || 0,
                                        cancel: parseFloat(cells[4].textContent) || 0,
                                        extra: parseFloat(cells[5].textContent) || 0,
                                        final: parseFloat(cells[6].textContent) || 0,
                                        passPercent: cells[7].textContent.trim()
                                    };
                                }
                                return null;
                            }).filter(x => x !== null),
                            totals: {
                                totalPersonal: parseFloat(document.getElementById('total-personal')
                                    ?.textContent || '0'),
                                totalPass: parseFloat(document.getElementById('total-pass')
                                    ?.textContent || '0'),
                                totalBug: parseFloat(document.getElementById('total-bug')
                                    ?.textContent || '0'),
                                totalCancel: parseFloat(document.getElementById('total-cancel')
                                    ?.textContent || '0'),
                                totalExtra: parseFloat(document.getElementById('total-extra')
                                    ?.textContent || '0'),
                                totalFinal: parseFloat(document.getElementById('total-final')
                                    ?.textContent || '0')
                            }
                        });

                        reportData.story_points_data = storyPointsJson;
                    }

                    if (bugCardsContainer) {
                        const bugCardsJson = JSON.stringify({
                            bugCards: Array.from(document.querySelectorAll('.bug-card')).map(
                                card => {
                                    const nameElement = card.querySelector(
                                        '.font-medium.text-gray-900');
                                    const pointElement = card.querySelector(
                                        '.bg-red-600.rounded-full');
                                    const listElement = card.querySelector(
                                        '.text-xs.text-gray-500');
                                    const descriptionElement = card.querySelector(
                                        '.description-content');
                                    const memberElement = card.querySelector(
                                        '.text-xs.text-gray-500.mt-1');

                                    return {
                                        name: nameElement ? nameElement.textContent.trim() :
                                            'Unnamed Card',
                                        points: pointElement ? parseInt(pointElement
                                            .textContent) || 0 : 0,
                                        list: listElement ? listElement.textContent.replace(
                                            'From:', '').trim() : '',
                                        description: descriptionElement ? descriptionElement
                                            .textContent.trim() : '',
                                        members: memberElement ? memberElement.textContent
                                            .trim() : 'Not assigned',
                                        priorityClass: Array.from(card.classList).find(c => c
                                            .startsWith('priority-')) || 'priority-none'
                                    };
                                }),
                            bugCount: document.getElementById('bug-count')?.textContent || '0 bugs',
                            totalBugPoints: document.getElementById('total-bug-points')
                                ?.textContent || '0'
                        });

                        reportData.bug_cards_data = bugCardsJson;
                    }

                    console.log('Saving report data to session:', {
                        hasStoryPoints: !!reportData.story_points_data,
                        hasBugCards: !!reportData.bug_cards_data
                    });

                    // Store board selection in session
                    fetch(`${apiBaseUrl}/save/board-selection`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .getAttribute('content')
                            },
                            body: JSON.stringify(reportData)
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Redirect to create report form
                                window.location.href = `${apiBaseUrl}/saved-reports/create`;
                            } else {
                                showToast('Error: ' + (data.message ||
                                    'Failed to save board selection'), 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error saving board selection:', error);
                            showToast('An error occurred while saving the board selection', 'error');
                        });
                });
            }

            // Extra Points Modal Functionality
            const extraPointsModal = document.getElementById('extra-points-modal');
            const extraPointsInput = document.getElementById('extra-points-input');
            const cancelExtraPoints = document.getElementById('cancel-extra-points');
            const saveExtraPoints = document.getElementById('save-extra-points');
            let currentMemberId = null;
            let currentRowIndex = null;

            // Function to open extra points modal
            function openExtraPointsModal(memberId, rowIndex, currentExtraPoints) {
                currentMemberId = memberId;
                currentRowIndex = rowIndex;
                extraPointsInput.value = currentExtraPoints || 0;
                document.getElementById('extra-points-member-id').value = memberId;
                document.getElementById('extra-points-row-index').value = rowIndex;
                extraPointsModal.classList.remove('hidden');
            }

            // Function to close extra points modal
            function closeExtraPointsModal() {
                extraPointsModal.classList.add('hidden');
                extraPointsInput.value = '';
                currentMemberId = null;
                currentRowIndex = null;
            }

            // Add click handlers for each row in the team members table
            document.getElementById('team-members-table-body').addEventListener('click', function(e) {
                const row = e.target.closest('tr');
                if (!row) return;

                const extraCell = row.querySelector('td:nth-child(6)'); // Extra points column
                if (e.target === extraCell || extraCell.contains(e.target)) {
                    const memberId = row.dataset.memberId;
                    const rowIndex = Array.from(row.parentElement.children).indexOf(row);
                    const currentExtraPoints = parseFloat(extraCell.textContent) || 0;
                    openExtraPointsModal(memberId, rowIndex, currentExtraPoints);
                }
            });

            // Cancel button handler
            cancelExtraPoints.addEventListener('click', closeExtraPointsModal);

            // Save button handler
            saveExtraPoints.addEventListener('click', function() {
                const extraPoints = parseFloat(extraPointsInput.value) || 0;
                const row = document.querySelector(
                    `#team-members-table-body tr:nth-child(${parseInt(currentRowIndex) + 1})`);

                if (row) {
                    const extraCell = row.querySelector('td:nth-child(6)');
                    const finalCell = row.querySelector('td:nth-child(7)');
                    const pointPersonalCell = row.querySelector('td:nth-child(2)');
                    const bugCell = row.querySelector('td:nth-child(4)');

                    // Update extra points cell
                    extraCell.textContent = extraPoints.toFixed(1);

                    // Get values from cells
                    const pointPersonal = parseFloat(pointPersonalCell.textContent) || 0;
                    const bugPoints = parseFloat(bugCell.textContent) || 0;

                    // Recalculate final points (personal points - bug points)
                    const finalPoints = pointPersonal - bugPoints;
                    finalCell.textContent = finalPoints.toFixed(1);

                    // Save the extra points to localStorage
                    if (currentBoardId && currentMemberId) {
                        // Use a key format that includes both board and member ID
                        const storageKey = `extraPoints_${currentBoardId}_${currentMemberId}`;
                        localStorage.setItem(storageKey, extraPoints);

                        // Also update the cachedData if we have it
                        if (window.cachedData && window.cachedData.memberPoints) {
                            const memberIndex = window.cachedData.memberPoints.findIndex(m => m.id === currentMemberId);
                            if (memberIndex >= 0) {
                                window.cachedData.memberPoints[memberIndex].extraPoint = extraPoints;

                                // Make sure we're using the right property name consistently
                                const member = window.cachedData.memberPoints[memberIndex];

                                // Update final point in the cached data
                                window.cachedData.memberPoints[memberIndex].finalPoint = finalPoints;

                                console.log('Updated cached data:', window.cachedData.memberPoints[memberIndex]);
                            } else {
                                console.warn('Member not found in cached data:', currentMemberId);
                            }
                        } else {
                            console.warn('No cached member points data available');
                        }
                    }

                    // Update totals
                    updateTotals();
                }

                closeExtraPointsModal();
                showToast('Extra points updated successfully', 'success');
            });

            // Function to update totals
            function updateTotals() {
                const rows = document.querySelectorAll('#team-members-table-body tr');
                let totals = {
                    personal: 0,
                    pass: 0,
                    bug: 0,
                    cancel: 0,
                    extra: 0,
                    final: 0
                };

                rows.forEach(row => {
                    totals.personal += parseFloat(row.querySelector('td:nth-child(2)').textContent) || 0;
                    totals.pass += parseFloat(row.querySelector('td:nth-child(3)').textContent) || 0;
                    totals.bug += parseFloat(row.querySelector('td:nth-child(4)').textContent) || 0;
                    totals.cancel += parseFloat(row.querySelector('td:nth-child(5)').textContent) || 0;
                    totals.extra += parseFloat(row.querySelector('td:nth-child(6)').textContent) || 0;
                    totals.final += parseFloat(row.querySelector('td:nth-child(7)').textContent) || 0;
                });

                // Update totals in footer
                document.getElementById('total-personal').textContent = totals.personal.toFixed(1);
                document.getElementById('total-pass').textContent = totals.pass.toFixed(1);
                document.getElementById('total-bug').textContent = totals.bug.toFixed(1);
                document.getElementById('total-cancel').textContent = totals.cancel.toFixed(1);
                document.getElementById('total-extra').textContent = totals.extra.toFixed(1);
                document.getElementById('total-final').textContent = totals.final.toFixed(1);

                // Update actual points and recalculate percentages for the sprint summary
                document.getElementById('actual-points').textContent = totals.final.toFixed(1);
                document.getElementById('actual-current-sprint').textContent = (totals.final + totals.extra).toFixed(1);

                // Check if we have a user-input plan point value
                const planPointsInput = document.getElementById('plan-points');
                const savedPlanPoints = localStorage.getItem(`planPoints_${currentBoardId}`);

                // If no saved value or the saved value equals the previous total personal points,
                // update plan points to match the new total personal points
                if (!savedPlanPoints || parseFloat(savedPlanPoints) === parseFloat(planPointsInput.getAttribute('data-previous-total') || '0')) {
                    planPointsInput.value = totals.personal.toFixed(1);
                    // Save this as the new default
                    if (currentBoardId) {
                        localStorage.setItem(`planPoints_${currentBoardId}`, planPointsInput.value);
                    }
                }

                // Store current total for future reference
                planPointsInput.setAttribute('data-previous-total', totals.personal.toFixed(1));

                // Get plan points (either user input or automatically set)
                const planPoints = parseFloat(planPointsInput.value) || 0;

                // Recalculate percentages
                if (planPoints > 0) {
                    const remainPercent = Math.round(((planPoints - totals.final) / planPoints) * 100);
                    const percentComplete = Math.round((totals.final / planPoints) * 100);

                    document.getElementById('remain-percent').textContent = `${remainPercent}%`;
                    document.getElementById('percent-complete').textContent = `${percentComplete}%`;
                }
            }

            // Add event listener for Create New Report button
            const createNewReportBtn = document.getElementById('create-new-report-btn');
            if (createNewReportBtn) {
                createNewReportBtn.addEventListener('click', function() {
                    const boardSelector = document.getElementById('board-selector');
                    if (!boardSelector || !boardSelector.value) {
                        showToast('Please select a board first', 'error');
                        return;
                    }

                    const boardId = boardSelector.value;
                    const boardName = boardSelector.options[boardSelector.selectedIndex].text;

                    // Get the current sprint number
                    let sprintNumber = document.getElementById('current-sprint-number')?.textContent || '1';

                    // Default report name
                    let reportName = `Sprint ${sprintNumber} Report - ${boardName}`;

                    // Create a modal to let the user name the report
                    const modal = document.createElement('div');
                    modal.className =
                        'fixed inset-0 bg-gray-600 bg-opacity-75 flex items-center justify-center z-50';
                    modal.innerHTML = `
                    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md mx-auto">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Create New Report</h3>
                        <form id="quick-report-form">
                            <div class="mb-4">
                                <label for="report-name" class="block text-sm font-medium text-gray-700 mb-1">Report Name</label>
                                <input type="text" id="report-name" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500" value="${reportName}" required>
                            </div>
                            <div class="mb-4">
                                <label for="report-notes" class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                                <textarea id="report-notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"></textarea>
                            </div>
                            <div class="flex justify-end space-x-3">
                                <button type="button" id="cancel-report" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                    Cancel
                                </button>
                                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-md shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                    Save Report
                                </button>
                            </div>
                        </form>
                    </div>
                `;

                    document.body.appendChild(modal);

                    // Handle cancel button
                    document.getElementById('cancel-report').addEventListener('click', function() {
                        document.body.removeChild(modal);
                    });

                    // Handle form submission
                    document.getElementById('quick-report-form').addEventListener('submit', function(e) {
                        e.preventDefault();

                        const reportName = document.getElementById('report-name').value;
                        const notes = document.getElementById('report-notes').value;

                        // Capture the current report data
                        const storyPointsData = document.getElementById('story-points-summary');
                        const bugCardsContainer = document.getElementById(
                            'cards-by-list-container');

                        // Create structured data for storage
                        const reportData = {
                            board_id: boardId,
                            board_name: boardName,
                            report_name: reportName,
                            name: reportName, // Add the 'name' field which is required by the database
                            notes: notes
                        };

                        // Helper function to trim long text fields
                        const trimDescription = (text, maxLength = 1000) => {
                            if (!text) return '';
                            return text.length > maxLength ? text.substring(0, maxLength) +
                                '...' : text;
                        };

                        if (storyPointsData) {
                            reportData.story_points_data = JSON.stringify({
                                summary: {
                                    planPoints: parseInt(document.getElementById(
                                        'plan-points')?.value || '0'),
                                    actualPoints: parseInt(document.getElementById(
                                        'actual-points')?.textContent || '0'),
                                    remainPercent: document.getElementById('remain-percent')
                                        ?.textContent || '0%',
                                    percentComplete: document.getElementById(
                                        'percent-complete')?.textContent || '0%',
                                    currentSprintPoints: parseInt(document.getElementById(
                                            'current-sprint-points')?.textContent ||
                                        '0'),
                                    actualCurrentSprint: parseInt(document.getElementById(
                                            'actual-current-sprint')?.textContent ||
                                        '0'),
                                    boardName: document.getElementById('board-name-display')
                                        ?.textContent || '',
                                    lastUpdated: document.getElementById('last-updated')
                                        ?.textContent || ''
                                },
                                teamMembers: Array.from(document.querySelectorAll(
                                    '#team-members-table-body tr')).map(row => {
                                    const cells = row.querySelectorAll('td');
                                    if (cells.length >= 7) {
                                        return {
                                            name: cells[0].textContent.trim(),
                                            pointPersonal: parseFloat(cells[1]
                                                .textContent) || 0,
                                            pass: parseFloat(cells[2]
                                                .textContent) || 0,
                                            bug: parseFloat(cells[3].textContent) ||
                                                0,
                                            cancel: parseFloat(cells[4]
                                                .textContent) || 0,
                                            extra: parseFloat(cells[5]
                                                .textContent) || 0,
                                            final: parseFloat(cells[6]
                                                .textContent) || 0,
                                            passPercent: cells[7].textContent.trim()
                                        };
                                    }
                                    return null;
                                }).filter(x => x !== null),
                                totals: {
                                    totalPersonal: parseFloat(document.getElementById(
                                        'total-personal')?.textContent || '0'),
                                    totalPass: parseFloat(document.getElementById(
                                        'total-pass')?.textContent || '0'),
                                    totalBug: parseFloat(document.getElementById(
                                        'total-bug')?.textContent || '0'),
                                    totalCancel: parseFloat(document.getElementById(
                                        'total-cancel')?.textContent || '0'),
                                    totalExtra: parseFloat(document.getElementById(
                                        'total-extra')?.textContent || '0'),
                                    totalFinal: parseFloat(document.getElementById(
                                        'total-final')?.textContent || '0')
                                }
                            });
                        }

                        if (bugCardsContainer) {
                            reportData.bug_cards_data = JSON.stringify({
                                bugCards: Array.from(document.querySelectorAll('.bug-card'))
                                    .map(card => {
                                        const nameElement = card.querySelector(
                                            '.font-medium.text-gray-900');
                                        const pointElement = card.querySelector(
                                            '.bg-red-600.rounded-full');
                                        const listElement = card.querySelector(
                                            '.text-xs.text-gray-500');
                                        const descriptionElement = card.querySelector(
                                            '.description-content');
                                        const memberElement = card.querySelector(
                                            '.text-xs.text-gray-500.mt-1');

                                        return {
                                            name: nameElement ? nameElement.textContent
                                                .trim() : 'Unnamed Card',
                                            points: pointElement ? parseInt(pointElement
                                                .textContent) || 0 : 0,
                                            list: listElement ? listElement.textContent
                                                .replace('From:', '').trim() : '',
                                            description: descriptionElement ?
                                                trimDescription(descriptionElement
                                                    .textContent.trim()) : '',
                                            members: memberElement ? memberElement
                                                .textContent.trim() : 'Not assigned',
                                            priorityClass: Array.from(card.classList)
                                                .find(c => c.startsWith('priority-')) ||
                                                'priority-none'
                                        };
                                    }),
                                bugCount: document.getElementById('bug-count')
                                    ?.textContent || '0 bugs',
                                totalBugPoints: document.getElementById('total-bug-points')
                                    ?.textContent || '0'
                            });
                        }

                        // Show loading message
                        const saveButton = e.target.querySelector('button[type="submit"]');
                        const originalButtonText = saveButton.textContent;
                        saveButton.disabled = true;
                        saveButton.textContent = 'Saving...';

                        console.log('Saving report with data:', reportData);

                        // Submit directly to savedReports.store endpoint
                        fetch(`${apiBaseUrl}/report/save`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]').getAttribute('content'),
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                body: JSON.stringify(reportData)
                            })
                            .then(response => {
                                console.log('Response status:', response.status);
                                // Check if the response is JSON
                                const contentType = response.headers.get('content-type');
                                console.log('Response content type:', contentType);

                                if (contentType && contentType.includes('application/json')) {
                                    return response.json().then(data => {
                                        if (!response.ok) {
                                            console.error('Error response data:', data);
                                            throw new Error(data.error || data
                                                .message ||
                                                'Network response was not ok');
                                        }
                                        return data;
                                    });
                                } else {
                                    // Handle HTML response or other types
                                    if (!response.ok) {
                                        // Try to get text content for more info
                                        return response.text().then(text => {
                                            console.error('Error response text:', text);
                                            throw new Error(
                                                'Network response was not ok');
                                        });
                                    }
                                    // Return a default success object for non-JSON responses
                                    return {
                                        success: response.ok,
                                        message: 'Report saved successfully'
                                    };
                                }
                            })
                            .then(data => {
                                document.body.removeChild(modal);

                                if (data.success) {
                                    showToast('Report saved successfully!', 'success');

                                    // Optionally redirect to saved reports
                                    if (confirm(
                                            'Report saved successfully! View saved reports?')) {
                                        window.location.href =
                                            `${apiBaseUrl}/saved-reports`;
                                    }
                                } else {
                                    showToast(data.error || 'Error saving report', 'error');
                                }
                            })
                            .catch(error => {
                                console.error('Error saving report:', error);
                                saveButton.disabled = false;
                                saveButton.textContent = originalButtonText;
                                showToast('Error saving report: ' + error.message, 'error');
                            });
                    });
                });
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Edit Modal Elements
            const editModal = document.getElementById('edit-backlog-modal');
            const editForm = document.getElementById('edit-backlog-form');
            const cancelEditBtn = document.getElementById('cancel-edit-backlog');
            const editBugId = document.getElementById('edit-bug-id');
            const editBugName = document.getElementById('edit-bug-name');
            const editBugDescription = document.getElementById('edit-bug-description');
            const editBugPoints = document.getElementById('edit-bug-points');

            // Debug check if elements exist
            console.log('Modal elements:', {
                editModal: !!editModal,
                editForm: !!editForm,
                cancelEditBtn: !!cancelEditBtn,
                editBugId: !!editBugId,
                editBugName: !!editBugName,
                editBugDescription: !!editBugDescription,
                editBugPoints: !!editBugPoints
            });

            // Use event delegation for edit buttons
            document.addEventListener('click', function(e) {
                const editButton = e.target.closest('.edit-backlog-task');
                if (editButton) {
                    console.log('Edit button clicked');
                    const bugId = editButton.getAttribute('data-bug-id');
                    const bugName = editButton.getAttribute('data-bug-name');
                    const bugDescription = editButton.getAttribute('data-bug-description');
                    const bugPoints = editButton.getAttribute('data-bug-points');

                    console.log('Bug data:', { bugId, bugName, bugDescription, bugPoints });

                    if (editModal && editBugId && editBugName && editBugDescription && editBugPoints) {
                        editBugId.value = bugId;
                        editBugName.value = bugName;
                        editBugDescription.value = bugDescription;
                        editBugPoints.value = bugPoints;

                        // Remove both hidden class and display:none style
                        editModal.classList.remove('hidden');
                        editModal.style.display = 'block';
                        console.log('Modal should be visible now');
                    } else {
                        console.error('Some modal elements are missing');
                    }
                }
            });

            // Cancel Button
            if (cancelEditBtn) {
                cancelEditBtn.addEventListener('click', () => {
                    console.log('Cancel button clicked');
                    editModal.classList.add('hidden');
                    editModal.style.display = 'none';
                    editForm.reset();
                });
            }

            // Close modal when clicking outside
            if (editModal) {
                editModal.addEventListener('click', function(e) {
                    if (e.target === editModal) {
                        console.log('Clicked outside modal');
                        editModal.classList.add('hidden');
                        editModal.style.display = 'none';
                        editForm.reset();
                    }
                });
            }

            // Form Submit Handler
            if (editForm) {
                editForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    console.log('Form submitted');
                    const bugId = editBugId.value;

                    try {
                        const formData = {
                            name: editBugName.value,
                            description: editBugDescription.value,
                            points: parseInt(editBugPoints.value) || 0,
                            team: document.querySelector('input[name="team"]')?.value || 'Unknown Team', // Add team field
                            assigned: document.querySelector('input[name="assigned"]')?.value || null // Add assigned field
                        };

                        // Use the correct API endpoint
                        const apiUrl = `${apiBaseUrl}/backlog/${bugId}`;
                        console.log('Sending data:', formData);
                        console.log('To URL:', apiUrl);

                        const response = await fetch(apiUrl, {
                            method: 'PUT', // Changed back to PUT to match the route definition
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(formData)
                        });

                        console.log('Response status:', response.status);

                        let data;
                        const contentType = response.headers.get('content-type');
                        if (contentType && contentType.includes('application/json')) {
                            data = await response.json();
                        } else {
                            const text = await response.text();
                            console.error('Received non-JSON response:', text);
                            throw new Error('Server returned an invalid response format');
                        }

                        if (!response.ok) {
                            throw new Error(data.message || 'Failed to update task');
                        }

                        // Show success message
                        alert('Task updated successfully');

                        // Close modal and refresh page
                        editModal.classList.add('hidden');
                        editModal.style.display = 'none';
                        editForm.reset();
                        window.location.reload();
                    } catch (error) {
                        console.error('Error updating backlog task:', error);
                        alert('Failed to update the task. Please check the console for details.');
                    }
                });
            }

            // Add delete button handler
            const deleteBugBtn = document.getElementById('deleteBugBtn');
            if (deleteBugBtn) {
                deleteBugBtn.addEventListener('click', async function() {
                    if (!confirm('Are you sure you want to delete this bug?')) {
                        return;
                    }

                    const bugId = editBugId.value;
                    try {
                        const bugId = editBugId.value;
                        // Extract the numeric ID from the bug ID (e.g., "BUG-720" -> "720")
                        const numericId = bugId.split('-').pop();
                        const apiUrl = `${window.location.origin}/backlog/${numericId}`; // Use the numeric ID
                        const response = await fetch(apiUrl, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });

                        if (!response.ok) {
                            const data = await response.json();
                            throw new Error(data.error || 'Failed to delete bug');
                        }

                        // Show success message
                        alert('Bug deleted successfully');

                        // Close modal and refresh page
                        editModal.classList.add('hidden');
                        editModal.style.display = 'none';
                        editForm.reset();

                        // Remove the bug from the UI
                        const bugElement = document.querySelector(`[data-bug-id="${bugId}"]`);
                        if (bugElement) {
                            bugElement.remove();
                        }

                        // Optionally reload the page to refresh the data
                        window.location.reload();
                    } catch (error) {
                        console.error('Error deleting bug:', error);
                        alert('Failed to delete bug. Please try again.');
                    }
                });
            }
        });
    </script>
@endif
@endsection
