@extends('layouts.app')

@section('title', 'Edit Sprint Report')

@section('page-title', 'Edit Sprint Report')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('sprints.show', $report->sprint->id) }}" class="text-primary-600 hover:text-primary-900 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Sprint Reports
        </a>
    </div>

    <form action="{{ route('sprint-reports.update', $report->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">Sprint Information</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-medium mb-3">Sprint #{{ $sprintInfo['number'] }}</h3>
                    <dl class="grid grid-cols-2 gap-x-4 gap-y-2">
                        <dt class="text-sm font-medium text-gray-500">Start Date:</dt>
                        <dd class="text-sm text-gray-900">{{ $sprintInfo['startDate'] }}</dd>
                        
                        <dt class="text-sm font-medium text-gray-500">End Date:</dt>
                        <dd class="text-sm text-gray-900">{{ $sprintInfo['endDate'] }}</dd>
                        
                        <dt class="text-sm font-medium text-gray-500">Progress:</dt>
                        <dd class="text-sm text-gray-900">{{ number_format($sprintInfo['progress'], 1) }}%</dd>
                    </dl>
                </div>
                
                <div>
                    <h3 class="text-lg font-medium mb-3">Notes</h3>
                    <div class="mb-4">
                        <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea id="notes" name="notes" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">{{ $notes }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Story Points Section -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">Story Points</h2>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Team Member</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Completed</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">In Progress</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Not Started</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($teamMembers as $index => $member)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $member['name'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="number" name="story_points_data[teamMembers][{{ $index }}][completed]" 
                                           value="{{ $member['completed'] ?? 0 }}" 
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="number" name="story_points_data[teamMembers][{{ $index }}][inProgress]" 
                                           value="{{ $member['inProgress'] ?? 0 }}" 
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="number" name="story_points_data[teamMembers][{{ $index }}][notStarted]" 
                                           value="{{ $member['notStarted'] ?? 0 }}" 
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $member['total'] ?? 0 }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Bug Cards Section -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">Bug Cards ({{ $bugCount }})</h2>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Points</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">List</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Members</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($bugCards as $index => $bug)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <input type="text" name="bug_cards_data[bugCards][{{ $index }}][name]" 
                                           value="{{ $bug['name'] }}" 
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="number" name="bug_cards_data[bugCards][{{ $index }}][points]" 
                                           value="{{ $bug['points'] }}" 
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="text" name="bug_cards_data[bugCards][{{ $index }}][list]" 
                                           value="{{ $bug['list'] }}" 
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                </td>
                                <td class="px-6 py-4">
                                    <textarea name="bug_cards_data[bugCards][{{ $index }}][description]" 
                                              rows="2" 
                                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">{{ $bug['description'] }}</textarea>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="text" name="bug_cards_data[bugCards][{{ $index }}][members]" 
                                           value="{{ $bug['members'] }}" 
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                Save Changes
            </button>
        </div>
    </form>
</div>
@endsection 