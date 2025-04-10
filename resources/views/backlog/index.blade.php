@extends('layouts.app')

@section('title', 'Bug Backlog')

@section('page-title', 'Bug Backlog')

@section('content')
<div class="max-w-7xl min-h-screen mx-auto">

    <!-- Edit Bug Modal -->
    <div id="editBugModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full backdrop-blur-sm">
        <div class="relative top-20 mx-auto p-8 w-[600px] shadow-xl rounded-[25px] bg-white modal-content">
            <div class="mt-2">
                <div class="flex items-center mb-6">
                    <div class="w-10 h-10 rounded-full bg-sky-100 flex justify-center items-center mr-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#13A7FD" class="bi bi-bug" viewBox="0 0 16 16">
                            <path d="M4.355.522a.5.5 0 0 1 .623.333l.291.956A5 5 0 0 1 8 1c1.007 0 1.946.298 2.731.811l.29-.956a.5.5 0 1 1 .957.29l-.41 1.352A5 5 0 0 1 13 6h.5a.5.5 0 0 0 .5-.5V5a.5.5 0 0 1 1 0v.5A1.5 1.5 0 0 1 13.5 7H13v1h1.5a.5.5 0 0 1 0 1H13v1h.5a1.5 1.5 0 0 1 1.5 1.5v.5a.5.5 0 1 1-1 0v-.5a.5.5 0 0 0-.5-.5H13a5 5 0 0 1-10 0h-.5a.5.5 0 0 0-.5.5v.5a.5.5 0 1 1-1 0v-.5A1.5 1.5 0 0 1 2.5 10H3V9H1.5a.5.5 0 0 1 0-1H3V7h-.5A1.5 1.5 0 0 1 1 5.5V5a.5.5 0 0 1 1 0v.5a.5.5 0 0 0 .5.5H3c0-1.364.547-2.601 1.432-3.503l-.41-1.352a.5.5 0 0 1 .333-.623M4 7v4a4 4 0 0 0 3.5 3.97V7zm4.5 0v7.97A4 4 0 0 0 12 11V7zM12 6a4 4 0 0 0-1.334-2.982A3.98 3.98 0 0 0 8 2a3.98 3.98 0 0 0-2.667 1.018A4 4 0 0 0 4 6z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-semibold text-gray-900">Edit Bug</h3>
                </div>

                <form id="editBugForm" class="mt-5 space-y-6">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="bugId" name="id">
                    
                    <div class="mb-4">
                    <label for="bugName" class="block text-sm font-medium text-gray-700 mb-2">Bug Name</label>
                    <input type="text" id="bugName" name="name" class="mt-1 block w-full px-4 py-3 rounded-2xl bg-gray-50 border-0 focus:border-0 outline-none focus:ring-2 focus:ring-[#13A7FD] transition-all duration-200">
                    </div>

                    <div class="mb-4">
                        <label for="bugTeam" class="block text-sm font-medium text-gray-700 ">team</label>
                        <input type="text" id="bugTeam" name="team" class="mt-1 block w-full px-4 py-3 rounded-2xl bg-gray-50 border-0 focus:border-0 outline-none focus:ring-2 focus:ring-[#13A7FD] transition-all duration-200 " readonly>
                    </div>
                    
                    <div class="mb-4">
                        <label for="bugPoints" class="block text-sm font-medium text-gray-700">Points</label>
                        <input type="number" id="bugPoints" name="points" min="1" class="mt-1 block w-full rounded-3xl bg-gray-100 shadow-sm focus:ring-primary-500 border-0 focus:border-0 outline-none'' ">
                    </div>

                    <div class="mb-4">
                        <label for="bugAssigned" class="block text-sm font-medium text-gray-700">Assigned To</label>
                        <input type="text" id="bugAssigned" name="assigned" class="mt-1 block w-full rounded-3xl bg-gray-100 shadow-sm focus:ring-primary-500 border-0 focus:border-0 outline-none">
                    </div>

                    <div class="mb-4">
                        <label for="bugDescription" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea id="bugDescription" name="description" rows="3" class="mt-1 block w-full rounded-3xl bg-gray-100 shadow-sm focus:ring-primary-500 border-0 focus:border-0 outline-none"></textarea>
                    </div>

                    <div class="mt-5 flex space-x-3 ml-20">
                        <button type="button" id="cancelEditBug" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-full bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-300">
                            Cancel
                        </button>
                        <button type="submit" class="px-6 py-2.5 bg-[#13A7FD] text-white rounded-full hover:bg-[#0090e0] focus:outline-none transition-all duration-200">
                            Save 
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- No Bugs Message (Only shown if there are no backlog bugs) -->
    @if($allBugs->count() == 0)
    <div class="bg-white shadow rounded-lg p-8 text-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No Backlog Bugs</h3>
        <p class="text-gray-500 mb-4">There are no unresolved bugs in the backlog. Great job!</p>
    </div>
    @else
    <!-- All Bugs Tab Content -->
    <div class="bg-white shadow rounded-lg p-8 text-center">
        <div class="flex ps-5 pt-3 ">
            <div class="w-20 h-20 rounded-full bg-sky-100 flex justify-center items-center ">
                <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" fill="#13A7FD" class="bi bi-bug"
                    viewBox="0 0 16 16">
                    <path
                        d="M4.355.522a.5.5 0 0 1 .623.333l.291.956A5 5 0 0 1 8 1c1.007 0 1.946.298 2.731.811l.29-.956a.5.5 0 1 1 .957.29l-.41 1.352A5 5 0 0 1 13 6h.5a.5.5 0 0 0 .5-.5V5a.5.5 0 0 1 1 0v.5A1.5 1.5 0 0 1 13.5 7H13v1h1.5a.5.5 0 0 1 0 1H13v1h.5a1.5 1.5 0 0 1 1.5 1.5v.5a.5.5 0 1 1-1 0v-.5a.5.5 0 0 0-.5-.5H13a5 5 0 0 1-10 0h-.5a.5.5 0 0 0-.5.5v.5a.5.5 0 1 1-1 0v-.5A1.5 1.5 0 0 1 2.5 10H3V9H1.5a.5.5 0 0 1 0-1H3V7h-.5A1.5 1.5 0 0 1 1 5.5V5a.5.5 0 0 1 1 0v.5a.5.5 0 0 0 .5.5H3c0-1.364.547-2.601 1.432-3.503l-.41-1.352a.5.5 0 0 1 .333-.623M4 7v4a4 4 0 0 0 3.5 3.97V7zm4.5 0v7.97A4 4 0 0 0 12 11V7zM12 6a4 4 0 0 0-1.334-2.982A3.98 3.98 0 0 0 8 2a3.98 3.98 0 0 0-2.667 1.018A4 4 0 0 0 4 6z" />
                </svg>
            </div>
            
            <p
                class="mt-2 ms-3 pb-3 font-style: italic font-weight: text-[#009eff] text-6xl font-bold inline-block align-middle ">
                Backlog</p>
                <!-- Total Bug Count -->
                <span id="totalBugCount" class="ml-4 mt-6 flex items-center justify-center font-bold text-sm bg-amber-100 text-amber-800 pt-2 py-1 px-2 min-w-40 max-w-full h-10 rounded-full">
                    
                    {{ $allBugs->count() }} {{ Str::plural('bug', $allBugs->count()) }}
                    ({{ $allBugs->sum('points') }} {{ Str::plural('point', $allBugs->sum('points')) }})
                </span>
                <!-- Team Count Badges -->
                <div id="teamCountBadges">
                @foreach($bugsByTeam as $teamName => $bugs)
                <span class="ml-4 mt-6 flex items-center justify-center font-bold text-sm bg-amber-100 text-amber-800 pt-2 py-1 px-2 min-w-40 max-w-full h-10 rounded-full team-count-badge" data-team="{{ $teamName }}">
                    {{ $bugs->count() }} {{ Str::plural('bug', $bugs->count()) }}
                    ({{ $bugs->sum('points') }} {{ Str::plural('point', $bugs->sum('points')) }})
                </span>
                @endforeach
                </div>

                
            <!-- Dropdown-->
            <div class="ms-[150px] grid grid-rows-2 gap-1">
                <div>
                    <!-- Dropdown Teams-->
                    <div class="flex w-full">
                        <label for="team" class="text-sm font-medium text-gray-900 pt-6 me-4 ">Team:</label>
                        <div class="relative inline-block w-full text-left py-3.5">
                            <button id="teamDropdownButton"
                                class="flex items-center px-4 py-2 bg-white border rounded-[100px] shadow-md w-[400px] justify-between hover:bg-gray-200">
                                <span id="selectedTeam" class="block w-full px-6">All</span>
                                <svg class="w-4 h-4 text-gray-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>

                            <!-- Dropdown Menu -->
                            <div id="teamDropdownMenu"
                                class="hidden absolute right-0 mt-2 bg-white border rounded-[15px] w-[400px] shadow-lg overflow-hidden dropdown-menu">
                                <ul class="text-gray-700">
                                    <li><a href="#" class="block px-11 py-2 hover:bg-gray-200 border-b text-center" data-team="all">All</a></li>
                                    @foreach($bugsByTeam as $teamName => $bugs)
                                        <li><a href="#" class="block px-11 py-2 hover:bg-gray-200 border-b text-center" data-team="{{ $teamName }}">{{ $teamName }}</a></li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <span>
                    <!-- Dropdown Year and Sprint -->
                    <div class="flex h-10">
                        <!--Dropdown Year -->
                        <label for="year" class="text-sm font-medium text-gray-900 flex items-center me-5">Year:</label>
                        <div class="relative inline-block text-left ">
                            <button id="yearDropdownButton"
                                class="flex items-center px-4 py-2 bg-white border rounded-[100px] shadow-md w-32 justify-between hover:bg-gray-200">
                                <span id="selectedYear" class="block px-6">2025</span>
                                <svg class="w-4 h-4 text-gray-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>

                <!-- Dropdown Menu -->
                        <div id="yearDropdownMenu"
                            class="hidden absolute right-0 mt-2 w-32 bg-white border rounded-[15px] shadow-lg overflow-hidden dropdown-menu">
                            <ul class="text-gray-700">
                                <li><a href="#" class="block px-11 py-2 hover:bg-gray-200 border-b">2025</a></li>
                                <li><a href="#" class="block px-11 py-2 hover:bg-gray-200 border-b">2024</a></li>
                                <li><a href="#" class="block px-11 py-2 hover:bg-gray-200 border-b">2023</a></li>
                                <li><a href="#" class="block px-11 py-2 hover:bg-gray-200 border-b">2022</a></li>
                                <li><a href="#" class="block px-11 py-2 hover:bg-gray-200 border-b">2021</a></li>
                                <li><a href="#" class="block px-11 py-2 hover:bg-gray-200">2020</a></li>
                            </ul>
                        </div>
            </div>
            <!--Dropdown Sprint -->
                        <label for="sprint" class="text-sm font-medium text-gray-900 flex items-center ps-5 me-5">Sprint:</label>
                        <div class="relative inline-block text-left">
                <button id="sprintDropdownButton"
                    class="flex items-center px-4 py-2 bg-white border rounded-[100px] shadow-md w-48 justify-between hover:bg-gray-200">
                    <span id="selectedSprint" class="block w-full px-6">1 ~ 10</span>
                    <svg class="w-4 h-4 text-gray-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                        </svg>
                </button>

                <!-- Dropdown Menu -->
                <div id="sprintDropdownMenu"
                    class="hidden absolute right-0 mt-2 w-48 bg-white border rounded-[15px] shadow-lg overflow-hidden dropdown-menu">
                    <ul class="text-gray-700">
                        <li><a href="#" class="block px-12 py-2 text-center hover:bg-gray-200 border-b" data-sprint-range="1-10">1 ~ 10</a></li>
                        <li><a href="#" class="block px-11 py-2 text-center hover:bg-gray-200 border-b" data-sprint-range="11-20">11 ~ 20</a></li>
                        <li><a href="#" class="block px-11 py-2 text-center hover:bg-gray-200 border-b" data-sprint-range="21-30">21 ~ 30</a></li>
                        <li><a href="#" class="block px-11 py-2 text-center hover:bg-gray-200 border-b" data-sprint-range="31-40">31 ~ 40</a></li>
                        <li><a href="#" class="block px-11 py-2 text-center hover:bg-gray-200 border-b" data-sprint-range="41-50">41 ~ 50</a></li>
                        <li><a href="#" class="block px-11 py-2 text-center hover:bg-gray-200" data-sprint-range="51-52">51 ~ 52</a></li>
                    </ul>
                </div>
            </div>
