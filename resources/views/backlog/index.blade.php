@extends('layouts.app')

@section('title', 'Bug Backlog')

@section('page-title', 'Bug Backlog')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold flex items-center">
                Bug Backlog
                <span class="ml-3 text-sm bg-amber-100 text-amber-800 py-1 px-2 rounded-full">
                    {{ $allBugs->count() }} {{ Str::plural('bug', $allBugs->count()) }}
                    ({{ $allBugs->sum('points') }} {{ Str::plural('point', $allBugs->sum('points')) }})
                </span>
            </h1>

            <div class="flex items-center space-x-2">
                <a href="{{ route('sprints.index') }}" class="text-primary-600 hover:text-primary-900 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                    View All Sprints
                </a>

                <button onclick="window.print()" class="ml-4 inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print
                </button>
            </div>
        </div>

        <p class="mt-2 text-sm text-gray-600">
            This page displays all backlog bugs from previous sprints. These bugs have been tracked but not resolved in their original sprints.
        </p>
    </div>

    <!-- Tab Navigation -->
    <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex space-x-8">
            <button
                id="tab-all-bugs"
                class="tab-button whitespace-nowrap py-4 px-1 border-b-2 border-primary-500 font-medium text-sm text-primary-600"
                onclick="showTab('all-bugs')">
                All Bugs
            </button>
            <button
                id="tab-by-sprint"
                class="tab-button whitespace-nowrap py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300"
                onclick="showTab('by-sprint')">
                By Sprint
            </button>
            <button
                id="tab-by-team"
                class="tab-button whitespace-nowrap py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300"
                onclick="showTab('by-team')">
                By Team
            </button>
        </nav>
    </div>

    <!-- No Bugs Message (Only shown if there are no backlog bugs) -->
    @if($allBugs->count() == 0)
    <div class="bg-white shadow rounded-lg p-8 text-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No Backlog Bugs</h3>
        <p class="text-gray-500 mb-4">There are no unresolved bugs in the backlog. Great job!</p>
    </div>
    @else
    <!-- All Bugs Tab Content -->
    <div id="all-bugs-content" class="tab-content">
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">All Backlog Bugs
                <!-- Add Refresh Button -->
                <div class="flex justify-end mb-4">
                    <button
                        onclick="refreshPage()"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Refresh
                    </button>
                </div>

                <!-- ปุ่มเลือก sprint ที่ต้องการดู -->
                <div class="mb-6 flex justify-end">
                    <h2 class="text-lg font-semibold pe-5 place-self-center">Select Sprint : </h2>
                    <div class="relative">
                        <select
                            id="sprintDropdown"
                            class="block w-full text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                            onchange="showSprint(this.value)">
                            <option value="" disabled selected>Select a Sprint</option>
                            @foreach(array_keys($bugsBySprint) as $sprintNumber)
                            <option value="{{ $sprintNumber }}">Sprint {{ $sprintNumber }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </h2>

            <!-- เนื้อหาใน sprint dropdown -->
            <div id="sprint-content-container">
                @foreach($bugsBySprint as $sprintNumber => $bugs)
                <div id="sprint-{{ $sprintNumber }}-content" class="sprint-content hidden">
                    <h3 class="text-xl font-semibold mb-4">Sprint {{ $sprintNumber }} Backlog</h3>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-2">
                        @foreach($bugs as $bug)
                        <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                            <!-- Card Header with Bug ID and Priority -->
                            <div class="flex justify-between items-center p-4 border-b border-gray-100">
                                <div class="flex items-center">
                                    <!-- Points in circle -->
                                    <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-500 flex items-center justify-center font-bold text-sm mr-3">
                                        {{ $bug['points'] ?? '-' }}
                                    </div>
                                    <!-- Bug ID and Name -->
                                    <div>
                                        <a href="{{ $bug['url'] ?? '#' }}" class="text-gray-900 font-medium hover:text-primary-600" target="_blank">{{ $bug['name'] }}</a>
                                    </div>
                                </div>
                                <!-- Sprint badge -->
                                <div>
                                    @if(isset($bug['sprint_origin']))
                                    <span class="px-3 py-1 text-xs rounded-full bg-gray-100 text-gray-600">
                                        Sprint {{ $bug['sprint_origin'] }}
                                    </span>
                                    @else
                                    <span class="px-3 py-1 text-xs rounded-full bg-gray-100 text-gray-600">
                                        Sprint {{ $bug['sprint_number'] ?? '?' }}
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Bug Name/Description/Buttons -->
                            <div class="p-4 grid grid-cols-9">
                                <div class="col-span-8">
                                    {{ $bug['description'] ?? 'No description available' }}
                                </div>

                                <!-- Edit Button -->
                                <button type="button" class="text-[#985E00] bg-[#FFC7B2] hover:bg-[#FFA954] focus:outline-none font-medium rounded-full px-2 py-2 text-center ms-3 h-8 w-8 col-start-9">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                                        <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z" />
                                        <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z" />
                                    </svg>
                                </button>

                                <!-- Delete Button -->
                                <button type="button" class="text-[#FF0004] bg-[#FFACAE] hover:bg-[#FF7C7E] focus:outline-none font-medium rounded-full px-2 py-2 text-center h-8 w-8 col-start-11">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3" viewBox="0 0 16 16">
                                        <path d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5M11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1zm1.958 1-.846 10.58a1 1 0 0 1-.997.92h-6.23a1 1 0 0 1-.997-.92L3.042 3.5zm-7.487 1a.5.5 0 0 1 .528.47l.5 8.5a.5.5 0 0 1-.998.06L5 5.03a.5.5 0 0 1 .47-.53Zm5.058 0a.5.5 0 0 1 .47.53l-.5 8.5a.5.5 0 1 1-.998-.06l.5-8.5a.5.5 0 0 1 .528-.47M8 4.5a.5.5 0 0 1 .5.5v8.5a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Card Footer with Assignment -->
                            <div class="bg-gray-50 px-4 py-3 flex justify-between items-center">
                                <div class="flex items-center">
                                    <span class="text-sm text-gray-500">Assign to :</span>
                                    <span class="ml-2 px-2 py-1 text-xs font-medium rounded-full bg-[#BAF3FF] text-[#13A7FD]">
                                        {{ $bug['team'] ?? '-' }}
                                    </span>
                                    <span class="ml-2 px-2 py-1 text-xs font-medium rounded-full">
                                        {{ $bug['assigned'] ?? 'Unassigned' }}
                                    </span>
                                </div>

                                <!-- Status indicator -->
                                <div>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-[#DDFFEC] text-[#82DF3C]">
                                        Success
                                    </span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>

            <!-- All Bugs List -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-2">
                @foreach($allBugs as $bug)
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                    <!-- Card Header with Bug ID and Priority -->
                    <div class="flex justify-between items-center p-4 border-b border-gray-100 ">
                        <div class="flex items-center">
                            <!-- Points in circle -->
                            <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-500 flex items-center justify-center font-bold text-sm mr-3">
                                {{ $bug['points'] ?? '-' }}
                            </div>
                            <!-- Bug ID and Name -->
                            <div>
                                <a href="{{ $bug['url'] ?? '#' }}" class="text-gray-900 font-medium hover:text-primary-600" target="_blank">{{ $bug['name'] }}</a>
                            </div>
                        </div>
                        <!-- Sprint badge -->
                        <div>
                            @if(isset($bug['sprint_origin']))
                            <span class="px-3 py-1 text-xs rounded-full bg-gray-100 text-gray-600">
                                Sprint {{ $bug['sprint_origin'] }}
                            </span>
                            @else
                            <span class="px-3 py-1 text-xs rounded-full bg-gray-100 text-gray-600">
                                Sprint {{ $bug['sprint_number'] ?? '?' }}
                            </span>
                            @endif
                        </div>
                    </div>

                    <!-- Bug Name/Description/buttom -->
                    <div class="p-4 grid grid-cols-9">

                        <div class="col-span-8">"Description"
                        </div>

                        <button type="button" class="text-[#985E00] bg-[#FFC7B2] hover:bg-[#FFA954] focus:outline-none font-medium rounded-full px-2 py-2 text-center ms-3 h-8 w-8 col-start-9">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class=" bi bi-pencil-square" viewBox="0 0 16 16 ">
                                <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z" />
                                <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z" />
                            </svg>
                        </button>

                        <button type="button" class="text-[#FF0004] bg-[#FFACAE] hover:bg-[#FF7C7E] focus:outline-none font-medium rounded-full px-2 py-2 text-center  h-8 w-8  col-start-11">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3" viewBox="0 0 16 16">
                                <path d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5M11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1zm1.958 1-.846 10.58a1 1 0 0 1-.997.92h-6.23a1 1 0 0 1-.997-.92L3.042 3.5zm-7.487 1a.5.5 0 0 1 .528.47l.5 8.5a.5.5 0 0 1-.998.06L5 5.03a.5.5 0 0 1 .47-.53Zm5.058 0a.5.5 0 0 1 .47.53l-.5 8.5a.5.5 0 1 1-.998-.06l.5-8.5a.5.5 0 0 1 .528-.47M8 4.5a.5.5 0 0 1 .5.5v8.5a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5" />
                            </svg>
                        </button>
                    </div>

                    <!-- Card Footer with Assignment -->
                    <div class="bg-gray-50 px-4 py-3 flex justify-between items-center">
                        <div class="flex items-center">
                            <span class="text-sm text-gray-500">Assign to :</span>

                            <span class="ml-2 px-2 py-1 text-xs font-medium rounded-full bg-[#BAF3FF] text-[#13A7FD]">
                                {{ $bug['team'] ?? '-' }}
                            </span>

                            <span class="ml-2 px-2 py-1 text-xs font-medium rounded-full">
                                {{ $bug['assigned'] ?? 'Unassigned' }}
                            </span>
                        </div>

                        <!-- Status indicator -->
                        <div>
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-[#DDFFEC] text-[#82DF3C]">
                                Success
                            </span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <!-- Pagination Links -->
            <div class="mt-6 pt-7">
                {{ $allBugs->links('pagination::tailwind') }}
            </div>
        </div>

    </div>
</div>


<!-- By Sprint Tab Content -->
<div id="by-sprint-content" class="tab-content hidden">
    <div class="space-y-8">
        @foreach($bugsBySprint as $sprintNumber => $bugs)
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">
                Sprint {{ $sprintNumber }} Backlog
                <span class="ml-2 text-sm bg-amber-100 text-amber-800 py-1 px-2 rounded-full">
                    {{ $bugs->count() }} {{ Str::plural('bug', $bugs->count()) }}
                    ({{ $bugs->sum('points') }} {{ Str::plural('point', $bugs->sum('points')) }})
                </span>
            </h2>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bug</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Points</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Team</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Origin</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($bugs as $bug)
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
                                <span class="text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- By Team Tab Content -->
<div id="by-team-content" class="tab-content hidden">
    <div class="space-y-8">
        @foreach($bugsByTeam as $teamName => $bugs)
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">
                {{ $teamName }} Backlog
                <span class="ml-2 text-sm bg-amber-100 text-amber-800 py-1 px-2 rounded-full">
                    {{ $bugs->count() }} {{ Str::plural('bug', $bugs->count()) }}
                    ({{ $bugs->sum('points') }} {{ Str::plural('point', $bugs->sum('points')) }})
                </span>
            </h2>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bug</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Points</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sprint</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Origin</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($bugs as $bug)
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
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">
                                    Sprint {{ $bug['sprint_number'] ?? '?' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if(isset($bug['sprint_origin']))
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                    Sprint {{ $bug['sprint_origin'] }}
                                </span>
                                @else
                                <span class="text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif
</div>



<script>
    function showTab(tabId) {
        // Hide all tab contents
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.add('hidden');
        });

        // Show the selected tab content
        document.getElementById(tabId + '-content').classList.remove('hidden');

        // Update tab buttons
        document.querySelectorAll('.tab-button').forEach(button => {
            button.classList.remove('border-primary-500', 'text-primary-600');
            button.classList.add('border-transparent', 'text-gray-500');
        });

        // Set active tab button
        document.getElementById('tab-' + tabId).classList.remove('border-transparent', 'text-gray-500');
        document.getElementById('tab-' + tabId).classList.add('border-primary-500', 'text-primary-600');
    }

    function showSprint(sprintNumber) {
        // Hide all sprint contents
        document.querySelectorAll('.sprint-content').forEach(content => {
            content.classList.add('hidden');
        });

        // Show the selected sprint content
        const sprintContent = document.getElementById('sprint-' + sprintNumber + '-content');
        if (sprintContent) {
            sprintContent.classList.remove('hidden');
        }
    }

    // Function to refresh the page
    function refreshPage() {
        window.location.reload(); // Reload the current page
    }

    // Function to show the selected sprint content
    function showSprint(sprintNumber) {
        // Hide all sprint contents
        document.querySelectorAll('.sprint-content').forEach(content => {
            content.classList.add('hidden');
        });

        // Show the selected sprint content
        const sprintContent = document.getElementById('sprint-' + sprintNumber + '-content');
        if (sprintContent) {
            sprintContent.classList.remove('hidden');
        }
    }
</script>


@endsection