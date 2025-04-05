@extends('layouts.app')

@section('title', 'Create New Report')

@section('page-title', 'Create New Report')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold">Create New Report</h1>
            
            <div>
                <a href="{{ route('saved-reports.index') }}" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Reports
                </a>
            </div>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">There were errors with your submission</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <ul class="list-disc pl-5 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('saved-reports.store') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label for="report_name" class="block text-sm font-medium text-gray-700">Report Name</label>
                    <input type="text" name="report_name" id="report_name" value="{{ old('report_name') }}" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                </div>
                
                <div>
                    <label for="board_name" class="block text-sm font-medium text-gray-700">Board Name</label>
                    <input type="text" name="board_name" id="board_name" value="{{ old('board_name', session('selected_board_name', '')) }}" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                </div>
                
                <!-- Hidden board_id field -->
                <input type="hidden" name="board_id" id="board_id" value="{{ old('board_id', session('selected_board', '')) }}">
                
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                    <textarea name="notes" id="notes" rows="4" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ old('notes') }}</textarea>
                    <p class="mt-1 text-sm text-gray-500">Add any notes or context about this report.</p>
                </div>
                
                <!-- Hidden fields to save story_points_data and bug_cards_data -->
                <input type="hidden" name="story_points_data" id="story_points_data" value="{{ session('current_story_points_data', '') }}">
                <input type="hidden" name="bug_cards_data" id="bug_cards_data" value="{{ session('current_bug_cards_data', '') }}">
                
                <div class="pt-4">
                    <div class="flex justify-end">
                        <a href="{{ route('story.points.report') }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 mr-2">
                            Cancel
                        </a>
                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            Save Report
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Capture structured data from the report
        captureReportData();
        
        // Add form submit event listener for debugging
        document.querySelector('form').addEventListener('submit', function(e) {
            const storyPointsData = document.getElementById('story_points_data').value;
            const bugCardsData = document.getElementById('bug_cards_data').value;
            
            console.log('Form submission - data status:', {
                storyPointsDataLength: storyPointsData ? storyPointsData.length : 0,
                bugCardsDataLength: bugCardsData ? bugCardsData.length : 0,
                storyPointsDataIsEmpty: !storyPointsData,
                bugCardsDataIsEmpty: !bugCardsData
            });
            
            // Only for debugging - ensure data is submitted
            if (!storyPointsData || !bugCardsData) {
                console.warn('⚠️ Warning: Some data fields are empty! This might cause issues.');
            }
        });
    });
    
    function captureReportData() {
        try {
            // Check if we already have data from the session
            const storyPointsField = document.getElementById('story_points_data');
            const bugCardsField = document.getElementById('bug_cards_data');
            
            if (storyPointsField.value && bugCardsField.value) {
                console.log('Using pre-populated data from session:', {
                    storyPointsLength: storyPointsField.value.length,
                    bugCardsLength: bugCardsField.value.length
                });
                return; // Don't try to capture data if we already have it
            }
            
            // 1. Capture summary statistics
            const summaryData = {
                planPoints: parseInt(document.getElementById('plan-points')?.value || '0'),
                actualPoints: parseInt(document.getElementById('actual-points')?.textContent || '0'),
                remainPercent: document.getElementById('remain-percent')?.textContent || '0%',
                percentComplete: document.getElementById('percent-complete')?.textContent || '0%',
                currentSprintPoints: parseInt(document.getElementById('current-sprint-points')?.textContent || '0'),
                actualCurrentSprint: parseInt(document.getElementById('actual-current-sprint')?.textContent || '0'),
                boardName: document.getElementById('board-name-display')?.textContent || '',
                lastUpdated: document.getElementById('last-updated')?.textContent || ''
            };
            
            // Debug: Log which elements we found
            console.log('Summary element search results:', {
                'plan-points': !!document.getElementById('plan-points'),
                'actual-points': !!document.getElementById('actual-points'),
                'remain-percent': !!document.getElementById('remain-percent'),
                'percent-complete': !!document.getElementById('percent-complete'),
                'current-sprint-points': !!document.getElementById('current-sprint-points'),
                'actual-current-sprint': !!document.getElementById('actual-current-sprint'),
                'board-name-display': !!document.getElementById('board-name-display'),
                'last-updated': !!document.getElementById('last-updated')
            });
            
            // 2. Capture team member data
            const teamMemberData = [];
            const memberRows = document.querySelectorAll('#team-members-table-body tr');
            
            memberRows.forEach(row => {
                const cells = row.querySelectorAll('td');
                if (cells.length >= 7) {
                    teamMemberData.push({
                        name: cells[0].textContent.trim(),
                        pointPersonal: parseFloat(cells[1].textContent) || 0,
                        pass: parseFloat(cells[2].textContent) || 0,
                        bug: parseFloat(cells[3].textContent) || 0,
                        cancel: parseFloat(cells[4].textContent) || 0,
                        final: parseFloat(cells[5].textContent) || 0,
                        passPercent: cells[6].textContent.trim()
                    });
                }
            });
            
            console.log('Found team member rows:', memberRows.length);
            
            // 3. Capture totals
            const totalsData = {
                totalPersonal: parseFloat(document.getElementById('total-personal')?.textContent || '0'),
                totalPass: parseFloat(document.getElementById('total-pass')?.textContent || '0'),
                totalBug: parseFloat(document.getElementById('total-bug')?.textContent || '0'),
                totalCancel: parseFloat(document.getElementById('total-cancel')?.textContent || '0'),
                totalFinal: parseFloat(document.getElementById('total-final')?.textContent || '0')
            };
            
            // 4. Capture bug cards data
            const bugCards = [];
            const bugCardElements = document.querySelectorAll('.bug-card');
            
            bugCardElements.forEach(card => {
                const nameElement = card.querySelector('.font-medium.text-gray-900');
                const pointElement = card.querySelector('.bg-red-600.rounded-full');
                const listElement = card.querySelector('.text-xs.text-gray-500');
                const descriptionElement = card.querySelector('.description-content');
                const memberElement = card.querySelector('.text-xs.text-gray-500.mt-1');
                
                bugCards.push({
                    name: nameElement ? nameElement.textContent.trim() : 'Unnamed Card',
                    points: pointElement ? parseInt(pointElement.textContent) || 0 : 0,
                    list: listElement ? listElement.textContent.replace('From:', '').trim() : '',
                    description: descriptionElement ? descriptionElement.textContent.trim() : '',
                    members: memberElement ? memberElement.textContent.trim() : 'Not assigned',
                    priorityClass: Array.from(card.classList).find(c => c.startsWith('priority-')) || 'priority-none'
                });
            });
            
            console.log('Found bug cards:', bugCardElements.length);
            
            // Compile all data into a single JSON object
            const reportData = {
                summary: summaryData,
                teamMembers: teamMemberData,
                totals: totalsData,
                bugCards: bugCards,
                bugCount: document.getElementById('bug-count')?.textContent || '0 bugs',
                totalBugPoints: document.getElementById('total-bug-points')?.textContent || '0'
            };
            
            // Store the structured data in the hidden fields
            const storyPointsJson = JSON.stringify({
                summary: reportData.summary,
                teamMembers: reportData.teamMembers,
                totals: reportData.totals
            });
            
            const bugCardsJson = JSON.stringify({
                bugCards: reportData.bugCards,
                bugCount: reportData.bugCount,
                totalBugPoints: reportData.totalBugPoints
            });
            
            document.getElementById('story_points_data').value = storyPointsJson;
            document.getElementById('bug_cards_data').value = bugCardsJson;
            
            // Fallback: If we couldn't find any data, create a minimal set of data
            // This ensures we always have something to save
            if (reportData.teamMembers.length === 0 && reportData.bugCards.length === 0) {
                console.warn('No data found on page - using fallback data');
                
                // Create fallback data
                const fallbackData = {
                    summary: {
                        planPoints: 0,
                        actualPoints: 0,
                        remainPercent: '0%',
                        percentComplete: '0%',
                        currentSprintPoints: 0,
                        actualCurrentSprint: 0,
                        boardName: document.getElementById('board-selector')?.options[document.getElementById('board-selector')?.selectedIndex]?.text || 'Unknown Board',
                        lastUpdated: 'Last updated: ' + new Date().toLocaleString()
                    },
                    teamMembers: [],
                    totals: {
                        totalPersonal: 0,
                        totalPass: 0,
                        totalBug: 0,
                        totalCancel: 0,
                        totalFinal: 0
                    }
                };
                
                document.getElementById('story_points_data').value = JSON.stringify(fallbackData);
                document.getElementById('bug_cards_data').value = JSON.stringify({
                    bugCards: [],
                    bugCount: '0 bugs',
                    totalBugPoints: 0
                });
            }
            
            console.log('Captured report data:', {
                storyPointsLength: storyPointsJson.length,
                bugCardsLength: bugCardsJson.length,
                teamMembersCount: teamMemberData.length,
                bugCardsCount: bugCards.length
            });
            
            // Check if data was actually stored in the fields
            setTimeout(() => {
                const finalStoryData = document.getElementById('story_points_data').value;
                const finalBugData = document.getElementById('bug_cards_data').value;
                
                console.log('Final field values:', {
                    storyPointsSet: finalStoryData.length > 0,
                    bugCardsSet: finalBugData.length > 0
                });
            }, 500);
        } catch (error) {
            console.error('Error capturing report data:', error);
        }
    }
</script>
@endsection 