</div>

                </span>
            </div>
        </div>
        <p class=" text-left ms-5 mt-2 mb-8 text-sm text-gray-600">
            This page displays all backlog bugs from previous sprints. These bugs have been tracked but not resolved in their original sprints.
        </p>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-2">
            @foreach($allBugs as $bug)
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden  transition-shadow bug-card" data-team="{{ $bug['team'] ?? 'all' }}" data-name="{{ $bug['name'] ?? '' }}" data-sprint="{{ $bug['sprint_origin'] ?? $bug['sprint_number'] ?? '?' }}" data-id="{{ $bug['id'] }}">
                <!-- Card Header with Bug ID and Priority -->
                <div class="flex justify-between items-center p-4 border-b border-gray-100 ">
                    <div class="flex items-center">
                        <!-- Points in circle -->
                        <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-500 flex items-center justify-center font-bold text-sm mr-3">
                            {{ $bug['points'] ?? '-' }}
                        </div>
                        <!-- Bug ID and Name -->
                        <div>
                            <a href="{{ $bug['url'] ?? '#' }}" class="text-gray-900 font-medium hover:text-primary-600" target="_blank">{{ $bug['name'] }}</a>
                        </div>
                    </div>
                    <!-- Sprint badge -->
                    <div>
                        @if(isset($bug['sprint_origin']))
                        <span class="px-3 py-1 text-xs rounded-full bg-gray-100 text-gray-600">
                            Sprint {{ $bug['sprint_origin'] }}
                        </span>
                        @else
                        <span class="px-3 py-1 text-xs rounded-full bg-gray-100 text-gray-600">
                            Sprint {{ $bug['sprint_number'] ?? '?' }}
                        </span>
                        @endif
                    </div>
                </div>

                <!-- Bug Name/Description/buttom -->
                <div class="p-4 ">

                    <div class="col-span-8  flex items-end overflow-auto ">
                    </div>
                    <!-- Edit Button -->
                    <div class="flex space-x-3 flex items-end ">
 
                    <button type="button" class="text-[#985E00] bg-[#FFC7B2] hover:bg-[#FFA954] focus:outline-none font-medium rounded-full px-2 py-2 text-center h-8 w-8 ">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class=" bi bi-pencil-square" viewBox="0 0 16 16 ">
                            <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z" />
                            <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z" />
                        </svg>
                    </button>
                    <!-- Delete Button -->
                    <form action="{{ route('backlog.destroy', $bug['id']) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this bug?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-[#FF0004] bg-[#FFACAE] hover:bg-[#FF7C7E] focus:outline-none font-medium rounded-full px-2 py-2 text-center h-8 w-8">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3" viewBox="0 0 16 16">
                                <path d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5M11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1zm1.958 1-.846 10.58a1 1 0 0 1-.997.92h-6.23a1 1 0 0 1-.997-.92L3.042 3.5zm-7.487 1a.5.5 0 0 1 .528.47l.5 8.5a.5.5 0 0 1-.998.06L5 5.03a.5.5 0 0 1 .47-.53Zm5.058 0a.5.5 0 0 1 .47.53l-.5 8.5a.5.5 0 1 1-.998-.06l.5-8.5a.5.5 0 0 1 .528-.47M8 4.5a.5.5 0 0 1 .5.5v8.5a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5" />
                            </svg>
                        </button>
                    </form>
                </div>
                </div>
                <!-- Card Footer with Assignment -->
                <div class="bg-gray-50 px-4 py-3 flex justify-between items-center">
                    <div class="flex items-center">
                        <span class="text-sm text-gray-500">Assign to:</span>

                        <span class="ml-2 px-2 py-1 text-xs font-medium rounded-full bg-[#BAF3FF] text-[#13A7FD]">
                            {{ $bug['team'] ?? '-' }}
                        </span>

                        <span class="ml-2 px-2 py-1 text-xs font-medium rounded-full">
                            {{ $bug['assigned'] ?? 'Unassigned' }}
                        </span>
                    </div>

                    <!-- Status indicator -->
                    <div>
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-[#DDFFEC] text-[#82DF3C]">
                       success
                        </span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>


@endif
</div>

<style>
            .hidden {
                display: none;
                opacity: 0;
                transform: translateY(-10px);
                transition: opacity 0.3s ease, transform 0.3s ease;
            }

            .block {
                display: block;
                opacity: 1;
                transform: translateY(0);
            }

            .sprint-with-data {
                display: block !important;
            }

            .sprint-date {
                display: block;
                width: 100%;
                text-align: center;
            }
            
            /* Fix for dropdown positioning */
            .dropdown-menu {
                z-index: 20;
                position: absolute;
            }
            
            /* Ensure dropdowns don't overlap */
            #teamDropdownMenu {
                z-index: 20;
            }
            
            #yearDropdownMenu {
                z-index: 19;
            }
            
            #sprintDropdownMenu {
                z-index: 18;
            }

            /* Modal styling */
            #editBugModal {
                z-index: 100;
            }

            #editBugModal .relative {
                z-index: 101;
            }

            /* Ensure modal content stays above the overlay */
            .modal-content {
                position: relative;
                z-index: 102;
            }
        </style>

