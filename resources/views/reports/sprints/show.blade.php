@extends('layouts.app')

@section('title', 'Sprint #' . $sprint->sprint_number . ' Reports')

@section('page-title', 'Sprint #' . $sprint->sprint_number . ' Reports')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6">
        <!--ask if is admin go to route sprint index if not go to user report-->
        <a href="{{ auth()->user()->isAdmin() ? route('sprints.index') : route('reports') }}" class="text-primary-600 hover:text-primary-900 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to {{ auth()->user()->isAdmin() ? 'All sprints' : 'All Reports' }}
        </a>
    </div>

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Sprint #{{ $sprint->sprint_number }} Reports</h1>
        
        <div class="flex space-x-3">
            <a href="{{ route('backlog.index') }}" class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                View Backlog
            </a>
            
            <a href="{{ route('story.points.report') }}" class="bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                View Current Sprint Report
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    <!-- Sprint Info Card -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Sprint Details</h2>
                <dl class="mt-2 text-sm text-gray-600">
                    <div class="mt-1">
                        <dt class="inline font-medium text-gray-500">Status:</dt>
                        <dd class="inline ml-1">
                            <span class="px-2 py-1 text-xs font-medium rounded-full 
                                {{ $sprint->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                  ($sprint->status === 'active' ? 'bg-blue-100 text-blue-800' : 
                                   'bg-gray-100 text-gray-800') }}">
                                {{ ucfirst($sprint->status) }}
                            </span>
                        </dd>
                    </div>
                    <div class="mt-1">
                        <dt class="inline font-medium text-gray-500">Start Date:</dt>
                        <dd class="inline ml-1">{{ $sprint->formatted_start_date }}</dd>
                    </div>
                    <div class="mt-1">
                        <dt class="inline font-medium text-gray-500">End Date:</dt>
                        <dd class="inline ml-1">{{ $sprint->formatted_end_date }}</dd>
                    </div>
                    <div class="mt-1">
                        <dt class="inline font-medium text-gray-500">Duration:</dt>
                        <dd class="inline ml-1">{{ $sprint->duration }} days</dd>
                    </div>
                </dl>
            </div>
            
            <div class="col-span-3">
                <h2 class="text-lg font-semibold text-gray-900">Sprint Progress</h2>
                <div class="mt-4">
                    <div class="flex items-center justify-between text-sm text-gray-500 mb-1">
                        <span>{{ $sprint->days_elapsed }} of {{ $sprint->duration }} days elapsed</span>
                        <span>{{ number_format($sprint->progress_percentage, 1) }}% complete</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-4">
                        <div class="bg-primary-600 h-4 rounded-full" style="width: {{ $sprint->progress_percentage }}%"></div>
                    </div>
                    
                    <!-- Day markers -->
                    <div class="relative h-6 mt-1">
                        @for ($i = 0; $i <= $sprint->duration; $i++)
                            @if($i % 2 == 0 || $i == $sprint->duration) <!-- Show markers every other day -->
                                <div class="absolute" style="left: {{ ($i / $sprint->duration) * 100 }}%">
                                    <div class="h-2 border-l border-gray-300"></div>
                                    <div class="text-xs text-gray-500 -ml-1">{{ $i }}</div>
                                </div>
                            @endif
                        @endfor
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reports List -->
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-lg font-semibold mb-4">
            Available Reports ({{ $sprint->reports->count() }})
        </h2>

        @if($sprint->reports->count() > 0)
            <!-- Team tabs -->
            <div class="border-b border-gray-200 mb-6">
                <nav class="-mb-px flex space-x-8" aria-label="Teams">
                    @foreach($reportsByTeam as $teamName => $reports)
                        <button 
                            class="team-tab whitespace-nowrap py-4 px-1 border-b-2 {{ $loop->first ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500' }} font-medium text-sm hover:text-gray-700 hover:border-gray-200 focus:outline-none" 
                            data-team="{{ $teamName }}"
                            onclick="showTeamReports('{{ \Illuminate\Support\Str::slug($teamName) }}')">
                            {{ $teamName }} <span class="ml-2 py-0.5 px-2.5 text-xs font-medium rounded-full bg-blue-100 text-blue-800">{{ count($reports) }}</span>
                        </button>
                    @endforeach
                </nav>
            </div>

            <!-- Team reports -->
            @foreach($reportsByTeam as $teamName => $reports)
                <div class="team-reports" id="team-{{ \Illuminate\Support\Str::slug($teamName) }}" style="display: none;">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Report Name</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Version</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created By</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Created</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($reports as $report)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $report->report_name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ isset($reportVersions[$report->id]) && $reportVersions[$report->id] === 'v1' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                                {{ $reportVersions[$report->id] ?? 'Unknown' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">{{ $report->user->name ?? 'System' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">{{ $report->formatted_created_at }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $report->is_auto_generated ? 'bg-yellow-100 text-yellow-800' : 'bg-purple-100 text-purple-800' }}">
                                                {{ $report->is_auto_generated ? 'Auto' : 'Manual' }}
                                            </span>
                                        </td>
                                        <!-- Actions -->
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('sprint-reports.show', $report->id) }}" class="text-primary-600 hover:text-primary-900 mr-3">View</a>
                                            <a href="{{ route('reports.print', $report->id) }}" target="_blank" class="text-green-600 hover:text-green-900 mr-3">Print</a>
                                            <!--if user is admin show edit and delete-->
                                            @if((auth()->user()->isAdmin() ) || (auth()->user()->role === 'tester'))
                                            <form action="{{ route('sprint-reports.delete', $report->id) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this report?')">Delete</button>
                                            </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
            
            <!-- JavaScript to handle switching tabs -->
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Show the first team by default
                    const teams = @json(array_keys($reportsByTeam));
                    if (teams.length > 0) {
                        // Convert team name to a slug for use as ID
                        const firstTeamSlug = teams[0].replace(/[^a-z0-9]/gi, '-').toLowerCase();
                        showTeamReports(firstTeamSlug); 
                    }
                });
                
                function showTeamReports(teamName) {
                    // Hide all team reports
                    document.querySelectorAll('.team-reports').forEach(element => {
                        element.style.display = 'none';
                    });
                    
                    // Show selected team reports
                    const teamElement = document.getElementById('team-' + teamName.replace(/[^a-z0-9]/gi, '-').toLowerCase());
                    if (teamElement) {
                        teamElement.style.display = 'block';
                    }
                    
                    // Update active tab
                    document.querySelectorAll('.team-tab').forEach(tab => {
                        const sluggedTabTeam = tab.getAttribute('data-team').replace(/[^a-z0-9]/gi, '-').toLowerCase();
                        if (sluggedTabTeam === teamName) {
                            tab.classList.add('border-primary-500', 'text-primary-600');
                            tab.classList.remove('border-transparent', 'text-gray-500');
                        } else {
                            tab.classList.remove('border-primary-500', 'text-primary-600');
                            tab.classList.add('border-transparent', 'text-gray-500');
                        }
                    });
                }
            </script>
        @else
            <div class="text-center py-8">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                @if(auth()->user()->isAdmin()|| auth()->user()->role === 'tester')
                <h3 class="text-lg font-medium text-gray-900 mb-2">No reports available for this sprint</h3>
                <p class="text-gray-500 mb-4">Generate a new report to track your sprint progress.</p>
                <a href="{{ route('story.points.report') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    View Current Sprint Report
                </a>
                @else
                <h3 class="text-lg font-medium text-gray-900 mb-2">No reports available for this sprint</h3>
                <p class="text-gray-500 mb-4">Generate a new report to track your sprint progress.</p>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection 