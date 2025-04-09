@extends('layouts.app')

@section('title', 'User Management')
@section('page-title', 'User Management')

@section('content')
<div class="rounded-[2vw] h-full w-full bg-gray-100 px-5 py-2">
<div class="max-w-7xl mx-auto">
    <div class="mb-6 pt-7 flex items-center">
        <div class="w-20 h-20 rounded-full bg-sky-100 flex justify-center items-center ">
            <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" fill="#13A7FD" class="bi bi-gear" viewBox="0 0 16 16">
                <path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492M5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0"></path>
                <path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115z"></path>
            </svg>
        </div>
        <h2 class="pt-1 ml-3 text-[#13A7FD] text-6xl font-bold italic">User Management</h2>
    </div>
    
    <!-- Tabs -->
    <div class="mb-4 border-b text-gray-500  border-gray-200">
        <ul class="flex flex-wrap -mb-px text-lg font-medium text-center" id="tabs" role="tablist">
            <li class="mr-2" role="presentation">
                <button 
                    class="inline-block p-4 rounded-t-lg border-b-2 border-transparent hover:text-[#13A7FD] text-[#13A7FD] flex items-center" 
                    id="all-users-tab" 
                    data-tab-target="all-users-content" 
                    type="button" 
                    role="tab" 
                    aria-controls="all-users" 
                    aria-selected="true"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 "  fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round"  stroke-linejoin="round"  stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    All Users 
                </button>
            </li>
            <li class="mr-2 " role="presentation">
                <button 
                    class="inline-block p-4 rounded-t-lg border-b-2 border-transparent hover:text-[#13A7FD] hover:text-[#13A7FD] flex items-center" 
                    id="pending-users-tab" 
                    data-tab-target="pending-users-content" 
                    type="button" 
                    role="tab" 
                    aria-controls="pending-users" 
                    aria-selected="false"
                >
                    <svg xmlns="http://www.w3.org/2000/svg"  class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke=currentColor>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Pending Users
                </button>
            </li>
        </ul>
    </div>
    
    <!-- Tab Contents -->
    <div class="tab-content">
        <!-- All Users Tab Content -->
        <div class="tab-pane active" id="all-users-content" role="tabpanel" aria-labelledby="all-users-tab">
            <div class="bg-white shadow-md rounded-xl overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Name
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Email
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Role
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Teams
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Created At
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($users as $user)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-primary-100 rounded-full flex items-center justify-center">
                                            <span class="text-primary-600 font-semibold text-lg">{{ substr($user->name, 0, 1) }}</span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $user->name }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $roleBgColor = 'bg-gray-100';
                                        $roleTextColor = 'text-gray-800';
                                        
                                        if ($user->isAdmin()) {
                                            $roleBgColor = 'bg-red-100';
                                            $roleTextColor = 'text-red-800';
                                        } elseif ($user->isTester()) {
                                            $roleBgColor = 'bg-blue-100';
                                            $roleTextColor = 'text-blue-800';
                                            //edit color Developer and Project manager
                                        } elseif ($user->isDeveloper()) {
                                            $roleBgColor = 'bg-green-100';
                                            $roleTextColor = 'text-green-800';
                                        } elseif ($user->isProjectManager()) {
                                            $roleBgColor = 'bg-purple-100';
                                            $roleTextColor = 'text-purple-800';
                                        }
                                    @endphp
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $roleBgColor }} {{ $roleTextColor }}">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-wrap gap-2">
                                        @if(isset($userTeamsMap[$user->id]) && count($userTeamsMap[$user->id]) > 0)
                                            @foreach($userTeamsMap[$user->id] as $team)
                                                <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-700 rounded-full">
                                                    {{ $team['name'] }}
                                                </span>
                                            @endforeach
                                        @else
                                            <span class="text-xs text-gray-500">No teams</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500">{{ $user->created_at->format('M d, Y') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end mr-4 space-x-2">
                                        <a href="{{ route('users.edit', $user->id) }}" class="text-sky-500 hover:text-indigo-900">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                        @if(auth()->id() !== $user->id)
                                            <form method="POST" action="{{ route('users.destroy', $user->id) }}" class="inline"
                                                onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                    No users found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4">
                {{ $users->links() }}
            </div>
        </div>
        
        <!-- Pending Users Tab Content -->
        <div class="tab-pane hidden" id="pending-users-content" role="tabpanel" aria-labelledby="pending-users-tab">
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Name
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Email
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Role
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Created At
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($pendingUsers as $user)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-primary-100 rounded-full flex items-center justify-center">
                                            <span class="text-primary-600 font-semibold text-lg">{{ substr($user->name, 0, 1) }}</span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $user->name }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $roleBgColor = 'bg-gray-100';
                                        $roleTextColor = 'text-gray-800';
                                        
                                        if ($user->isAdmin()) {
                                            $roleBgColor = 'bg-red-100';
                                            $roleTextColor = 'text-red-800';
                                        } elseif ($user->isTester()) {
                                            $roleBgColor = 'bg-blue-100';
                                            $roleTextColor = 'text-blue-800';
                                        } elseif ($user->isDeveloper()) {
                                            $roleBgColor = 'bg-green-100';
                                            $roleTextColor = 'text-green-800';
                                        } elseif ($user->isProjectManager()) {
                                            $roleBgColor = 'bg-purple-100';
                                            $roleTextColor = 'text-purple-800';
                                        }
                                    @endphp
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $roleBgColor }} {{ $roleTextColor }}">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500">{{ $user->created_at->format('M d, Y') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end space-x-2">
                                        <form method="POST" action="{{ route('users.approve', $user->id) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-green-600 hover:text-green-900">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('users.reject', $user->id) }}" class="inline"
                                            onsubmit="return confirm('Are you sure you want to reject this user?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                    No pending users found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab functionality
    const tabs = document.querySelectorAll('[data-tab-target]');
    const tabContents = document.querySelectorAll('.tab-pane');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            // Remove active class from all tabs and tab contents
            tabs.forEach(t => {
                t.classList.remove('border-[#13A7FD]', 'text-[#13A7FD]');
                t.classList.add('border-transparent', 'hover:text-gray-600', 'hover:border-gray-300');
                t.setAttribute('aria-selected', 'false');
            });
            
            tabContents.forEach(content => {
                content.classList.add('hidden');
                content.classList.remove('active');
            });
            
            // Add active class to selected tab and content
            const targetId = tab.getAttribute('data-tab-target');
            const targetContent = document.getElementById(targetId);
            
            tab.classList.add('border-[#13A7FD]', 'text-[#13A7FD]');
            tab.classList.remove('border-transparent', 'hover:text-gray-600', 'hover:border-gray-300');
            tab.setAttribute('aria-selected', 'true');
            
            targetContent.classList.remove('hidden');
            targetContent.classList.add('active');
        });
    });
});
</script>
@endsection