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
    <style>
        .sidebar-button {
            max-width: 90%;
            /* Adjust this percentage to control button width */
            margin-right: auto;
            border-top-right-radius: 9999px;
            border-bottom-right-radius: 9999px;
        }

        .sidebar-button:hover,
        .sidebar-button.active {
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
            overflow: auto;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
</head>

<body class="bg-gray-50">
    <div class="min-h-screen flex" x-data="{ sidebarOpen: true }">
        <!-- Sidebar -->
        <div
            class="bg-white shadow-md flex flex-col fixed h-full z-30 transition-all duration-300 ease-in-out"
            :class="sidebarOpen ? 'w-64' : 'w-20'">
            <!-- Logo and Toggle Section -->
            <div class="py-4 px-4 border-b border-gray-200 flex items-center justify-between">
                <div class="flex items-center" :class="sidebarOpen ? 'flex' : 'hidden'">
                    <img src="{{ asset('Frame_25.png') }}" class="h-15 w-15">
                </div>
                <button
                    @click="sidebarOpen = !sidebarOpen"
                    class="p-2 rounded-lg hover:bg-[#13A7FD] focus:outline-none">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4 transition-transform duration-300"
                        :class="sidebarOpen ? 'transform rotate-180' : ''"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                    </svg>
                </button>
            </div>

            <!-- Profile Section -->
            <div class="px-4 py-4 border-b border-gray-200" x-show="sidebarOpen" x-transition>
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
            <div class="py-4 pe-2 flex-grow overflow-y-auto">
                <div class="px-4 py-2 mt-2 mb-2" x-show="sidebarOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                    <p class="text-xs uppercase font-semibold text-gray-500 tracking-wider">Reports</p>
                </div>
                <!-- Menu Items -->
                <div class="space-y-1">
                    <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-2 rounded-r-full transition-colors duration-200 {{ request()->routeIs('dashboard') ? 'bg-[#13A7FD] text-white' : 'hover:text-white hover:bg-[#13A7FD]' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span class="ml-3 transition-opacity duration-200" :class="sidebarOpen ? 'opacity-100' : 'opacity-0 hidden'">
                            Dashboard
                        </span>
                    </a>
                    @if(auth()->user()->isDeveloper() || auth()->user()->isTester())
                    <a href="{{ route('reports') }}" class="flex items-center px-4 py-2 rounded-r-full transition-colors duration-200 {{ request()->routeIs('reports') ? 'bg-[#13A7FD] text-white' : 'hover:text-white hover:bg-[#13A7FD]' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span class="ml-3 transition-opacity duration-200" :class="sidebarOpen ? 'opacity-100' : 'opacity-0 hidden'">
                            Reports
                        </span>
                    </a>
                    @endif
                    <a href="{{ route('story.points.report') }}" class="flex items-center px-4 py-2 rounded-r-full transition-colors duration-200 {{ request()->routeIs('story.points.report') ? 'bg-[#13A7FD] text-white' : 'hover:text-white hover:bg-[#13A7FD]' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>

                        <span class="ml-3 transition-opacity duration-200" :class="sidebarOpen ? 'opacity-100' : 'opacity-0 hidden'">
                            Current Sprint Report
                        </span>
                    </a>
                    <a href="{{ route('minor-cases.index') }}" class="flex items-center px-4 py-2 rounded-r-full transition-colors duration-200 {{ request()->routeIs('minor-cases.*') ?  'bg-[#13A7FD] text-white' : 'hover:text-white hover:bg-[#13A7FD]' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-send" viewBox="0 0 16 16">
                            <path d="M15.854.146a.5.5 0 0 1 .11.54l-5.819 14.547a.75.75 0 0 1-1.329.124l-3.178-4.995L.643 7.184a.75.75 0 0 1 .124-1.33L15.314.037a.5.5 0 0 1 .54.11ZM6.636 10.07l2.761 4.338L14.13 2.576zm6.787-8.201L1.591 6.602l4.339 2.76z" />
                        </svg>
                        <span class="ml-3 transition-opacity duration-200" :class="sidebarOpen ? 'opacity-100' : 'opacity-0 hidden'">
                            Minor Cases
                        </span>
                    </a>
                    <a href="{{ route('backlog.index') }}" class="flex items-center px-4 py-2 rounded-r-full transition-colors duration-200 {{ request()->routeIs('backlog.*') ? '  bg-[#13A7FD] text-white' : 'hover:text-white hover:bg-[#13A7FD]' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="ml-3 transition-opacity duration-200" :class="sidebarOpen ? 'opacity-100' : 'opacity-0 hidden'">
                            Bug Backlog
                        </span>
                    </a>
                    <a href="{{ route('trello.teams.index') }}" class="flex items-center px-4 py-2 rounded-r-full transition-colors duration-200 {{ request()->routeIs('trello.teams.*') ? 'bg-[#13A7FD] text-white' : 'hover:text-white hover:bg-[#13A7FD]'}}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span class="ml-3 transition-opacity duration-200" :class="sidebarOpen ? 'opacity-100' : 'opacity-0 hidden'">
                            Trello teams
                        </span>
                    </a>
                    @if(auth()->user()->isAdmin() )
                    <a href="{{ route('settings.sprint') }}" class="flex items-center px-4 py-2 rounded-r-full transition-colors duration-200 {{ request()->routeIs('settings.sprint') ? 'bg-[#13A7FD] text-white' : 'hover:text-white hover:bg-[#13A7FD]' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span class="ml-3 transition-opacity duration-200" :class="sidebarOpen ? 'opacity-100' : 'opacity-0 hidden'">
                            Sprint Settings
                        </span>
                    </a>
                    <a href="{{ route('saved-reports.index') }}" class="flex items-center px-4 py-2 rounded-r-full transition-colors duration-200 {{ request()->routeIs('saved-reports.*') ?'bg-[#13A7FD] text-white' : 'hover:text-white hover:bg-[#13A7FD]' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                        </svg>
                        <span class="ml-3 transition-opacity duration-200" :class="sidebarOpen ? 'opacity-100' : 'opacity-0 hidden'">
                            Saved Reports
                        </span>
                    </a>
                    <a href="{{ route('sprints.index') }}" class="flex items-center px-4 py-2 rounded-r-full transition-colors duration-200 {{ request()->routeIs('sprints.*') || request()->routeIs('sprint-reports.*') ? 'bg-[#13A7FD] text-white' : 'hover:text-white hover:bg-[#13A7FD]' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span class="ml-3 transition-opacity duration-200" :class="sidebarOpen ? 'opacity-100' : 'opacity-0 hidden'">
                            Sprint Reports
                        </span>
                    </a>
                    @endif
                    @if(auth()->user()->isAdmin())
                    <div class="px-4 py-2 mt-6 mb-6" x-show="sidebarOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                        <p class="text-xs uppercase font-semibold text-gray-500 mt-1 mb-1 tracking-wider">Admin Settings</p>
                    </div>
                    <a href="{{ route('users.index') }}" class="flex items-center px-4 py-2 rounded-r-full transition-colors duration-200 {{ request()->routeIs('users.*') ? 'bg-[#13A7FD] text-white' : 'hover:text-white hover:bg-[#13A7FD]'}}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span class="ml-3 transition-opacity duration-200" :class="sidebarOpen ? 'opacity-100' : 'opacity-0 hidden'">
                            User Management
                        </span>
                    </a>
                    @endif
                    @if(auth()->user()->isAdmin() )
                    <a href="{{ route('trello.settings.index') }}" class="flex items-center px-4 py-2 rounded-r-full transition-colors duration-200 {{ request()->routeIs('trello.settings.*') ?'bg-[#13A7FD] text-white' : 'hover:text-white hover:bg-[#13A7FD]' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="ml-3 transition-opacity duration-200" :class="sidebarOpen ? 'opacity-100' : 'opacity-0 hidden'">
                            Trello API Settings
                        </span>
                    </a>
                    @endif
                </div>
            </div>

            <!-- App Version -->
            <div class="border-t border-gray-200 py-3 px-4">
                <p class="text-xs text-gray-500 text-center" :class="sidebarOpen ? '' : 'hidden'">
                    v{{ config('app.version', '1.0') }}
                </p>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 transition-all duration-300"
            :class="sidebarOpen ? 'ml-64' : 'ml-20'">
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

    <style>
        .sidebar-transition {
            transition: all 0.3s ease-in-out;
        }

        /* Hide scrollbar for Chrome, Safari and Opera */
        .overflow-y-auto::-webkit-scrollbar {
            display: none;
        }

        /* Hide scrollbar for IE, Edge and Firefox */
        .overflow-y-auto {
            -ms-overflow-style: none;
            /* IE and Edge */
            scrollbar-width: none;
            /* Firefox */
        }
    </style>
</body>

</html>
