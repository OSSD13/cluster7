@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    <div class="rounded-[2vw] h-full w-full bg-gray-100 p-5">
        <div class="flex items-center space-x-4">
            <div class="w-20 h-20 rounded-full bg-sky-100 flex justify-center items-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" fill="#13A7FD" class="bi bi-hash"
                    viewBox="0 0 16 16">
                    <path
                        d="M8.39 12.648a1 1 0 0 0-.015.18c0 .305.21.508.5.508.266 0 .492-.172.555-.477l.554-2.703h1.204c.421 0 .617-.234.617-.547 0-.312-.188-.53-.617-.53h-.985l.516-2.524h1.265c.43 0 .618-.227.618-.547 0-.313-.188-.524-.618-.524h-1.046l.476-2.304a1 1 0 0 0 .016-.164.51.51 0 0 0-.516-.516.54.54 0 0 0-.539.43l-.523 2.554H7.617l.477-2.304c.008-.04.015-.118.015-.164a.51.51 0 0 0-.523-.516.54.54 0 0 0-.531.43L6.53 5.484H5.414c-.43 0-.617.22-.617.532s.187.539.617.539h.906l-.515 2.523H4.609c-.421 0-.609.219-.609.531s.188.547.61.547h.976l-.516 2.492c-.008.04-.015.125-.015.18 0 .305.21.508.5.508.265 0 .492-.172.554-.477l.555-2.703h2.242zm-1-6.109h2.266l-.515 2.563H6.859l.532-2.563z" />
                </svg>
            </div>
            <p class="text-[#13A7FD] text-6xl font-bold italic">Minor Cases</p>
        </div>

        <!-- Container for all Sprints -->
        <div id="sprintContainer" class="mt-5">
            <!-- Sprints will be loaded here -->
        </div>

        <!-- Container for both Dropdowns and add button -->
        <div class="absolute top-14 right-10 flex space-x-4">

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
                        <li><a href="#" class="block px-11 py-2 hover:bg-gray-200 border-b">2025</a></li>
                        <li><a href="#" class="block px-11 py-2 hover:bg-gray-200 border-b">2024</a></li>
                        <li><a href="#" class="block px-11 py-2 hover:bg-gray-200 border-b">2023</a></li>
                        <li><a href="#" class="block px-11 py-2 hover:bg-gray-200 border-b">2022</a></li>
                        <li><a href="#" class="block px-11 py-2 hover:bg-gray-200 border-b">2021</a></li>
                        <li><a href="#" class="block px-11 py-2 hover:bg-gray-200">2020</a></li>
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
                        <li><a href="#" class="block px-12 py-2 hover:bg-gray-200 border-b">Sprint 1 ~ 10</a></li>
                        <li><a href="#" class="block px-11 py-2 hover:bg-gray-200 border-b">Sprint 11 ~ 20</a></li>
                        <li><a href="#" class="block px-11 py-2 hover:bg-gray-200 border-b">Sprint 21 ~ 30</a></li>
                        <li><a href="#" class="block px-11 py-2 hover:bg-gray-200 border-b">Sprint 31 ~ 40</a></li>
                        <li><a href="#" class="block px-11 py-2 hover:bg-gray-200 border-b">Sprint 41 ~ 50</a></li>
                        <li><a href="#" class="block px-11 py-2 hover:bg-gray-200">Sprint 51 ~ 52</a></li>
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
                width: 100%;
                text-align: center;
            }
        </style>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                // Load minor cases data from API
                function loadMinorCases() {
                    // Show loading indicator
                    const sprintContainer = document.getElementById('sprintContainer');
                    sprintContainer.innerHTML = `
                        <div class="bg-white rounded-lg p-4 mb-4 flex items-center justify-center">
                            <div class="inline-block animate-spin rounded-full h-6 w-6 border-t-2 border-b-2 border-blue-500 mr-2"></div>
                            <span>Loading minor cases...</span>
                        </div>
                    `;
                    
                    fetch('{{ route("minor-cases.data") }}')
                        .then(response => response.json())
                        .then(data => {
                            // Process the data
                            console.log('Loaded minor cases:', data);
                            populateSprintsFromData(data);
                        })
                        .catch(error => {
                            console.error('Error loading minor cases data:', error);
                            // Show error message
                            const errorDiv = document.createElement('div');
                            errorDiv.className = 'bg-red-100 text-red-700 p-4 rounded-lg mb-4';
                            errorDiv.innerHTML = `
                                <p class="font-bold">Error Loading Data</p>
                                <p>${error.message}</p>
                            `;
                            document.getElementById('sprintContainer').innerHTML = '';
                            document.getElementById('sprintContainer').appendChild(errorDiv);
                        });
                }

                // Edit minor case
                function editMinorCase(id) {
                    // Get the minor case details
                    fetch(`/api/minor-cases/${id}`)
                        .then(response => response.json())
                        .then(data => {
                            console.log('Minor case to edit:', data);
                            // Populate form fields with data
                            document.getElementById('minor-case-id').value = data.id;
                            document.getElementById('minor-case-board-id').value = data.board_id;
                            document.getElementById('minor-case-sprint').value = data.sprint;
                            document.getElementById('minor-case-card').value = data.card;
                            document.getElementById('minor-case-description').value = data.description || '';
                            document.getElementById('minor-case-member').value = data.member;
                            document.getElementById('minor-case-points').value = data.points;
                            
                            // Show modal
                            document.getElementById('minor-case-modal').classList.remove('hidden');
                            document.getElementById('minor-case-modal-title').textContent = 'Edit Minor Case';
                        })
                        .catch(error => {
                            console.error('Error loading minor case details:', error);
                            alert('Error loading minor case details: ' + error.message);
                        });
                }

                // Delete minor case
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

                // Create a sprint div with data
                function createSprintDiv(sprintNumber, sprintItems) {
                    const sprintDiv = document.createElement("div");
                    sprintDiv.className = "bg-blue-100 rounded-lg p-3 mb-3 sprint-block";
                    sprintDiv.dataset.sprintNumber = sprintNumber;

                    // Get the date range
                    const dateRange = getSprintDateRange(parseInt(sprintNumber));

                    sprintDiv.innerHTML = `
                        <div class="sprint-header flex justify-between items-center cursor-pointer text-blue-700 font-bold text-lg">
                            <span>Sprint #${sprintNumber} <span class="sprint-date text-sm text-center font-normal">${dateRange}</span></span>
                            <span class="collapse-icon">▲</span>
                        </div>
                        <div class="sprint-content mt-2 bg-white p-3 rounded-lg shadow-md">
                            <table class="w-full border-collapse border border-gray-300">
                                <thead>
                                    <tr class="bg-blue-200">
                                        <th class="border border-gray-300 px-4 py-2">Sprint</th>
                                        <th class="border border-gray-300 px-4 py-2">Card</th>
                                        <th class="border border-gray-300 px-4 py-2">Description</th>
                                        <th class="border border-gray-300 px-4 py-2">Member</th>
                                        <th class="border border-gray-300 px-4 py-2">Points</th>
                                        <th class="border border-gray-300 px-4 py-2 w-54">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="card-list">
                                    ${sprintItems.length > 0 ? renderCardRows(sprintItems) : '<tr class="text-gray-500 italic no-data"><td colspan="6" class="text-center py-2">No data available</td></tr>'}
                                </tbody>
                            </table>
                            <div class="mt-4">
                                <button class="add-case-btn bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded" data-sprint="${sprintNumber}">
                                    Add Minor Case
                                </button>
                            </div>
                        </div>
                    `;

                    return sprintDiv;
                }

                // Render card rows from sprint items
                function renderCardRows(items) {
                    return items.map(item => `
                        <tr data-id="${item.id}">
                            <td class="border border-gray-300 px-4 py-2 text-center">${item.sprint_number || ''}</td>
                            <td class="border border-gray-300 px-4 py-2">${item.card || ''}</td>
                            <td class="border border-gray-300 px-4 py-2" style="max-width: 400px;">
                                <div class="max-h-28 overflow-y-auto">
                                    ${item.description || 'No description'}
                                </div>
                            </td>
                            <td class="border border-gray-300 px-4 py-2 text-center">${item.member || ''}</td>
                            <td class="border border-gray-300 px-4 py-2 text-center">${parseFloat(item.points || 0).toFixed(1)}</td>
                            <td class="border border-gray-300 px-4 py-2 text-center">
                                <div class="flex justify-center space-x-2">
                                    <button class="edit-btn bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded mr-1" data-id="${item.id}">
                                        <svg class="h-5 w-5 text-stone-800" width="24" height="24" viewBox="0 0 24 24"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                        </svg>
                                    </button>
                                    <button class="delete-btn bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded" data-id="${item.id}">
                                        <svg class="h-5 w-5 text-red-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `).join('');
                }

                // Calculate sprint date range
                function getSprintDateRange(sprintNumber) {
                    const currentYear = new Date().getFullYear();
                    const startDate = new Date(currentYear, 0, 1);
                    startDate.setDate(startDate.getDate() + (sprintNumber - 1) * 7);
                    
                    const endDate = new Date(startDate);
                    endDate.setDate(startDate.getDate() + 6);
                    
                    return `${startDate.toLocaleDateString('en-US', {month: 'short', day: 'numeric'})} - ${endDate.toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'})}`;
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

                    // Add case button handlers
                    document.querySelectorAll('.add-case-btn').forEach(button => {
                        button.addEventListener('click', function() {
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
                                if (response.status === 422) {
                                    return response.json().then(data => {
                                        throw new Error(Object.values(data.errors).flat().join('\n'));
                                    });
                                }
                                throw new Error(`Failed to ${id ? 'update' : 'add'} minor case`);
                            }
                            return response.json();
                        })
                        .then(data => {
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

                // Populate sprints with data
                function populateSprintsFromData(data) {
                    const sprintContainer = document.getElementById('sprintContainer');
                    sprintContainer.innerHTML = '';

                    // Group data by sprint
                    const sprintGroups = {};

                    data.forEach(item => {
                        if (!sprintGroups[item.sprint_number]) {
                            sprintGroups[item.sprint_number] = [];
                        }
                        sprintGroups[item.sprint_number].push(item);
                    });

                    // Sort sprint numbers in descending order (highest first)
                    const sortedSprintNumbers = Object.keys(sprintGroups).sort((a, b) => b - a);

                    // Create sprint sections for each group
                    sortedSprintNumbers.forEach(sprintNumber => {
                        const sprintItems = sprintGroups[sprintNumber];
                        sprintContainer.appendChild(createSprintDiv(sprintNumber, sprintItems));
                    });

                    // Set up event handlers
                    setupSprintEventListeners();
                }

                // Load data on page load
                loadMinorCases();
            });
        </script>
    </div>

    <!-- Minor Case Modal -->
    <div id="minor-case-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 id="minor-case-modal-title" class="text-lg leading-6 font-medium text-gray-900">Add Minor Case</h3>
                <div class="mt-2 px-7 py-3">
                    <form id="minor-case-form" class="space-y-4">
                        <input type="hidden" id="minor-case-id">
                        
                        <div class="flex flex-col">
                            <label for="minor-case-board-id" class="text-left mb-1">Board ID</label>
                            <select id="minor-case-board-id" class="border rounded px-3 py-2" required>
                                <option value="">Select a board</option>
                                <!-- Boards will be populated dynamically -->
                            </select>
                        </div>
                        
                        <div class="flex flex-col">
                            <label for="minor-case-sprint" class="text-left mb-1">Sprint</label>
                            <input type="text" id="minor-case-sprint" class="border rounded px-3 py-2" required>
                        </div>
                        
                        <div class="flex flex-col">
                            <label for="minor-case-card" class="text-left mb-1">Card</label>
                            <input type="text" id="minor-case-card" class="border rounded px-3 py-2" required>
                        </div>
                        
                        <div class="flex flex-col">
                            <label for="minor-case-description" class="text-left mb-1">Description</label>
                            <textarea id="minor-case-description" class="border rounded px-3 py-2" rows="3"></textarea>
                        </div>
                        
                        <div class="flex flex-col">
                            <label for="minor-case-member" class="text-left mb-1">Member</label>
                            <input type="text" id="minor-case-member" class="border rounded px-3 py-2" required>
                        </div>
                        
                        <div class="flex flex-col">
                            <label for="minor-case-points" class="text-left mb-1">Points</label>
                            <input type="number" id="minor-case-points" class="border rounded px-3 py-2" step="0.1" min="0" required value="0">
                        </div>
                        
                        <div class="flex justify-end mt-4 space-x-4">
                            <button type="button" id="cancel-minor-case" class="px-4 py-2 bg-gray-300 text-black rounded hover:bg-gray-400">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Load available boards for dropdown
        document.addEventListener('DOMContentLoaded', function() {
            const boardSelect = document.getElementById('minor-case-board-id');
            
            fetch('/api/boards')
                .then(response => response.json())
                .then(data => {
                    // Fallback if API not available - use a default board
                    if (!data || !data.length) {
                        boardSelect.innerHTML = '<option value="6429c8bd5c54e0050731a095">Default Board</option>';
                        return;
                    }
                    
                    // Populate boards dropdown
                    data.forEach(board => {
                        const option = document.createElement('option');
                        option.value = board.id;
                        option.textContent = board.name;
                        boardSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error loading boards:', error);
                    // Fallback
                    boardSelect.innerHTML = '<option value="6429c8bd5c54e0050731a095">Default Board</option>';
                });
        });
    </script>
@endsection