<script>
    

    function setupDropdown(buttonId, menuId, selectedId) {
        document.getElementById(buttonId).addEventListener("click", function() {
            document.getElementById(menuId).classList.toggle("hidden");
        });

        document.querySelectorAll(`#${menuId} a`).forEach(item => {
            item.addEventListener("click", function(e) {
                e.preventDefault(); // Prevent default link behavior
                document.getElementById(selectedId).textContent = this.textContent;
                document.getElementById(menuId).classList.add("hidden");

                // Update displayed items based on selected filters
                if (menuId === "sprintDropdownMenu") {
                    const sprintRange = this.getAttribute('data-sprint-range');
                    filterBySprint(sprintRange);
                } else if (menuId === "teamDropdownMenu") {
                    const teamValue = this.getAttribute('data-team');
                    filterByTeam(teamValue);
                }
            });
        });

        document.addEventListener("click", function(event) {
            const dropdown = document.getElementById(menuId);
            const button = document.getElementById(buttonId);
            if (!button.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.classList.add("hidden");
            }
        });
    }
    
    // Setup both dropdowns
    setupDropdown("yearDropdownButton", "yearDropdownMenu", "selectedYear");
    setupDropdown("sprintDropdownButton", "sprintDropdownMenu", "selectedSprint");
    setupDropdown("teamDropdownButton", "teamDropdownMenu", "selectedTeam");


    // Update displayed sprints based on filters
    function updateDisplayedSprints() {
        const sprintRange = document.getElementById('selectedSprint').textContent;

        // Parse sprint range (e.g., "Sprint 1 ~ 10" to [1, 10])
        const rangeMatch = sprintRange.match(/Sprint (\d+) ~ (\d+)/);
        const startSprint = rangeMatch ? parseInt(rangeMatch[1]) : 1;
        const endSprint = rangeMatch ? parseInt(rangeMatch[2]) : 10;

        // Show/hide sprints based on range
        document.querySelectorAll('.sprint-block').forEach(sprintBlock => {
            const sprintNumber = parseInt(sprintBlock.dataset.sprintNumber);

            if (sprintNumber >= startSprint && sprintNumber <= endSprint) {
                sprintBlock.style.display = 'block';
            } else {
                sprintBlock.style.display = 'none';
            }
        });
    }

    function filterByTeam(selectedTeam) {
        const bugCards = document.querySelectorAll('.bug-card');
        const selectedTeamText = document.getElementById('selectedTeam').textContent;
        const selectedSprintRange = document.getElementById('selectedSprint').textContent;
        
        // Extract sprint range from the selected sprint text
        let startSprint = 1;
        let endSprint = 52;
        
        if (selectedSprintRange !== 'All') {
            const sprintMatch = selectedSprintRange.match(/(\d+)\s*~\s*(\d+)/);
            if (sprintMatch) {
                startSprint = parseInt(sprintMatch[1]);
                endSprint = parseInt(sprintMatch[2]);
            }
        }
        
        // Show/hide total bug count and team count badges based on selected team
        const totalBugCount = document.getElementById('totalBugCount');
        const teamCountBadges = document.getElementById('teamCountBadges');
        
        if (selectedTeam === 'all') {
            // Show total bug count and hide team count badges
            totalBugCount.style.display = 'inline-block';
            teamCountBadges.style.display = 'none';
        } else {
            // Hide total bug count and show team count badges
            totalBugCount.style.display = 'none';
            teamCountBadges.style.display = 'inline-block';
            
            // Hide all team count badges first
            document.querySelectorAll('.team-count-badge').forEach(badge => {
                badge.style.display = 'none';
            });
            
            // Show only the selected team's count badge
            const selectedTeamBadge = document.querySelector(`.team-count-badge[data-team="${selectedTeam}"]`);
            if (selectedTeamBadge) {
                selectedTeamBadge.style.display = 'inline-block';
            }
        }
        
        bugCards.forEach(card => {
            const cardTeam = card.getAttribute('data-team');
            const cardSprint = parseInt(card.getAttribute('data-sprint')) || 999;
            
            const teamMatch = selectedTeam === 'all' || cardTeam === selectedTeam;
            const sprintMatch = cardSprint >= startSprint && cardSprint <= endSprint;
            
            if (teamMatch && sprintMatch) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
        
        // Update the count of visible bugs
        updateBugCount();
        
        // Automatically sort bugs by sprint after filtering
        sortBugsBySprint();
    }

    function filterBySprint(sprintRange) {
        const bugCards = document.querySelectorAll('.bug-card');
        const [startSprint, endSprint] = sprintRange.split('-').map(Number);
        const selectedTeam = document.getElementById('selectedTeam').textContent;
        
        // Show/hide total bug count and team count badges based on selected team
        const totalBugCount = document.getElementById('totalBugCount');
        const teamCountBadges = document.getElementById('teamCountBadges');
        
        if (selectedTeam === 'All') {
            // Show total bug count and hide team count badges
            totalBugCount.style.display = 'inline-block';
            teamCountBadges.style.display = 'none';
        } else {
            // Hide total bug count and show team count badges
            totalBugCount.style.display = 'none';
            teamCountBadges.style.display = 'inline-block';
            
            // Hide all team count badges first
            document.querySelectorAll('.team-count-badge').forEach(badge => {
                badge.style.display = 'none';
            });
            
            // Show only the selected team's count badge
            const selectedTeamBadge = document.querySelector(`.team-count-badge[data-team="${selectedTeam}"]`);
            if (selectedTeamBadge) {
                selectedTeamBadge.style.display = 'inline-block';
            }
        }
        
        bugCards.forEach(card => {
            const cardSprint = parseInt(card.getAttribute('data-sprint'));
            const cardTeam = card.getAttribute('data-team');
            
            const sprintMatch = !isNaN(cardSprint) && cardSprint >= startSprint && cardSprint <= endSprint;
            const teamMatch = selectedTeam === 'All' || cardTeam === selectedTeam;
            
            if (sprintMatch && teamMatch) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
        
        // Update the count of visible bugs
        updateBugCount();
        
        // Automatically sort bugs by sprint after filtering
        sortBugsBySprint();
    }
    
    function updateBugCount() {
        const visibleBugs = document.querySelectorAll('.bug-card[style="display: block"]').length;
        const totalPoints = Array.from(document.querySelectorAll('.bug-card[style="display: block"]'))
            .reduce((sum, card) => {
                const pointsElement = card.querySelector('.w-8.h-8.rounded-full.bg-amber-100');
                const points = pointsElement ? parseInt(pointsElement.textContent.trim()) || 0 : 0;
                return sum + points;
            }, 0);
            
        // Update the bug count display
        const bugCountElement = document.getElementById('bugCountDisplay');
        if (bugCountElement) {
            bugCountElement.textContent = `${visibleBugs} ${visibleBugs === 1 ? 'bug' : 'bugs'} (${totalPoints} ${totalPoints === 1 ? 'point' : 'points'})`;
        }
    }
    
    // Initialize the bug count display and sort bugs automatically
    document.addEventListener('DOMContentLoaded', function() {
        updateBugCount();
        
        // Automatically sort bugs by sprint when page loads
        sortBugsBySprint();
        
        // Initialize team filtering
        const selectedTeam = document.getElementById('selectedTeam').textContent;
        if (selectedTeam !== 'All') {
            filterByTeam(selectedTeam.toLowerCase());
        } else {
            // Show total bug count and hide team count badges
            document.getElementById('totalBugCount').style.display = 'inline-block';
            document.getElementById('teamCountBadges').style.display = 'none';
        }
    });
    
    function sortBugsBySprint() {
        const bugContainer = document.querySelector('.grid.grid-cols-1.gap-4.md\\:grid-cols-2.lg\\:grid-cols-2');
        const bugCards = Array.from(document.querySelectorAll('.bug-card'));
        
        // Sort bug cards by sprint number
        bugCards.sort((a, b) => {
            const sprintA = parseInt(a.getAttribute('data-sprint')) || 999;
            const sprintB = parseInt(b.getAttribute('data-sprint')) || 999;
            return sprintA - sprintB;
        });
        
        // Clear the container
        bugCards.forEach(card => {
            card.remove();
        });
        
        // Add sorted cards back to the container
        bugCards.forEach(card => {
            bugContainer.appendChild(card);
        });
        
        // Update the count of visible bugs
        updateBugCount();
    }

    // Edit Bug Modal Functions
    function openEditModal(bugId) {
        const bugCard = document.querySelector(`.bug-card[data-id="${bugId}"]`);
        const modal = document.getElementById('editBugModal');
        const form = document.getElementById('editBugForm');

        // Set form values
        form.querySelector('#bugId').value = bugId;
        form.querySelector('#bugName').value = bugCard.querySelector('a').textContent;
        form.querySelector('#bugTeam').value = bugCard.getAttribute('data-team') || 'all';
        form.querySelector('#bugPoints').value = bugCard.querySelector('.w-8.h-8.rounded-full').textContent.trim();
        form.querySelector('#bugAssigned').value = bugCard.querySelector('.ml-2.px-2.py-1.text-xs.font-medium.rounded-full:last-child').textContent.trim();
        form.querySelector('#bugDescription').value = bugCard.querySelector('.col-span-8').textContent.trim();

        // Show modal
        modal.classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('editBugModal').classList.add('hidden');
    }

    // Edit form submission
    document.getElementById('editBugForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const bugId = this.querySelector('#bugId').value;
        const formData = new FormData(this);
        
        try {
            const response = await fetch(`/backlog/${bugId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(Object.fromEntries(formData))
            });

            const data = await response.json();

            if (response.ok) {
                // Close modal
                closeEditModal();
                
                // Update the bug card in the UI
                const bugCard = document.querySelector(`.bug-card[data-id="${bugId}"]`);
                bugCard.querySelector('a').textContent = formData.get('name');
                bugCard.setAttribute('data-team', formData.get('team'));
                bugCard.querySelector('.rounded-full').textContent = formData.get('points');
                bugCard.querySelector('.ml-2.px-2.py-1.text-xs.font-medium.rounded-full:last-child').textContent = formData.get('assigned') || 'Unassigned';
                bugCard.querySelector('.col-span-8').textContent = formData.get('description');
                
                // Show success message
                alert('Bug updated successfully');
                
                // Refresh the page to update all data
                window.location.reload();
            } else {
                throw new Error(data.error || 'Failed to update bug');
            }
        } catch (error) {
            alert(error.message);
        }
    });

    // Cancel button click handler
    document.getElementById('cancelEditBug').addEventListener('click', closeEditModal);

    // Close modal when clicking outside
    document.getElementById('editBugModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeEditModal();
        }
    });

    // Connect edit buttons to modal
    document.querySelectorAll('.bi-pencil-square').forEach(button => {
        button.closest('button').addEventListener('click', function() {
            const bugId = this.closest('.bug-card').getAttribute('data-id');
            openEditModal(bugId);
        });
    });
</script>
@endsection