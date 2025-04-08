@extends('layouts.app')

@section('title', $reportName)

@section('page-title', $reportName)

@section('content')
<style>
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
</style>

<div class="max-w-7xl mx-auto">
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold flex items-center">
                {{ $reportName }}
                <span class="ml-2 text-sm bg-primary-100 text-primary-800 py-1 px-2 rounded-full">{{ $boardName }}</span>
                @isset($reportVersion)
                <span class="ml-2 text-sm {{ $reportVersion === 'v1' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }} py-1 px-2 rounded-full">{{ $reportVersion }}</span>
                @endisset
            </h1>
            
            <div class="flex items-center space-x-2">
                <a href="{{ route('sprints.show', $report->sprint->id) }}" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Sprint Reports
                </a>
                
                <button onclick="window.print()" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print
                </button>
                
                @if(auth()->user()->isAdmin())
                <form action="{{ route('sprint-reports.delete', $report->id) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                        onclick="return confirm('Are you sure you want to delete this report?')"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Delete
                    </button>
                </form>
                @endif
            </div>
        </div>
        
        <div class="mt-2 text-sm text-gray-500 flex items-center gap-4">
            <span>
                Created on {{ $report->created_at->format('F d, Y \a\t h:i A') }}
                @if($report->updated_at->gt($report->created_at))
                    · Updated on {{ $report->updated_at->format('F d, Y \a\t h:i A') }}
                @endif
            </span>
            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $report->is_auto_generated ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                {{ $report->is_auto_generated ? 'Auto Generated' : 'Manually Generated' }}
            </span>
        </div>
    </div>

    <!-- Sprint Information Section -->
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
                @if(!empty($notes))
                    <h3 class="text-lg font-medium mb-3">Notes</h3>
                    <div class="prose max-w-none text-sm text-gray-700">
                        {!! nl2br(e($notes)) !!}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Story Points Section -->
    <div class="bg-white shadow rounded-lg p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Story Points</h2>
        
        <div class="overflow-x-auto">
            @if(!empty($teamMembers) || !empty($summaryData))
                <!-- Summary Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-6">
                    <div class="bg-gray-50 rounded-lg p-4 text-center">
                        <h3 class="text-sm font-medium text-gray-500">Plan Point</h3>
                        <p class="text-2xl font-bold text-gray-800">{{ $summaryData['planPoints'] ?? 0 }}</p>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-4 text-center">
                        <h3 class="text-sm font-medium text-blue-500">Actual Point</h3>
                        <p class="text-2xl font-bold text-blue-600">{{ $summaryData['actualPoints'] ?? 0 }}</p>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4 text-center">
                        <h3 class="text-sm font-medium text-green-500">Remain Percent</h3>
                        <p class="text-2xl font-bold text-green-600">{{ $summaryData['remainPercent'] ?? '0%' }}</p>
                    </div>
                    <div class="bg-purple-50 rounded-lg p-4 text-center">
                        <h3 class="text-sm font-medium text-purple-500">Percent</h3>
                        <p class="text-2xl font-bold text-purple-600">{{ $summaryData['percentComplete'] ?? '0%' }}</p>
                    </div>
                    <div class="bg-yellow-50 rounded-lg p-4 text-center">
                        <h3 class="text-sm font-medium text-yellow-500">Point Current Sprint</h3>
                        <p class="text-2xl font-bold text-yellow-600">{{ $summaryData['currentSprintPoints'] ?? 0 }}</p>
                    </div>
                    <div class="bg-red-50 rounded-lg p-4 text-center">
                        <h3 class="text-sm font-medium text-red-500">Actual Point Current Sprint</h3>
                        <p class="text-2xl font-bold text-red-600">{{ $summaryData['actualCurrentSprint'] ?? 0 }}</p>
                    </div>
                </div>
                
                <!-- Board Info -->
                <div class="mb-4">
                    <h3 class="text-lg font-semibold mb-2">
                        Points from {{ $boardName }}
                    </h3>
                    @if(!empty($summaryData['lastUpdated']))
                        <p class="text-xs text-gray-500">{{ $summaryData['lastUpdated'] }}</p>
                    @endif
                </div>
                
                <!-- Team Members Table -->
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
                    <tbody>
                        @forelse($teamMembers as $member)
                            <tr>
                                <td class="py-3 px-4 border-b">{{ $member['name'] ?? 'Unknown' }}</td>
                                <td class="py-3 px-4 border-b text-center">{{ $member['pointPersonal'] ?? 0 }}</td>
                                <td class="py-3 px-4 border-b text-center">{{ $member['pass'] ?? 0 }}</td>
                                <td class="py-3 px-4 border-b text-center">{{ $member['bug'] ?? 0 }}</td>
                                <td class="py-3 px-4 border-b text-center">{{ $member['cancel'] ?? 0 }}</td>
                                <td class="py-3 px-4 border-b text-center">{{ $member['final'] ?? 0 }}</td>
                                <td class="py-3 px-4 border-b text-center">{{ $member['passPercent'] ?? '0%' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-4 px-4 text-center text-gray-500">No team members data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-gray-50 font-semibold">
                        <tr>
                            <td class="py-3 px-4 border-t text-left">Totals</td>
                            <td class="py-3 px-4 border-t text-center">{{ $totals['totalPersonal'] ?? 0 }}</td>
                            <td class="py-3 px-4 border-t text-center">{{ $totals['totalPass'] ?? 0 }}</td>
                            <td class="py-3 px-4 border-t text-center">{{ $totals['totalBug'] ?? 0 }}</td>
                            <td class="py-3 px-4 border-t text-center">{{ $totals['totalCancel'] ?? 0 }}</td>
                            <td class="py-3 px-4 border-t text-center">{{ $totals['totalFinal'] ?? 0 }}</td>
                            <td class="py-3 px-4 border-t text-center">-</td>
                        </tr>
                    </tfoot>
                </table>
            @else
                <div class="py-4 px-4 text-center text-gray-500 bg-gray-50 rounded-lg">
                    No story points data was saved with this report.
                </div>
            @endif
        </div>
    </div>

    <!-- Bug Cards Section -->
    @if(count($bugCards) > 0)
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">Bug Cards ({{ $bugCount }})</h2>
            <div class="mt-4">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bug</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Points</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($bugCards as $bug)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <a href="{{ $bug['url'] ?? '#' }}" class="text-primary-600 hover:text-primary-900" target="_blank">{{ $bug['id'] }}</a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $bug['name'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if(isset($bug['labels']) && count($bug['labels']) > 1)
                                            @foreach($bug['labels'] as $label)
                                                @if($label !== 'Bug')
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
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-50">
                                <th colspan="4" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Bug Points:</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $totalBugPoints }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Backlog Section -->
    @if(count($backlogCards) > 0)
        <div class="bg-gray-50 shadow rounded-lg p-6 mb-8 border-l-4 border-amber-500">
            <h2 class="text-xl font-semibold mb-2 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-amber-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Backlog ({{ $backlogBugCount }})
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
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($backlogCards as $bug)
                                <tr class="bg-amber-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <a href="{{ $bug['url'] ?? '#' }}" class="text-primary-600 hover:text-primary-900" target="_blank">{{ $bug['id'] }}</a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $bug['name'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if(isset($bug['labels']) && count($bug['labels']) > 1)
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
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-100">
                                <th colspan="4" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Backlog Points:</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $backlogTotalPoints }}</th>
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
    @endif
</div>
@endsection 