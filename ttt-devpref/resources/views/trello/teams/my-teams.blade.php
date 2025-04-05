@extends('layouts.app')

@section('title', 'My Teams')
@section('page-title', 'My Teams')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-semibold text-gray-900">My Teams</h2>
        <div class="flex items-center space-x-4">
            <a href="{{ route('dashboard') }}" class="text-primary-600 hover:text-primary-800 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Dashboard
            </a>
        </div>
    </div>
    
    @if(count($teams) > 0)
        <!-- Teams Tabs -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6" x-data="{ activeTab: '{{ $teams[0]['id'] }}' }">
            <!-- Tabs Navigation -->
            <div class="border-b border-gray-200 overflow-x-auto">
                <nav class="flex -mb-px whitespace-nowrap">
                    @foreach($teams as $index => $team)
                        <button 
                            @click="activeTab = '{{ $team['id'] }}'" 
                            :class="{ 'border-primary-500 text-primary-600': activeTab === '{{ $team['id'] }}', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== '{{ $team['id'] }}' }"
                            class="py-4 px-6 border-b-2 font-medium text-sm focus:outline-none transition-colors duration-200"
                        >
                            <div class="flex items-center">
                                <span>{{ $team['name'] }}</span>
                                <span class="ml-2 px-2 py-0.5 bg-blue-100 text-xs text-blue-800 rounded-full">{{ count($team['members']) }}</span>
                            </div>
                        </button>
                    @endforeach
                </nav>
            </div>
            
            <!-- Tabs Content -->
            <div>
                @foreach($teams as $team)
                    <div x-show="activeTab === '{{ $team['id'] }}'" class="p-4">
                        <div class="mb-4 flex items-start justify-between">
                            <div>
                                <h3 class="text-lg font-medium text-primary-600">{{ $team['name'] }}</h3>
                                @if(isset($team['desc']) && !empty($team['desc']))
                                    <p class="mt-1 text-sm text-gray-500">{{ $team['desc'] }}</p>
                                @endif
                                @if(isset($team['organization']))
                                    <p class="mt-1 text-sm text-gray-600">
                                        <span class="font-medium">Workspace:</span> {{ $team['organization']['displayName'] }}
                                    </p>
                                @endif
                            </div>
                            <div class="flex space-x-2">
                                @if(isset($team['url']))
                                    <a href="{{ $team['url'] }}" target="_blank" class="text-sm text-primary-600 hover:bg-gray-100 border border-primary-600 py-1 px-3 rounded flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                        Trello
                                    </a>
                                @endif
                            </div>
                        </div>
                        
                        <div class="mt-6 bg-gray-50 rounded-md p-4">
                            <h4 class="text-base font-medium text-gray-900 mb-3">Team Members</h4>
                            <div class="overflow-hidden">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse($team['members'] as $member)
                                            <tr class="{{ $member['fullName'] == Auth::user()->name ? 'bg-blue-50' : '' }}">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0 h-10 w-10">
                                                            <div class="h-10 w-10 rounded-full bg-primary-100 flex items-center justify-center {{ $member['fullName'] == Auth::user()->name ? 'ring-2 ring-blue-400' : '' }}">
                                                                <span class="text-primary-600 font-semibold text-lg">{{ substr($member['fullName'], 0, 1) }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="ml-4">
                                                            <div class="text-sm font-medium text-gray-900">
                                                                {{ $member['fullName'] }}
                                                                @if($member['fullName'] == Auth::user()->name)
                                                                    <span class="ml-2 px-2 py-0.5 bg-blue-100 text-blue-800 text-xs rounded-full">Me</span>
                                                                @endif
                                                            </div>
                                                            <div class="text-xs text-gray-500">{{ $member['username'] }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">
                                                        @if(isset($member['email']) && !empty($member['email']))
                                                            {{ $member['email'] }}
                                                        @else
                                                            <span class="text-gray-500 italic">Email not found</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                                    @if($member['isRegistered'] && isset($member['role']))
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
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                                    No members found for this team.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="bg-white shadow overflow-hidden sm:rounded-md p-6 text-center">
            <p class="text-gray-500">You are not a member of any Trello teams.</p>
            <p class="mt-1 text-sm">If you believe this is an error, please contact your administrator.</p>
        </div>
    @endif
    
    <div class="mt-6 bg-white shadow overflow-hidden sm:rounded-md p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">About My Teams</h3>
        <div class="text-sm text-gray-500 space-y-2">
            <p>This page displays all Trello teams that you are a member of. In this interface, each Trello board is treated as a team.</p>
            <p>Team membership is determined by matching your name in our system with member names in Trello.</p>
            <p>Your profile is highlighted with a <span class="px-2 py-0.5 bg-blue-100 text-blue-800 text-xs rounded-full">Me</span> indicator and a blue border to help you identify yourself in each team.</p>
            <p>Note: Trello may not provide email addresses for all members due to privacy settings. In such cases, "Email not found" will be displayed.</p>
        </div>
    </div>
</div>
@endsection