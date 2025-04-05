@extends('layouts.app')

@section('title', $savedReport->report_name)

@section('page-title', $savedReport->report_name)

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
                {{ $savedReport->report_name }}
                <span class="ml-2 text-sm bg-primary-100 text-primary-800 py-1 px-2 rounded-full">{{ $savedReport->board_name }}</span>
            </h1>
            
            <div class="flex items-center space-x-2">
                <a href="{{ route('saved-reports.index') }}" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Reports
                </a>
                
                <a href="{{ route('saved-reports.edit', $savedReport) }}" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit Report
                </a>
                
                <button onclick="window.print()" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print
                </button>
            </div>
        </div>
        
        <div class="mt-2 text-sm text-gray-500">
            Saved on {{ $savedReport->created_at->format('F d, Y \a\t h:i A') }}
            @if($savedReport->updated_at->gt($savedReport->created_at))
                · Updated on {{ $savedReport->updated_at->format('F d, Y \a\t h:i A') }}
            @endif
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6 mb-8">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">Report Details</h2>
            <div class="text-sm text-gray-500">
                <span class="font-medium">Board:</span> {{ $savedReport->board_name }}
            </div>
        </div>
        
        @if(!empty($savedReport->notes))
            <div class="border-t border-gray-200 pt-4 mt-4">
                <h3 class="text-lg font-medium mb-2">Notes</h3>
                <div class="prose max-w-none">
                    {!! nl2br(e($savedReport->notes)) !!}
                </div>
            </div>
        @endif
    </div>

    <!-- Story Points Section -->
    <div class="bg-white shadow rounded-lg p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Story Points</h2>
        
        <div class="overflow-x-auto">
            @php
                // Get structured data using the model accessor
                $storyPointsData = $savedReport->storyPointsStructured;
                $summaryData = $storyPointsData['summary'] ?? [];
                $teamMembersData = $storyPointsData['teamMembers'] ?? [];
                $totalsData = $storyPointsData['totals'] ?? [];
                $hasStoryPointsData = !empty($teamMembersData) || !empty($summaryData['planPoints']) || !empty($summaryData['actualPoints']);
            @endphp
            
            @if($hasStoryPointsData)
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
                        Points from {{ !empty($summaryData['boardName']) ? $summaryData['boardName'] : 'Sprint' }}
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
                        @forelse($teamMembersData as $member)
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
                            <td class="py-3 px-4 border-t text-center">{{ $totalsData['totalPersonal'] ?? 0 }}</td>
                            <td class="py-3 px-4 border-t text-center">{{ $totalsData['totalPass'] ?? 0 }}</td>
                            <td class="py-3 px-4 border-t text-center">{{ $totalsData['totalBug'] ?? 0 }}</td>
                            <td class="py-3 px-4 border-t text-center">{{ $totalsData['totalCancel'] ?? 0 }}</td>
                            <td class="py-3 px-4 border-t text-center">{{ $totalsData['totalFinal'] ?? 0 }}</td>
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
    <div class="bg-white shadow rounded-lg p-6 mb-8">
        @php
            // Get structured data using the model accessor
            $bugData = $savedReport->bugCardsStructured;
            $bugCards = $bugData['bugCards'] ?? [];
            $bugCount = $bugData['bugCount'] ?? '0 bugs';
            $totalBugPoints = $bugData['totalBugPoints'] ?? 0;
        @endphp
    
        <h2 class="text-xl font-semibold mb-4">Current Bug <span class="text-sm bg-red-100 text-red-800 py-1 px-2 rounded-full">{{ $bugCount }}</span></h2>
        <div class="text-sm text-gray-500 mb-4">
            Total Points: <span class="font-semibold">{{ $totalBugPoints }}</span>
        </div>
        
        <div class="overflow-x-auto">
            @if(!empty($bugCards))
                <div class="flex gap-4 p-4 overflow-x-auto">
                    @foreach($bugCards as $card)
                        <div class="bug-card bg-white rounded-lg shadow-md min-w-[300px] max-w-[300px] {{ $card['priorityClass'] ?? 'priority-none' }}">
                            <div class="p-4 h-full flex flex-col">
                                <div class="flex justify-between items-start">
                                    <div class="font-medium text-gray-900 mb-1">{{ $card['name'] }}</div>
                                    <div class="flex-shrink-0 ml-2">
                                        <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-100 bg-red-600 rounded-full">
                                            {{ $card['points'] }}
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="text-xs text-gray-500 mb-2">
                                    From: {{ $card['list'] }}
                                </div>
                                
                                <div class="text-sm text-gray-700 mt-2 flex-grow">
                                    {{ $card['description'] }}
                                </div>
                                
                                <div class="mt-3 pt-3 border-t border-gray-100">
                                    <div class="text-xs text-gray-500">
                                        {{ $card['members'] }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-4 px-4 text-center text-gray-500 bg-gray-50 rounded-lg">
                    No bug cards data was saved with this report.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Fix potential styling issues
        fixSavedReportDisplay();
        
        // Initialize any charts or interactive elements if they exist
        initializeCharts();
    });
    
    // Function to fix styling issues in the saved report content
    function fixSavedReportDisplay() {
        // Remove any hidden classes from elements that should be visible in this view
        document.querySelectorAll('.hidden').forEach(el => {
            // Only remove hidden class from certain elements that should be visible in the report
            if (el.closest('#story-points-summary') || 
                el.closest('#team-member-points-container') || 
                el.closest('.bug-cards-container')) {
                el.classList.remove('hidden');
            }
        });
        
        // Fix any broken references to images or stylesheets
        document.querySelectorAll('img[src^="data:"]').forEach(img => {
            // Ensure data URLs work properly
            img.style.display = 'inline-block';
        });
        
        // Make sure tables display properly
        document.querySelectorAll('table').forEach(table => {
            table.classList.add('min-w-full', 'border', 'border-gray-200');
        });
    }
    
    // Function to initialize charts if they exist
    function initializeCharts() {
        // If Chart.js instances exist, re-initialize them
        if (window.Chart && document.querySelectorAll('canvas[data-chart-type]').length > 0) {
            document.querySelectorAll('canvas[data-chart-type]').forEach(canvas => {
                // Implementation would depend on how charts are created in the story-points-report.blade.php
                console.log('Found chart canvas:', canvas.id);
            });
        }
    }
</script>
@endsection 