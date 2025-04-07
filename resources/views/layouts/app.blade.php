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

        /* Sidebar transition animation */
        .sidebar-transition {
            transition: width 0.3s ease-in-out;
        }

        /* Text fade in/out animation */
        .text-fade {
            transition: opacity 0.2s ease-in-out;
        }

        /* Tooltip for collapsed sidebar */
        .tooltip {
            position: relative;
        }

        .tooltip .tooltip-text {
            visibility: hidden;
            width: auto;
            background-color: rgba(0, 0, 0, 0.8);
            color: #fff;
            text-align: center;
            border-radius: 6px;
            padding: 5px 10px;
            position: absolute;
            z-index: 1;
            left: 100%;
            top: 50%;
            transform: translateY(-50%);
            margin-left: 10px;
            opacity: 0;
            transition: opacity 0.2s;
            white-space: nowrap;
        }

        .tooltip:hover .tooltip-text {
            visibility: visible;
            opacity: 1;
        }
         .sidebar-button span {
    overflow:auto;
    text-overflow: ellipsis;
    white-space: nowrap;
} 
    </style>
</head>

<body class="bg-gray-50">
<div class="min-h-screen flex" x-data="{ sidebarOpen: localStorage.getItem('sidebarOpen') === 'false' ? false : true }" 
x-init="$watch('sidebarOpen', val => localStorage.setItem('sidebarOpen', val))">
        <!-- Sidebar -->
        <div 
            class="bg-white shadow-md flex flex-col sidebar-transition overflow-hidden" 
            :class="sidebarOpen ? 'w-64' : 'w-16'"
        >
            <!-- Logo and App Name -->
            <div class="py-4 px-4 border-gray-200 flex items-center justify-between">
    <div class="flex items-center">
        <!-- Logo -->
        <div class="flex items-center justify-center" :class="sidebarOpen ? 'opacity-100' : 'opacity-0 hidden'" x-transition:enter="transition ease-out duration-300" x-transition:leave="transition ease-in duration-200">
            <img src="{{ asset('Frame 25.png') }}" class="h-15 w-15">
        </div>
        <!-- App Name -->
        <span class="ml-2 font-medium text-fade" :class="sidebarOpen ? 'opacity-100' : 'opacity-0 hidden'" x-transition:enter="transition ease-out duration-300" x-transition:leave="transition ease-in duration-200">
            {{ config( 'Laravel') }}
        </span>
    </div>
        <!-- Sidebar Toggle Button -->
        <button 
            @click="sidebarOpen = !sidebarOpen" 
            class="p-1 rounded-full hover:bg-gray-200 focus:outline-none"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
            </svg>
        </button>
    </div>

            <!-- User Profile Card - Only Show When Expanded -->
            <div class="px-4 py-4 border-b border-gray-200" x-show="sidebarOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
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
            <div class="py-4 px-2 flex-grow">
                <div class="px-4 py-2 mb-4">
                    <p class="text-xs uppercase font-semibold text-gray-500 tracking-wider">Main Menu</p>
                </div>
                <a href="{{ route('dashboard') }}" class="block px-4 py-2 rounded-lg mb-1 {{ request()->routeIs('dashboard') && !request()->routeIs('*.index') ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-100' }}">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Dashboard
                    </div>
                </a>
                <!-- Trello Teams - Available for all users -->
                <a href="{{ route('trello.teams.index') }}" class="block px-4 py-2 rounded-lg mb-1 {{ request()->routeIs('trello.teams.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-100' }}">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span class="ml-3 text-fade" :class="sidebarOpen ? 'opacity-100' : 'opacity-0 hidden'">My Teams</span>
                        <span class="tooltip-text" x-show="!sidebarOpen">My Teams</span>
                    </div>
                </a>
                <a href="{{ route('minorcases') }}" class="block px-4 py-2 rounded-lg mb-1 {{ request()->routeIs('minorcases') ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-100' }}">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" width="16" height="16" fill="currentColor" class="bi bi-hash" viewBox="0 0 16 16">
                            <path d="M8.39 12.648a1 1 0 0 0-.015.18c0 .305.21.508.5.508.266 0 .492-.172.555-.477l.554-2.703h1.204c.421 0 .617-.234.617-.547 0-.312-.188-.53-.617-.53h-.985l.516-2.524h1.265c.43 0 .618-.227.618-.547 0-.313-.188-.524-.618-.524h-1.046l.476-2.304a1 1 0 0 0 .016-.164.51.51 0 0 0-.516-.516.54.54 0 0 0-.539.43l-.523 2.554H7.617l.477-2.304c.008-.04.015-.118.015-.164a.51.51 0 0 0-.523-.516.54.54 0 0 0-.531.43L6.53 5.484H5.414c-.43 0-.617.22-.617.532s.187.539.617.539h.906l-.515 2.523H4.609c-.421 0-.609.219-.609.531s.188.547.61.547h.976l-.516 2.492c-.008.04-.015.125-.015.18 0 .305.21.508.5.508.265 0 .492-.172.554-.477l.555-2.703h2.242zm-1-6.109h2.266l-.515 2.563H6.859l.532-2.563z" />
                        </svg>
                        Minor Cases
                    </div>
                </a>
                <!-- Bug Backlog - Available for all users not admin z-->
                <a href="{{ route('backlog.index') }}" class="block px-4 py-2 rounded-lg mb-1 {{ request()->routeIs('backlog.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-100' }}">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Bug Backlog
                    </div>
                </a>
            

                <!-- Reports Section -->
                <div class="px-4 py-2 mt-4 mb-2" x-show="sidebarOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                    <p class="text-xs uppercase font-semibold text-gray-500 tracking-wider">Reports</p>
                </div>
                        </svg>
                        @php
                            $currentSprint = \App\Models\Sprint::getCurrentSprint();
                            $sprintNumber = $currentSprint ? $currentSprint->sprint_number : \App\Models\Sprint::getNextSprintNumber();
                        @endphp
                        Sprint: {{ $sprintNumber }} Report
                    </div>
                </a>

                


                @if(auth()->user()->isAdmin())
                <div class="px-4 py-2 mt-6 mb-4">
                    <p class="text-xs uppercase font-semibold text-gray-500 tracking-wider">Admin Settings</p>
                </div>
                <a href="{{ route('users.index') }}" class="block px-4 py-2 rounded-lg mb-1 {{ request()->routeIs('users.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-100' }}">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        User Management
                    </div>
                </a>

                <a href="{{ route('trello.settings.index') }}" class="block px-4 py-2 rounded-lg mb-1 {{ request()->routeIs('trello.settings.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-100' }}">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Trello API Settings
                    </div>
                </a>

                <a href="{{ route('settings.sprint') }}" class="block px-4 py-2 sidebar-button mb-1 tooltip {{ request()->routeIs('settings.sprint') ? 'active' : '' }}" :class="sidebarOpen ? '' : 'flex justify-center'">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span class="ml-3 text-fade" :class="sidebarOpen ? 'opacity-100' : 'opacity-0 hidden'">Sprint Settings</span>
                        <span class="tooltip-text" x-show="!sidebarOpen">Sprint Settings</span>
                    </div>
                </a>

                <a href="{{ route('saved-reports.index') }}" class="block px-4 py-2 sidebar-button mb-1 tooltip {{ request()->routeIs('saved-reports.*') ? 'active' : '' }}" :class="sidebarOpen ? '' : 'flex justify-center'">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                        </svg>
                        <span class="ml-3 text-fade" :class="sidebarOpen ? 'opacity-100' : 'opacity-0 hidden'">Saved Reports</span>
                        <span class="tooltip-text" x-show="!sidebarOpen">Saved Reports</span>
                    </div>
                </a>

                <a href="{{ route('sprints.index') }}" class="block px-4 py-2 sidebar-button mb-1 tooltip {{ request()->routeIs('sprints.*') || request()->routeIs('sprint-reports.*') ? 'active' : '' }}" :class="sidebarOpen ? '' : 'flex justify-center'">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span class="ml-3 text-fade" :class="sidebarOpen ? 'opacity-100' : 'opacity-0 hidden'">Sprint Reports</span>
                        <span class="tooltip-text" x-show="!sidebarOpen">Sprint Reports</span>
                    </div>
                </a>

                @endif
            </div>

            <!-- App Version - Only Show When Expanded -->
            <div class="border-t border-gray-200 py-3 px-4" x-show="sidebarOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
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