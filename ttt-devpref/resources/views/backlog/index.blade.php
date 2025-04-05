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
                <h2 class="text-xl font-semibold mb-4">All Backlog Bugs</h2>

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
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sprint</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($allBugs as $bug)
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
                            <tr class="bg-gray-50">
                                <th colspan="4" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Bugs: {{ $allBugs->count() }}</th>
                                <th colspan="3" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Points: {{ $allBugs->sum('points') }}</th>
                            </tr>
                        </tfoot>
                    </table>
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
</script>
@endsection 