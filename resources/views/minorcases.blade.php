@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    <div class="rounded-[2vw] h-full w-full bg-gray-100 p-5 ">

        <!-- Title Content -->
        <div class="flex items-center space-x-4">
            <div class="flex items-center justify-center w-20 h-20 rounded-full bg-sky-100">
                <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" fill="#13A7FD" class="bi bi-hash"
                    viewBox="0 0 16 16">
                    <path
                        d="M8.39 12.648a1 1 0 0 0-.015.18c0 .305.21.508.5.508.266 0 .492-.172.555-.477l.554-2.703h1.204c.421 0 .617-.234.617-.547 0-.312-.188-.53-.617-.53h-.985l.516-2.524h1.265c.43 0 .618-.227.618-.547 0-.313-.188-.524-.618-.524h-1.046l.476-2.304a1 1 0 0 0 .016-.164.51.51 0 0 0-.516-.516.54.54 0 0 0-.539.43l-.523 2.554H7.617l.477-2.304c.008-.04.015-.118.015-.164a.51.51 0 0 0-.523-.516.54.54 0 0 0-.531.43L6.53 5.484H5.414c-.43 0-.617.22-.617.532s.187.539.617.539h.906l-.515 2.523H4.609c-.421 0-.609.219-.609.531s.188.547.61.547h.976l-.516 2.492c-.008.04-.015.125-.015.18 0 .305.21.508.5.508.265 0 .492-.172.554-.477l.555-2.703h2.242zm-1-6.109h2.266l-.515 2.563H6.859l.532-2.563z" />
                </svg>
            </div>
            <p class="text-[#13A7FD] text-6xl font-bold italic font-sans">Minor Cases</p>
        </div>

        <!-- Container for all Sprints -->
        <div id="sprintContainer" class="mt-5">
            <!-- Sprints will be loaded here -->
        </div>

        <!-- Container for both Dropdowns and add button -->
        <div class="absolute flex space-x-4 top-14 right-10">

            <!-- Year Dropdown -->
            <div class="relative inline-block text-left py-3.5">
                <button id="yearDropdownButton"
                    class="flex items-center px-4 py-2 bg-white border rounded-[100px] shadow-md w-32 justify-between">
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
                    class="hidden absolute right-0 mt-2 w-32 bg-white border rounded-[15px] shadow-lg overflow-hidden">
                    <ul class="text-gray-700">
                        <li><a href="#" class="block py-2 border-b px-11 hover:bg-gray-200">2025</a></li>
                        <li><a href="#" class="block py-2 border-b px-11 hover:bg-gray-200">2024</a></li>
                        <li><a href="#" class="block py-2 border-b px-11 hover:bg-gray-200">2023</a></li>
                        <li><a href="#" class="block py-2 border-b px-11 hover:bg-gray-200">2022</a></li>
                        <li><a href="#" class="block py-2 border-b px-11 hover:bg-gray-200">2021</a></li>
                        <li><a href="#" class="block py-2 px-11 hover:bg-gray-200">2020</a></li>
                    </ul>
                </div>
            </div>

            <!-- Sprint Dropdown -->
            <div class="relative inline-block text-left py-3.5">
                <button id="sprintDropdownButton"
                    class="flex items-center px-4 py-2 bg-white border rounded-[100px] shadow-md w-49 justify-between">
                    <span id="selectedSprint" class="block px-6">Sprint 1 ~ 10</span>
                    <svg class="w-4 h-4 text-gray-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                    </svg>
                </button>

                <!-- Dropdown Menu -->
                <div id="sprintDropdownMenu"
                    class="hidden absolute right-0 mt-2 w-48 bg-white border rounded-[15px] shadow-lg overflow-hidden">
                    <ul class="text-gray-700">
                        <li><a href="#" class="block px-12 py-2 border-b hover:bg-gray-200">Sprint 1 ~ 10</a></li>
                        <li><a href="#" class="block py-2 border-b px-11 hover:bg-gray-200">Sprint 11 ~ 20</a></li>
                        <li><a href="#" class="block py-2 border-b px-11 hover:bg-gray-200">Sprint 21 ~ 30</a></li>
                        <li><a href="#" class="block py-2 border-b px-11 hover:bg-gray-200">Sprint 31 ~ 40</a></li>
                        <li><a href="#" class="block py-2 border-b px-11 hover:bg-gray-200">Sprint 41 ~ 50</a></li>
                        <li><a href="#" class="block py-2 px-11 hover:bg-gray-200">Sprint 51 ~ 52</a></li>
                    </ul>
                </div>
            </div>

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
                width: 16%;
                text-align: center;
            }

            .sprint-icon {
                width: 3%;
                text-align: center;
            }

            .new-sprint {
                border: #13A7FD;
            }
        </style>

        <!-- Modal for adding/editing minor cases -->
        <div id="minor-case-modal" class="fixed inset-0 z-50 hidden overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                    <div class="sm:flex sm:items-start">
                        <div class="w-full mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg font-medium leading-6 text-gray-900" id="minor-case-modal-title">Add Minor Case</h3>

                            <form id="minor-case-form" class="mt-4">
                                @csrf
                                <input type="hidden" id="minor-case-id" name="id">

                                <div class="mb-4">
                                    <label for="minor-case-board-id" class="block text-sm font-medium text-gray-700">Board</label>
                                    <select id="minor-case-board-id" name="board_id" class="block w-full px-3 py-2 mt-1 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        <option value="">Select a board</option>
                                        <!-- Options will be populated via JavaScript -->
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label for="minor-case-sprint" class="block text-sm font-medium text-gray-700">Sprint Number</label>
                                    <input type="number" id="minor-case-sprint" name="sprint" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>

                                <div class="mb-4">
                                    <label for="minor-case-card" class="block text-sm font-medium text-gray-700">Card</label>
                                    <input type="text" id="minor-case-card" name="card" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>

                                <div class="mb-4">
                                    <label for="minor-case-description" class="block text-sm font-medium text-gray-700">Description</label>
                                    <textarea id="minor-case-description" name="description" rows="3" class="block w-full mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                                </div>

                                <div class="mb-4">
                                    <label for="minor-case-member" class="block text-sm font-medium text-gray-700">Member</label>
                                    <input type="text" id="minor-case-member" name="member" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>

                                <div class="mb-4">
                                    <label for="minor-case-points" class="block text-sm font-medium text-gray-700">Points</label>
                                    <input type="number" step="0.5" id="minor-case-points" name="points" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>

                                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                                    <button type="submit" class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                        Save
                                    </button>
                                    <button type="button" id="cancel-minor-case" class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                // Function to load minor cases data from API
                function loadMinorCases() {
                    // Show loading indicator
                    const sprintContainer = document.getElementById('sprintContainer');
                    sprintContainer.innerHTML = `
                        <div class="flex items-center justify-center p-4 mb-4 bg-white rounded-lg">
                            <div class="inline-block w-6 h-6 mr-2 border-t-2 border-b-2 border-blue-500 rounded-full animate-spin"></div>
                            <span>Loading minor cases...</span>
                        </div>
                    `;

                    // First fetch available boards - API doesn't allow loading all data without a board_id
                    fetch('/api/boards')
                        .then(response => response.json())
                        .then(boards => {
                            console.log('Available boards:', boards);

                            if (boards.length === 0) {
                                // No boards available
                                sprintContainer.innerHTML = `
                                    <div class="p-4 mb-4 text-center bg-white rounded-lg">
                                        <p class="text-gray-500">No boards available. Please configure your Trello integration.</p>
                                    </div>
                                `;
                                return;
                            }

                            // Process all boards to get minor cases
                            Promise.all(boards.map(board =>
                                fetch(`/api/minor-cases?board_id=${board.id}`)
                                    .then(response => response.json())
                                    .then(data => data.data || []) // Access the 'data' property of the response
                                    .catch(error => {
                                        console.error(`Error fetching cases for board ${board.id}:`, error);
                                        return [];
                                    })
                            ))
                            .then(boardResults => {
                                // Flatten array of arrays and add board information
                                const allMinorCases = boardResults.flat().map((item, index) => {
                                    const boardInfo = boards.find(b => b.id === item.board_id) || { name: 'Unknown Board' };
                                    return {
                                        ...item,
                                        board_name: boardInfo.name
                                    };
                                });

                                console.log('Processed minor cases:', allMinorCases);
                                populateSprintsFromData(allMinorCases);
                            })
                            .catch(error => {
                                console.error('Error processing minor cases:', error);
                                // Show error message
                                const errorDiv = document.createElement('div');
                                errorDiv.className = 'bg-red-100 text-red-700 p-4 rounded-lg mb-4';
                                errorDiv.innerHTML = `
                                    <p class="font-bold">Error Loading Data</p>
                                    <p>${error.message}</p>
                                `;
                                sprintContainer.innerHTML = '';
                                sprintContainer.appendChild(errorDiv);
                            });
                        })
                        .catch(error => {
                            console.error('Error loading boards:', error);
                            // Show error message
                            const errorDiv = document.createElement('div');
                            errorDiv.className = 'bg-red-100 text-red-700 p-4 rounded-lg mb-4';
                            errorDiv.innerHTML = `
                                <p class="font-bold">Error Loading Boards</p>
                                <p>${error.message}</p>
                            `;
                            sprintContainer.innerHTML = '';
                            sprintContainer.appendChild(errorDiv);
                        });
                }

                // Function to populate sprints from data
                function populateSprintsFromData(data) {
                    const sprintContainer = document.getElementById('sprintContainer');
                    sprintContainer.innerHTML = '';

                    // Group data by sprint
                    const sprintGroups = {};

                    data.forEach(item => {
                        const sprintNumber = item.sprint_number || item.sprint;
                        if (!sprintGroups[sprintNumber]) {
                            sprintGroups[sprintNumber] = [];
                        }
                        sprintGroups[sprintNumber].push(item);
                    });

                    // Sort sprint numbers in descending order (highest first)
                    const sortedSprintNumbers = Object.keys(sprintGroups).sort((a, b) => b - a);

                    if (sortedSprintNumbers.length === 0) {
                        // No data available
                        sprintContainer.innerHTML = `
                            <div class="p-4 mb-4 text-center bg-white rounded-lg">
                                <p class="text-gray-500">No minor cases found. Use the + button to add new cases.</p>
                            </div>
                        `;
                        return;
                    }

                    // Create sprint sections for each group
                    sortedSprintNumbers.forEach(sprintNumber => {
                        const sprintItems = sprintGroups[sprintNumber];
                        const sprintDiv = createSprintDiv(sprintNumber, sprintItems);
                        sprintContainer.appendChild(sprintDiv);
                    });

                    // Set up event listeners
                    setupSprintEventListeners();

                    // Update displayed sprints based on filters
                    updateDisplayedSprints();
                }

                // Create a sprint div with data
                function createSprintDiv(sprintNumber, sprintItems) {
                    const sprintDiv = document.createElement("div");
                    sprintDiv.className = "bg-white rounded-3xl p-0 mb-5 sprint-block w-49";
                    sprintDiv.dataset.sprintNumber = sprintNumber;

                    // Check if the sprint has data
                    const hasData = sprintItems && sprintItems.length > 0;
                    const dataClass = hasData ? "sprint-from-report" : "";

                    // Get the date range for this sprint
                    const dateRange = getSprintDateRange(parseInt(sprintNumber));

                    sprintDiv.innerHTML = `
                        <div class="flex items-center h-12 px-10 py-3 text-lg font-bold text-blue-700 cursor-pointer rounded-3xl sprint-header bg-sky-100">
                            <span class="mr-2 text-[#13A7FD] text-1xl" style="width: 9%;">Sprint #${sprintNumber}</span>
                            <button class="ml-3 px-3 py-1 text-white text-left bg-[#13A7FD] rounded-full add-minor-case-btn hover:bg-blue-600" data-sprint="${sprintNumber}">+</button>
                            <span style="width: 70%;"></span>
                            <span class="ml-20 text-sm font-normal mb-2 lace-items-end sprint-date text-[#13A7FD] mt-3 bg-white rounded-3xl px-2 py-1">${dateRange}</span>
                            <span class="collapse-icon text-[#13A7FD] sprint-icon px-5 text-right">▲</span>
                        </div>

                        <div class="p-4 mt-2 bg-white sprint-content rounded-3xl" style="display: block;" ${dataClass}">
                            <table class="w-full border border-collapse bg gray-200">
                                <thead class="rounded-full bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 border border-white"><span class="px-10 py-1 mr-2 bg-white rounded-full text-[#13A7FD] font-bold pt-1 pb-1 shadow-md">Number</span></th>
                                        <th class="px-4 py-2 border border-white"><span class="px-10 py-1 mr-2 bg-white rounded-full text-[#13A7FD] font-bold pt-1 pb-1 shadow-md">Card</span></th>
                                        <th class="px-4 py-2 border border-white"><span class="px-10 py-1 mr-2 bg-white rounded-full text-[#13A7FD] font-bold pt-1 pb-1 shadow-md">Description</span></th>
                                        <th class="px-4 py-2 border border-white"><span class="px-10 py-1 mr-2 bg-white rounded-full text-[#13A7FD] font-bold pt-1 pb-1 shadow-md">Member</span></th>
                                        <th class="px-4 py-2 border border-white"><span class="px-8 py-1 mr-2 bg-white rounded-full text-[#13A7FD] font-bold shadow-md">Points</span></th>
                                        <th class="px-4 py-2 border border-white w-54 text-[#13A7FD]"><span class="px-10 py-1 mr-2 bg-white rounded-full text-[#13A7FD] font-bold pt-1 pb-1 shadow-md">Actions</span></th>
                                    </tr>
                                </thead>
                                <tbody class="card-list">
                                    ${hasData ? renderCardRows(sprintItems) : '<tr class="italic text-gray-500 no-data"><td colspan="6" class="py-2 text-center">No data available</td></tr>'}
                                </tbody>
                            </table>
                        </div>
                    `;

                    return sprintDiv;
                }

                // Render card rows from sprint items
                function renderCardRows(items) {
                    return items.map(item => `
                        <tr data-id="${item.id}">
                            <td class="px-4 py-2 text-center border border-white">
                                <span class="px-14 py-1 mr-2 bg-white border border-[#13A7FD] rounded-full text-[#13A7FD] font-bold pt-1 pb-1">#${item.number || item.sprint || ''}</span>
                            </td>
                            <td class="px-4 py-2 text-center border border-white">${item.card_detail || item.card || ''}</td>
                            <td class="py-2 text-center border border-white px-15" style="width: 40%;">
                                <div class="overflow-y-auto max-h-20">
                                    ${item.description || 'No description'}
                                </div>
                            </td>
                            <td class="px-4 py-2 text-center border border-white">
                                <span class="px-2 py-1 mr-2 text-sm font-bold text-[#65BC23] rounded-3xl bg-[#DDFFEC]">${item.board_name || item.teamName || ''}</span>
                                ${item.member || ''}
                            </td>
                            <td class="px-4 py-2 text-center border border-white">
                                <span class="px-16 py-1 mr-2 bg-[#BAF3FF] rounded-full text-[#13A7FD] font-bold pt-1 pb-1">${parseFloat(item.points || item.point || 0).toFixed(1)}pt.</span>
                            </td>
                            <td class="px-4 py-2 text-center border border-white">
                                <button class="px-2 py-2 mr-1 text-white bg-[#FFC7B2] rounded-full edit-btn hover:bg-yellow-600" data-id="${item.id}">
                                    <svg class="w-5 h-5 text-[#985E00] hover:text-white" width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                    </svg>
                                </button>
                                <button class="px-2 py-2 text-[#FF0004] bg-[#FFACAE] rounded-full delete-btn hover:bg-red-600" data-id="${item.id}">
                                    <svg class="w-5 h-5 text-2xl text-[#FF0004] hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    `).join('');
                }

                // Load available boards and populate dropdown
                function loadBoards() {
                    const boardSelect = document.getElementById('minor-case-board-id');

                    // Clear existing options except the first placeholder
                    while (boardSelect.options.length > 1) {
                        boardSelect.remove(1);
                    }

                    fetch('/api/boards')
                        .then(response => response.json())
                        .then(boards => {
                            console.log('Available boards for dropdown:', boards);

                            if (boards.length === 0) {
                                const option = document.createElement('option');
                                option.textContent = 'No boards available';
                                option.disabled = true;
                                boardSelect.appendChild(option);
                            } else {
                                boards.forEach(board => {
                                    const option = document.createElement('option');
                                    option.value = board.id;
                                    option.textContent = board.name;
                                    boardSelect.appendChild(option);
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error loading boards for dropdown:', error);
                            const option = document.createElement('option');
                            option.textContent = 'Error loading boards';
                            option.disabled = true;
                            boardSelect.appendChild(option);
                        });
                }

                // Edit minor case function
                function editMinorCase(id) {
                    fetch(`/api/minor-cases/${id}`)
                        .then(response => response.json())
                        .then(data => {
                            console.log('Minor case to edit:', data);

                            // Make sure boards are loaded first
                            loadBoards();

                            // Populate form fields with data
                            document.getElementById('minor-case-id').value = data.id;

                            // Set timeout to allow board dropdown to populate first
                            setTimeout(() => {
                                const boardSelect = document.getElementById('minor-case-board-id');
                                boardSelect.value = data.board_id;

                                document.getElementById('minor-case-sprint').value = data.sprint;
                                document.getElementById('minor-case-card').value = data.card;
                                document.getElementById('minor-case-description').value = data.description || '';
                                document.getElementById('minor-case-member').value = data.member;
                                document.getElementById('minor-case-points').value = data.points;

                                // Show modal
                                document.getElementById('minor-case-modal').classList.remove('hidden');
                                document.getElementById('minor-case-modal-title').textContent = 'Edit Minor Case';
                            }, 200);
                        })
                        .catch(error => {
                            console.error('Error loading minor case details:', error);
                            alert('Error loading minor case details: ' + error.message);
                        });
                }

                // Delete minor case function
                function deleteMinorCase(id) {
                    if (confirm('Are you sure you want to delete this minor case?')) {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

                        fetch(`/api/minor-cases/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Failed to delete minor case');
                            }

                            // Reload data after successful deletion
                            loadMinorCases();

                            // Show success message
                            alert('Minor case deleted successfully');
                        })
                        .catch(error => {
                            console.error('Error deleting minor case:', error);
                            alert('Error deleting minor case: ' + error.message);
                        });
                    }
                }

                // Function to get date range for a sprint
                function getSprintDateRange(sprintNumber) {
                    const currentYear = parseInt(document.getElementById('selectedYear').textContent) || new Date().getFullYear();
                    const startDate = new Date(currentYear, 0, 1);
                    startDate.setDate(startDate.getDate() + (sprintNumber - 1) * 7);

                    const endDate = new Date(startDate);
                    endDate.setDate(startDate.getDate() + 6);

                    const options = { day: 'numeric', month: 'long' };
                    const startDateStr = startDate.getDate();
                    const endDateStr = endDate.toLocaleDateString('en-GB', options);

                    return `${startDateStr} - ${endDateStr} ${currentYear}`;
                }

                // Set up event listeners for sprint elements
                function setupSprintEventListeners() {
                    // Toggle collapse/expand
                    document.querySelectorAll('.sprint-header').forEach(header => {
                        header.addEventListener('click', () => {
                            const content = header.nextElementSibling;
                            const icon = header.querySelector('.collapse-icon');

                            if (content.style.display === 'none') {
                                content.style.display = 'block';
                                icon.textContent = '▲';
                            } else {
                                content.style.display = 'none';
                                icon.textContent = '▼';
                            }
                        });
                    });

                    // Edit button handlers
                    document.querySelectorAll('.edit-btn').forEach(button => {
                        button.addEventListener('click', function(e) {
                            e.stopPropagation();
                            const id = this.dataset.id;
                            editMinorCase(id);
                        });
                    });

                    // Delete button handlers
                    document.querySelectorAll('.delete-btn').forEach(button => {
                        button.addEventListener('click', function(e) {
                            e.stopPropagation();
                            const id = this.dataset.id;
                            deleteMinorCase(id);
                        });
                    });

                    // Add button handlers
                    document.querySelectorAll('.add-minor-case-btn').forEach(button => {
                        button.addEventListener('click', function(e) {
                            e.stopPropagation();

                            // Load boards for dropdown
                            loadBoards();

                            // Reset form
                            document.getElementById('minor-case-form').reset();
                            document.getElementById('minor-case-id').value = '';
                            document.getElementById('minor-case-sprint').value = this.dataset.sprint;

                            // Show modal with title
                            document.getElementById('minor-case-modal').classList.remove('hidden');
                            document.getElementById('minor-case-modal-title').textContent = 'Add Minor Case';
                        });
                    });
                }

                // Update displayed sprints based on filters
                function updateDisplayedSprints() {
                    const sprintRange = document.getElementById('selectedSprint').textContent;
                    const selectedYear = document.getElementById('selectedYear').textContent;

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

                // Add minor case form handler
                const minorCaseForm = document.getElementById('minor-case-form');
                if (minorCaseForm) {
                    minorCaseForm.addEventListener('submit', function(e) {
                        e.preventDefault();

                        const id = document.getElementById('minor-case-id').value;
                        const boardId = document.getElementById('minor-case-board-id').value;
                        const sprint = document.getElementById('minor-case-sprint').value;
                        const card = document.getElementById('minor-case-card').value;
                        const description = document.getElementById('minor-case-description').value;
                        const member = document.getElementById('minor-case-member').value;
                        const points = document.getElementById('minor-case-points').value;

                        if (!boardId) {
                            alert('Please select a board before saving');
                            return;
                        }

                        const data = {
                            board_id: boardId,
                            sprint: sprint,
                            card: card,
                            description: description,
                            member: member,
                            points: parseFloat(points)
                        };

                        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                        const url = id ? `/api/minor-cases/${id}` : '/api/minor-cases';
                        const method = id ? 'PUT' : 'POST';

                        console.log('Submitting data:', data);

                        fetch(url, {
                            method: method,
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(data)
                        })
                        .then(response => {
                            if (!response.ok) {
                                return response.json().then(errData => {
                                    if (response.status === 422) {
                                        // Validation errors
                                        const errorMessages = Object.values(errData.errors || {}).flat().join('\n');
                                        throw new Error(errorMessages || `Failed to ${id ? 'update' : 'add'} minor case`);
                                    }
                                    throw new Error(`Failed to ${id ? 'update' : 'add'} minor case: ${errData.message || response.statusText}`);
                                });
                            }
                            return response.json();
                        })
                        .then(data => {
                            console.log('Success:', data);

                            // Reset form and close modal
                            minorCaseForm.reset();
                            document.getElementById('minor-case-modal').classList.add('hidden');

                            // Reload data
                            loadMinorCases();

                            // Show success message
                            alert(`Minor case ${id ? 'updated' : 'added'} successfully`);
                        })
                        .catch(error => {
                            console.error(`Error ${id ? 'updating' : 'adding'} minor case:`, error);
                            alert(`Error ${id ? 'updating' : 'adding'} minor case: ${error.message}`);
                        });
                    });
                }

                // Close modal button
                const cancelMinorCaseBtn = document.getElementById('cancel-minor-case');
                if (cancelMinorCaseBtn) {
                    cancelMinorCaseBtn.addEventListener('click', function() {
                        document.getElementById('minor-case-modal').classList.add('hidden');
                    });
                }

                // Add button for create new sprint
                const addSprintBtn = document.getElementById('addSprintBtn');
                if (addSprintBtn) {
                    addSprintBtn.addEventListener('click', function() {
                        // Calculate the next sprint number
                        const sprintBlocks = document.querySelectorAll('.sprint-block');
                        let maxSprintNum = 0;
                        sprintBlocks.forEach(block => {
                            const sprintNum = parseInt(block.dataset.sprintNumber);
                            if (sprintNum > maxSprintNum) {
                                maxSprintNum = sprintNum;
                            }
                        });

                        const newSprintNum = maxSprintNum + 1;
                        const newSprintDiv = createSprintDiv(newSprintNum, []);

                        // Add to container at the top
                        document.getElementById('sprintContainer').prepend(newSprintDiv);

                        // Set up event listeners
                        setupSprintEventListeners();

                        // Update displayed sprints
                        updateDisplayedSprints();
                    });
                }

                // Setup dropdowns
                function setupDropdown(buttonId, menuId, selectedId) {
                    document.getElementById(buttonId).addEventListener("click", function() {
                        document.getElementById(menuId).classList.toggle("hidden");
                    });

                    document.querySelectorAll(`#${menuId} a`).forEach(item => {
                        item.addEventListener("click", function() {
                            document.getElementById(selectedId).textContent = this.textContent;
                            document.getElementById(menuId).classList.add("hidden");

                            // Update displayed sprints when sprint range changes
                            if (menuId === "sprintDropdownMenu" || menuId === "yearDropdownMenu") {
                                updateDisplayedSprints();
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

                // Load data on page load
                loadMinorCases();

                // Pre-load boards for faster modal opening
                loadBoards();
            });
        </script>
    </div>
@endsection
