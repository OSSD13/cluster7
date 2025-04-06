<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - @yield('title', 'Dashboard')</title>
    <!-- Alpine.js -->
    <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.8.2/dist/alpine.min.js" defer></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Add custom styles for sidebar buttons -->
    <style>
        .sidebar-button {
            max-width: 90%; /* Adjust this percentage to control button width */
            margin-right: auto;
            border-top-right-radius: 9999px;
            border-bottom-right-radius: 9999px;
        }
        
        .sidebar-button:hover, .sidebar-button.active {
            background-color: #13A7FD;
            color: white;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-md flex flex-col">
            <!-- Logo and App Name -->
            <div class="py-4 px-4  border-gray-200">
                <div class="flex items-center">
                    <div class="flex items-center justify-center ">
                    <img src="{{ asset('Frame 25.png') }}" >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                    </div>
                    
                </div>
               
            </div>

            <!-- User Profile Card -->
            <div class="px-4 py-4 border-b border-gray-200">
                <div class="bg-gray-50 rounded-lg p-3 shadow-sm">
                    <div class="flex items-center">
                        
                            <div class="w-12 h-12 rounded-full bg-primary-100 flex items-center justify-center">
                                <span class="text-primary-700 font-bold text-lg">{{ substr(auth()->user()->name, 0, 1) }}</span>
                            </div>
                       
                        <div class="ml-3 flex-1 min-w-0">
                        <div class="mt-1">
                                @php
                                    $roleBgColor = 'bg-gray-100';
                                    $roleTextColor = 'text-gray-800';

                                    if (auth()->user()->isAdmin()) {
                                        $roleBgColor = 'bg-red-100';
                                        $roleTextColor = 'text-red-800';
                                    } elseif (auth()->user()->isTester()) {
                                        $roleBgColor = 'bg-blue-100';
                                        $roleTextColor = 'text-blue-800';
                                    } elseif (auth()->user()->isDeveloper()) {
                                        $roleBgColor = 'bg-green-100';
                                        $roleTextColor = 'text-green-800';
                                    }
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $roleBgColor }} {{ $roleTextColor }}">
                                    {{ ucfirst(auth()->user()->role) }}
                                </span>
                            </div>
                            <p class="text-sm font-medium text-gray-900 truncate">
                                {{ auth()->user()->name }}
                            </p>
                            <p class="text-xs text-gray-500 truncate">
                                {{ auth()->user()->email }}
                            </p>
                    
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-gray-600 hover:text-gray-900 p-1 rounded-full hover:bg-gray-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Navigation Menu -->
            <div class="py-4  flex-grow">
               
                @if(!auth()->user()->isAdmin())
                <a href="{{ route('my-teams.index') }}" class="block px-4 py-2 sidebar-button mb-1 {{ request()->routeIs('my-teams.*') ? 'active' : '' }}">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        My Teams
                    </div>
                </a>
                @endif

                <div class="px-4 py-2 mt-4 mb-2">
                    <p class="text-xs uppercase font-semibold text-gray-500 tracking-wider">Reports</p>
                </div>

                <a href="{{ route('story.points.report') }}" class="block px-4 py-2 sidebar-button mb-1 {{ request()->routeIs('story.points.report') ? 'active' : '' }}">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Current Sprint Report
                    </div>
                </a>

                <a href="{{ route('minorcases') }}" class="block gap-2 px-4 py-2 sidebar-button mb-1 {{ request()->routeIs('minorcases') ? 'active' : '' }}">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"  class="bi bi-send" viewBox="0 0 16 16">
                        <path d="M15.854.146a.5.5 0 0 1 .11.54l-5.819 14.547a.75.75 0 0 1-1.329.124l-3.178-4.995L.643 7.184a.75.75 0 0 1 .124-1.33L15.314.037a.5.5 0 0 1 .54.11ZM6.636 10.07l2.761 4.338L14.13 2.576zm6.787-8.201L1.591 6.602l4.339 2.76z"/>
                        </svg>
                        <span class="ml-4">Minor Cases</span>
                    </div>
                </a>

                <a href="{{ route('trello.teams.index') }}" class="block px-4 py-2 sidebar-button mb-1 {{ request()->routeIs('trello.teams.*') ? 'active' : '' }}"> 
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Trello Teams
                    </div>
                </a>

                <a href="{{ route('settings.sprint') }}" class="block px-4 py-2 sidebar-button mb-1 {{ request()->routeIs('settings.sprint') ? 'active' : '' }}">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Sprint Settings
                    </div>
                </a>

                <a href="{{ route('saved-reports.index') }}" class="block px-4 py-2 sidebar-button mb-1 {{ request()->routeIs('saved-reports.*') ? 'active' : '' }}">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                        </svg>
                        Saved Reports
                    </div>
                </a>

                <a href="{{ route('sprints.index') }}" class="block px-4 py-2 sidebar-button mb-1 {{ request()->routeIs('sprints.*') || request()->routeIs('sprint-reports.*') ? 'active' : '' }}">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Sprint Reports
                    </div>
                </a>

                <a href="{{ route('backlog.index') }}" class="block px-4 py-2 sidebar-button mb-1 {{ request()->routeIs('backlog.*') ? 'active' : '' }}">
                <div class="flex items-center">
                 <svg xmlns="http://www.w3.org/2000/svg" width="20" height="18" fill="currentColor" class="bi bi-bug" viewBox="0 0 16 16">
                    <path d="M4.355.522a.5.5 0 0 1 .623.333l.291.956A5 5 0 0 1 8 1c1.007 0 1.946.298 2.731.811l.29-.956a.5.5 0 1 1 .957.29l-.41 1.352A5 5 0 0 1 13 6h.5a.5.5 0 0 0 .5-.5V5a.5.5 0 0 1 1 0v.5A1.5 1.5 0 0 1 13.5 7H13v1h1.5a.5.5 0 0 1 0 1H13v1h.5a1.5 1.5 0 0 1 1.5 1.5v.5a.5.5 0 1 1-1 0v-.5a.5.5 0 0 0-.5-.5H13a5 5 0 0 1-10 0h-.5a.5.5 0 0 0-.5.5v.5a.5.5 0 1 1-1 0v-.5A1.5 1.5 0 0 1 2.5 10H3V9H1.5a.5.5 0 0 1 0-1H3V7h-.5A1.5 1.5 0 0 1 1 5.5V5a.5.5 0 0 1 1 0v.5a.5.5 0 0 0 .5.5H3c0-1.364.547-2.601 1.432-3.503l-.41-1.352a.5.5 0 0 1 .333-.623M4 7v4a4 4 0 0 0 3.5 3.97V7zm4.5 0v7.97A4 4 0 0 0 12 11V7zM12 6a4 4 0 0 0-1.334-2.982A3.98 3.98 0 0 0 8 2a3.98 3.98 0 0 0-2.667 1.018A4 4 0 0 0 4 6z"/>
                    </svg>
                    <span class="ml-3">Bug Backlog</span>
                </div>
            </a>

                @if(auth()->user()->isAdmin())
                <div class="px-4 py-2 mt-6 mb-4">
                    <p class="text-xs uppercase font-semibold text-gray-500 tracking-wider ">Admin Settings</p>
                </div>
                <a href="{{ route('users.index') }}" 
                    class="block px-4 py-2 sidebar-button mb-1 {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        User Management
                    </div>
                </a>

                <a href="{{ route('trello.settings.index') }}"
                     class="block px-4 py-2 sidebar-button mb-1 {{ request()->routeIs('trello.settings.*') ? 'active' : '' }}">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Trello API Settings
                    </div>
                </a>
                
                @endif
            </div>

            <!-- App Version -->
            <div class="border-t border-gray-200 py-3 px-4">
                <p class="text-xs text-gray-500 text-center">v{{ config('app.version', '1.0') }}</p>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <main class="flex-1 py-6 px-4 sm:px-6 lg:px-8 overflow-auto">
                @if(session('success'))
                    <div class="max-w-7xl mx-auto mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded" role="alert">
                        <p>{{ session('success') }}</p>
                    </div>
                @endif

                @if(session('error'))
                    <div class="max-w-7xl mx-auto mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded" role="alert">
                        <p>{{ session('error') }}</p>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script>
        // Functions for the report tools
        function printReport() {
            window.print();
        }

        function exportToCSV() {
            // Check if we're on the reports page and there's a report to export
            const exportBtn = document.getElementById('export-csv-btn');
            if (exportBtn) {
                // Trigger the export button on the reports page
                exportBtn.click();
            } else {
                // Not on the reports page or no data to export
                alert('Please navigate to a report page with data to export first.');
            }
        }
    </script>
</body>
</html>