@extends('layouts.app')

@section('title', 'Sprint Settings')

@section('page-title', 'Sprint Settings')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold">Sprint Settings</h1>

            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Dashboard
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700">
                        {{ session('success') }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </p>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white shadow rounded-lg p-6">
        <form action="{{ route('settings.sprint.update') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-4">
                <div class="sm:col-span-2">
                    <h2 class="text-lg font-medium text-gray-900 mb-2">Sprint Configuration</h2>
                    <p class="text-sm text-gray-500 mb-4">
                        Configure how sprint reports are generated automatically. Reports will be saved at the end of each sprint period.
                    </p>
                </div>

                <!-- Sprint Duration -->
                <div>
                    <label for="sprint_duration" class="block text-sm font-medium text-gray-700">Sprint Duration</label>
                    <select name="sprint_duration" id="sprint_duration" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md">
                        <option value="7" {{ (int)$sprintDuration === 7 ? 'selected' : '' }}>1 Week (7 days)</option>
                        <option value="14" {{ (int)$sprintDuration === 14 ? 'selected' : '' }}>2 Weeks (14 days)</option>
                        <option value="21" {{ (int)$sprintDuration === 21 ? 'selected' : '' }}>3 Weeks (21 days)</option>
                        <option value="28" {{ (int)$sprintDuration === 28 ? 'selected' : '' }}>4 Weeks (28 days)</option>
                    </select>
                    <p class="mt-1 text-sm text-gray-500">How long each sprint lasts. Sprints are counted from January 1st of the current year.</p>
                </div>

                <div>
                    <label for="sprint_start_day" class="block text-sm font-medium text-gray-700">Sprint Start Day</label>
                    <select name="sprint_start_day" id="sprint_start_day" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md">
                        <option value="1" {{ (int)$sprintStartDay === 1 ? 'selected' : '' }}>Monday</option>
                        <option value="2" {{ (int)$sprintStartDay === 2 ? 'selected' : '' }}>Tuesday</option>
                        <option value="3" {{ (int)$sprintStartDay === 3 ? 'selected' : '' }}>Wednesday</option>
                        <option value="4" {{ (int)$sprintStartDay === 4 ? 'selected' : '' }}>Thursday</option>
                        <option value="5" {{ (int)$sprintStartDay === 5 ? 'selected' : '' }}>Friday</option>
                        <option value="6" {{ (int)$sprintStartDay === 6 ? 'selected' : '' }}>Saturday</option>
                        <option value="0" {{ (int)$sprintStartDay === 0 ? 'selected' : '' }}>Sunday</option>
                    </select>
                    <p class="mt-1 text-sm text-gray-500">Day of the week when sprints start.</p>
                </div>

                <div>
                    <label for="sprint_end_time" class="block text-sm font-medium text-gray-700">Sprint End Time</label>
                    <select name="sprint_end_time" id="sprint_end_time" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md">
                        <option value="16:00" {{ $sprintEndTime === '16:00' ? 'selected' : '' }}>16:00</option>
                        <option value="17:00" {{ $sprintEndTime === '17:00' ? 'selected' : '' }}>17:00</option>
                        <option value="18:00" {{ $sprintEndTime === '18:00' ? 'selected' : '' }}>18:00</option>
                        <option value="23:59" {{ $sprintEndTime === '23:59' ? 'selected' : '' }}>23:59 (End of day)</option>
                    </select>
                    <p class="mt-1 text-sm text-gray-500">Time when sprint reports are generated (24-hour format).</p>
                </div>

                <div>
                    <label for="auto_save_enabled" class="block text-sm font-medium text-gray-700">Auto-Save Reports</label>
                    <select name="auto_save_enabled" id="auto_save_enabled" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md">
                        <option value="1" {{ $autoSaveEnabled === true ? 'selected' : '' }}>Enabled</option>
                        <option value="0" {{ $autoSaveEnabled === false ? 'selected' : '' }}>Disabled</option>
                    </select>
                    <p class="mt-1 text-sm text-gray-500">Automatically save reports at the end of each sprint.</p>
                </div>

                <div class="sm:col-span-2">
                    <h2 class="text-lg font-medium text-gray-900 mt-6 mb-2">Current Sprint Information</h2>
                    <div class="bg-gray-50 p-4 rounded-md">
                        <p class="text-sm text-gray-700">
                            <strong>Current Sprint:</strong> {{ $currentSprintNumber }}
                            <span class="text-gray-500 ml-2">(Week {{ $currentWeekNumber }} of the year)</span>
                        </p>
                        <p class="text-sm text-gray-700 mt-1">
                            <strong>Next Sprint Report:</strong> {{ $nextReportDate }}
                        </p>

                        <!-- Sprint Timeline Visualization -->
                        <div class="mt-4">
                            <h3 class="text-sm font-medium text-gray-700 mb-2">Sprint Timeline</h3>
                            <div class="relative bg-gray-200 h-12 rounded-md overflow-hidden">
                                <!-- Sprint Progress Bar -->
                                <div class="absolute top-0 left-0 h-full bg-primary-100" style="width: {{ $sprintProgressPercent }}%"></div>

                                <!-- Sprint Number Badge -->
                                <div class="absolute top-0 right-4 bg-primary-500 text-white px-2 py-1 rounded-b-md text-xs font-bold">
                                    Sprint {{ $currentSprintNumber }}
                                </div>

                                <!-- Start Date -->
                                <div class="absolute top-2 left-2 text-xs font-semibold">
                                    {{ $currentSprintStartDate }}
                                </div>

                                <!-- End Date -->
                                <div class="absolute top-2 right-24 text-xs font-semibold">
                                    {{ $currentSprintEndDate }}
                                </div>

                                <!-- Current Day Marker -->
                                @if($sprintProgressPercent > 0 && $sprintProgressPercent < 100)
                                <div class="absolute top-0 h-full" style="left: {{ $sprintProgressPercent }}%;">
                                    <div class="absolute top-0 -ml-0.5 h-full border-l-2 border-primary-600"></div>
                                    <div class="absolute -top-1 -ml-2 w-4 h-4 rounded-full bg-primary-600"></div>
                                    <div class="absolute -top-7 -ml-8 bg-primary-600 text-white px-2 py-0.5 rounded text-xs whitespace-nowrap">
                                        Today
                                    </div>
                                </div>
                                @elseif($sprintProgressPercent >= 100)
                                <div class="absolute top-0 h-full" style="left: 98%;">
                                    <div class="absolute top-0 -ml-0.5 h-full border-l-2 border-primary-600"></div>
                                    <div class="absolute -top-1 -ml-2 w-4 h-4 rounded-full bg-primary-600"></div>
                                    <div class="absolute -top-7 -ml-8 bg-primary-600 text-white px-2 py-0.5 rounded text-xs whitespace-nowrap">
                                        Complete
                                    </div>
                                </div>
                                @endif

                                <!-- Duration Label -->
                                <div class="absolute bottom-1 text-xs font-medium text-gray-700 left-1/2 transform -translate-x-1/2">
                                    @if($sprintDuration == 7)
                                        1 Week Duration
                                    @elseif($sprintDuration == 14)
                                        2 Weeks Duration
                                    @elseif($sprintDuration == 21)
                                        3 Weeks Duration
                                    @elseif($sprintDuration == 28)
                                        4 Weeks Duration
                                    @else
                                        {{ $sprintDuration }} Days Duration
                                    @endif
                                </div>
                            </div>

                            <!-- Sprint Stats -->
                            <div class="flex justify-between mt-2 text-xs text-gray-500">
                                <div>{{ $daysElapsed }} {{ Str::plural('day', $daysElapsed) }} elapsed</div>
                                <div>{{ $remainingText }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-end">
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    Save Settings
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white shadow rounded-lg p-6 mt-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Sprint Override</h2>
        <p class="text-sm text-gray-500 mb-4">
            You can manually set the current sprint number if needed. This will override the automatically calculated sprint number.
        </p>

        <form action="{{ route('settings.sprint.set-current') }}" method="POST" class="mt-4">
            @csrf
            <div class="flex items-end space-x-4">
                <div class="w-1/4">
                    <label for="current_sprint_number" class="block text-sm font-medium text-gray-700 mb-1">Current Sprint Number</label>
                    <input type="number" name="current_sprint_number" id="current_sprint_number"
                        class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-gray-300 rounded-md"
                        min="1" value="{{ $currentSprintNumber }}" placeholder="Enter sprint number">
                </div>
                <div>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Set Current Sprint
                    </button>
                </div>
            </div>
            <p class="mt-2 text-xs text-gray-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                Warning: Manually setting the sprint number may affect report generation and sprint tracking. Use only when necessary.
            </p>
        </form>
    </div>

    <div class="bg-white shadow rounded-lg p-6 mt-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Manual Sprint Reports</h2>
        <p class="text-sm text-gray-500 mb-4">
            You can manually trigger a sprint report generation for all boards. This will create reports with the current sprint information.
        </p>

        <form action="{{ route('settings.sprint.generate-now') }}" method="POST" class="mt-4">
            @csrf
            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Generate Sprint Reports Now
            </button>
        </form>
    </div>

    <div class="bg-white shadow rounded-lg p-6 mt-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">On-going Sprint Reports</h2>
        <p class="text-sm text-gray-500 mb-4">
            View and monitor the current on-going sprint reports and track your team's progress.
        </p>

        <div class="mt-4">
            <a href="{{ route('saved-reports.index', ['filter' => 'sprint']) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                View Current Sprint
            </a>
        </div>
    </div>
</div>
@endsection
