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
                <!--ask if is admin go to route sprint index if not go to user report-->
                <a href="{{ auth()->user()->isAdmin() ? route('sprints.index') : route('reports') }}" class="text-primary-600 hover:text-primary-900 flex items-center">
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
    <div class="bg-white shadow rounded-lg p-8 text-center">
        <div class="flex ps-5 pt-3 ">
            <div class="w-20 h-20 rounded-full bg-sky-100 flex justify-center items-center ">
                <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" fill="#13A7FD" class="bi bi-bug"
                    viewBox="0 0 16 16">
                    <path
                        d="M4.355.522a.5.5 0 0 1 .623.333l.291.956A5 5 0 0 1 8 1c1.007 0 1.946.298 2.731.811l.29-.956a.5.5 0 1 1 .957.29l-.41 1.352A5 5 0 0 1 13 6h.5a.5.5 0 0 0 .5-.5V5a.5.5 0 0 1 1 0v.5A1.5 1.5 0 0 1 13.5 7H13v1h1.5a.5.5 0 0 1 0 1H13v1h.5a1.5 1.5 0 0 1 1.5 1.5v.5a.5.5 0 1 1-1 0v-.5a.5.5 0 0 0-.5-.5H13a5 5 0 0 1-10 0h-.5a.5.5 0 0 0-.5.5v.5a.5.5 0 1 1-1 0v-.5A1.5 1.5 0 0 1 2.5 10H3V9H1.5a.5.5 0 0 1 0-1H3V7h-.5A1.5 1.5 0 0 1 1 5.5V5a.5.5 0 0 1 1 0v.5a.5.5 0 0 0 .5.5H3c0-1.364.547-2.601 1.432-3.503l-.41-1.352a.5.5 0 0 1 .333-.623M4 7v4a4 4 0 0 0 3.5 3.97V7zm4.5 0v7.97A4 4 0 0 0 12 11V7zM12 6a4 4 0 0 0-1.334-2.982A3.98 3.98 0 0 0 8 2a3.98 3.98 0 0 0-2.667 1.018A4 4 0 0 0 4 6z" />
                </svg>
            </div>
            <p
                class="mt-2 ms-3 pb-3 font-style: italic font-weight: text-[#009eff] text-6xl font-bold inline-block align-middle ">
                Backlog</p>
            <span class="mt-8 ms-7 min-w-40 h-8 text-sm font-bold bg-amber-100 text-amber-800 flex justify-center items-center rounded-full col-start-4">
                {{ $allBugs->count() }} {{ Str::plural('bug', $allBugs->count()) }}
                ({{ $allBugs->sum('points') }} {{ Str::plural('point', $allBugs->sum('points')) }})
            </span>


            <!-- Dropdown-->
            <div class="ms-60 grid grid-rows-2 gap-1">
                <div>
                    <!-- Dropdown Teams-->
                    <form class="flex w-full">
                         <label for="team" class="text-sm font-medium text-gray-900 pt-2 me-5 ">Team:</label>
                        <select
                            id="team" class="h-10 w-full bg-gray-50 border-gray-300 text-gray-900 text-sm rounded-3xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                            <option selected class="p-center ">All</option>
                            <option value="A">Alpha</option>
                            <option value="B">beta</option>
                            <option value="D">delta</option>
                        </select>
                    </form>
                </div>

                <span>
                    <!-- Dropdown Year and Sprint -->
                    <form class="flex h-10">
                        <label for="year" class="text-sm font-medium text-gray-900 pt-2 me-5">Year:</label>
                        <select
                            id="year" class="ms-2 w-40 bg-gray-50 border-gray-300 text-gray-900 text-sm rounded-3xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                            <option selected class="p-center ">2025</option>
                            <option value="A">2024</option>
                            <option value="B">2023</option>
                            <option value="D">2022</option>
                        </select>
                        <label for="sprint" class="text-sm font-medium text-gray-900 pt-2 ps-5 me-5">Sprint:</label>
                        <select
                            id="sprint" class=" bg-gray-50 border-gray-300 text-gray-900 text-sm rounded-3xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-white dark:border-gray-600 dark:placeholder-gray-400 dark:text-black dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            <option selected class="p-center " value="1 ~ 10">1 ~ 10</option>
                            <option value="11 ~ 20">11 ~ 20</option>
                            <option value="21 ~ 30">21 ~ 30</option>
                            <option value="31 ~ 40">31 ~ 40</option>
                        </select>
                    </form>

                </span>
            </div>
        </div>
        <p class=" text-left ms-5 mt-2 mb-8 text-sm text-gray-600">
            This page displays all backlog bugs from previous sprints. These bugs have been tracked but not resolved in their original sprints.
        </p>
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

                    <div class="col-span-8 text-left h-20 overflow-auto ">โค้ดที่คุณให้มาคือการใช้  Tailwind  Tailwind Tailาจอที่ใช้งาน
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
                        <span class="text-sm text-gray-500">Assign to:</span>

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
    // Update displayed sprints based on filters
    function updateDisplayedSprints() {
                    const sprintRange = document.getElementById('Sprint').textContent;

                    // Parse sprint range (e.g., "Sprint 1 ~ 10" to [1, 10])
                    const rangeMatch = sprintRange.match(/Sprint (\d+) ~ (\d+)/);
                    const startSprint = rangeMatch ? parseInt(rangeMatch[1]) : 1;
                    const endSprint = rangeMatch ? parseInt(rangeMatch[2]) : 10;

                    // Show/hide sprints based on range
                    document.querySelectorAll('.sprint-block').forEach(sprintBlock => {
                        const sprintNumber = parseInt(sprintBlock.dataset.sprintNumber);

                        if (sprintNumber >= startSprint && sprintNumber <= endSprint) {
                            sprintBlock.style.display = 'block';
                        } else {
                            sprintBlock.style.display = 'none';
                        }
                    });
                }
</script>
@endsection