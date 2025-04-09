@extends('layouts.app')

@section('title', 'Team Members - ' . ($organization['displayName'] ?? $organization['name'] ?? 'Trello Team'))
@section('page-title', 'Team Members')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6 flex justify-between items-center">
        <a href="{{ route('trello.teams.index') }}" class="text-primary-600 hover:text-primary-800 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Teams
        </a>
        <a href="{{ route('trello.teams.refresh') }}" class="bg-primary-500 hover:bg-primary-600 text-white py-2 px-4 rounded-md flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Refresh Data
        </a>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
        <div class="px-4 py-5 sm:px-6 flex items-center">
            <div class="flex-shrink-0 h-16 w-16 rounded overflow-hidden bg-gray-100 flex items-center justify-center mr-4">
                @if(isset($organization['logoHash']))
                    <img src="https://trello-logos.s3.amazonaws.com/{{ $organization['id'] }}/{{ $organization['logoHash'] }}/170.png" 
                         alt="{{ $organization['displayName'] ?? $organization['name'] }}" 
                         class="h-full w-full object-cover">
                @else
                    <svg class="h-10 w-10 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                @endif
                
            </div>
            <div>
                <h2 class="text-2xl font-semibold text-gray-900">{{ $organization['displayName'] ?? $organization['name'] ?? 'Trello Team' }}</h2>
                @if(isset($organization['desc']) && !empty($organization['desc']))
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">{{ $organization['desc'] }}</p>
                @endif
                @if(isset($organization['website']) && !empty($organization['website']))
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">
                        <a href="{{ $organization['website'] }}" target="_blank" class="text-primary-600 hover:underline flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                            </svg>
                            {{ $organization['website'] }}
                        </a>
                    </p>
                @endif
            </div>
        </div>
    </div>

    <!-- Boards Section -->
    @if(count($boards) > 0)
    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Team Boards</h3>
            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">{{ count($boards) }} Board{{ count($boards) != 1 ? 's' : '' }}</span>
        </div>
        
        <div class="border-t border-gray-200">+-
            <ul role="list" class="divide-y divide-gray-200">
                @foreach($boards as $board)
                    <li>
                        <a href="{{ route('trello.boards.show', $board['id']) }}" class="block hover:bg-gray-50">
                            <div class="px-4 py-4 sm:px-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 rounded bg-gray-100 flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                                        </svg>
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <div class="text-sm font-medium text-primary-600 truncate">
                                            {{ $board['name'] }}
                                        </div>
                                        <div class="mt-1 flex items-center text-sm text-gray-500">
                                            <span class="truncate">{{ $board['desc'] ?? 'No description' }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <!-- Members Section -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Team Members</h3>
            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">{{ count($members) }} Member{{ count($members) != 1 ? 's' : '' }}</span>
        </div>
        
        <div class="border-t border-gray-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trello Username</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($members as $member)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            @if(isset($member['avatarUrl']) && !empty($member['avatarUrl']))
                                                <img class="h-10 w-10 rounded-full" src="{{ $member['avatarUrl'] }}/50.png" alt="{{ $member['fullName'] }}">
                                            @else
                                                <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                                    <svg class="h-6 w-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $member['fullName'] }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        @if(isset($member['email']) && !empty($member['email']))
                                            {{ $member['email'] }}
                                        @else
                                            <span class="text-gray-500 italic">not found</span>
                                        @endif
                                    </div>
                                </td>
                                <!-- สถานะการลงทะเบียน -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if(isset($member['isRegistered']) && $member['isRegistered'])
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Registered
                                        </span>
                                        @if(isset($member['role']))
                                            <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $member['role'] == 'admin' ? 'bg-red-100 text-red-800' : 
                                               ($member['role'] == 'tester' ? 'bg-blue-100 text-blue-800' : 
                                                ($member['role'] == 'project_manager' ? 'bg-purple-100 text-purple-800' :
                                                ($member['role'] == 'dev' ? 'bg-green-100 text-green-800' :
                                                'bg-gray-100 text-gray-800'))) }}">
                                                {{ ucfirst(str_replace('_', ' ', $member['role'])) }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            Not Registered
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $member['username'] }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                    No members found for this team.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-6 bg-white shadow overflow-hidden sm:rounded-md p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">About Member Registration</h3>
        <div class="text-sm text-gray-500 space-y-2">
            <p>
                This page shows all members of the Trello team and their registration status in our system.
                A member is considered "registered" if their full name in Trello matches with a user name in our system.
            </p>
            <p>
                The email column shows the member's email address from Trello if available. 
                For registered users, it shows their email from our system.
            </p>
            <p>
                Note: Trello may not provide email addresses for all members due to privacy settings.
                In such cases, "not found" will be displayed in the email column.
            </p>
        </div>
    </div>
</div>
@endsection