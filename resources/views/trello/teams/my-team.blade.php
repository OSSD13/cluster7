@extends('layouts.app')

@section('title', 'Team Details - ' . ($board['name'] ?? 'Trello Team'))
@section('page-title', 'Team Details')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6 flex justify-between items-center">
        <a href="{{ route('my-teams.index') }}" class="text-primary-600 hover:text-primary-800 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to My Teams
        </a>
        @if(isset($board['url']))
            <a href="{{ $board['url'] }}" target="_blank" class="bg-primary-500 hover:bg-primary-600 text-white py-2 px-4 rounded-md flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
                Open in Trello
            </a>
        @endif
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
        <div class="px-4 py-5 sm:px-6 flex items-center">
            <div class="flex-shrink-0 h-16 w-16 rounded overflow-hidden bg-gray-100 flex items-center justify-center mr-4">
                <svg class="h-10 w-10 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                </svg>
            </div>
            <div>
                <h2 class="text-2xl font-semibold text-gray-900">{{ $board['name'] }}</h2>
                @if(isset($board['desc']) && !empty($board['desc']))
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">{{ $board['desc'] }}</p>
                @endif
                @if(isset($organization))
                    <p class="mt-1 text-sm text-gray-600">
                        <span class="font-medium">Workspace:</span> {{ $organization['displayName'] ?? $organization['name'] }}
                    </p>
                @endif
            </div>
        </div>
    </div>

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
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trello Username</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($members as $member)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-primary-100 flex items-center justify-center">
                                                <span class="text-primary-600 font-semibold text-lg">{{ substr($member['fullName'], 0, 1) }}</span>
                                            </div>
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
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if(isset($member['isRegistered']) && $member['isRegistered'] && isset($member['role']))
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $member['role'] == 'admin' ? 'bg-red-100 text-red-800' : 
                                           ($member['role'] == 'tester' ? 'bg-blue-100 text-blue-800' : 
                                            'bg-green-100 text-green-800') }}">
                                            {{ ucfirst($member['role']) }}
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            External User
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
        <h3 class="text-lg font-medium text-gray-900 mb-4">About Team Members</h3>
        <div class="text-sm text-gray-500 space-y-2">
            <p>
                This page shows all members of this Trello team and their roles in the system.
                A member appears as "External User" if they are not registered in our system.
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