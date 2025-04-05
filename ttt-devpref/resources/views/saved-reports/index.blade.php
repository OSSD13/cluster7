@extends('layouts.app')

@section('title', isset($filter) && $filter === 'sprint' ? 'On-going Sprint Reports' : 'Saved Reports')

@section('page-title', isset($filter) && $filter === 'sprint' ? 'On-going Sprint Reports' : 'Saved Reports')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">{{ isset($filter) && $filter === 'sprint' ? 'On-going Sprint Reports' : 'Saved Reports' }}</h1>
        
        <div>
            <a href="{{ route('story.points.report') }}" class="bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Create New Report
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    @if(count($reports) > 0)
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <ul class="divide-y divide-gray-200">
                @foreach($reports as $report)
                    <li>
                        <div class="block hover:bg-gray-50">
                            <div class="px-4 py-4 sm:px-6">
                                <div class="flex items-center justify-between">
                                    <div class="truncate">
                                        <div class="flex text-sm">
                                            <p class="font-medium text-primary-600 truncate">{{ $report->report_name }}</p>
                                            <p class="ml-1 flex-shrink-0 font-normal text-gray-500">from {{ $report->board_name }}</p>
                                        </div>
                                        <div class="mt-2 flex">
                                            <div class="flex items-center text-sm text-gray-500">
                                                <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                <span>
                                                    Created on <time datetime="{{ $report->created_at->format('Y-m-d') }}">{{ $report->created_at->format('M d, Y') }}</time>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex flex-shrink-0 ml-2">
                                        <a href="{{ route('saved-reports.show', $report) }}" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-primary-700 bg-primary-100 hover:bg-primary-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 mr-2">
                                            View
                                        </a>
                                        <a href="{{ route('saved-reports.edit', $report) }}" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 mr-2">
                                            Edit
                                        </a>
                                        <form action="{{ route('saved-reports.destroy', $report) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this report?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="mt-4">
            {{ $reports->appends(['filter' => $filter])->links() }}
        </div>
    @else
        <div class="bg-white shadow rounded-lg p-6 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">{{ isset($filter) && $filter === 'sprint' ? 'No on-going sprint reports found' : 'No saved reports yet' }}</h3>
            <p class="text-gray-500 mb-4">{{ isset($filter) && $filter === 'sprint' ? 'Generate a new sprint report to track your current sprint progress.' : 'Create a new report to save it for future reference.' }}</p>
            <a href="{{ route('story.points.report') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                Go to Report Page
            </a>
        </div>
    @endif
</div>
@endsection 