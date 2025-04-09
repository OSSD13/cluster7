@extends('layouts.app')

@section('title', 'Minor Cases')
@section('page-title', 'Minor Cases')

@section('content')
    <div class="rounded-[2vw] h-full w-full bg-gray-100 p-5">

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

        <!-- Container for both Dropdowns -->
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
            <div class="relative inline-block text-left py-3.5 mb-10">
                <button id="sprintDropdownButton"
                    class="flex items-center px-4 py-2 bg-white border rounded-[100px] shadow-md w-49 justify-between">
                    <span id="selectedSprint" class="block px-6 ">Sprint 1 ~ 10</span>
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

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sprint</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Card</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Points</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($minorCases as $case)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">{{ $case->sprint }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $case->card }}</td>
                            <td class="px-6 py-4">{{ $case->description ?? '' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $case->member }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $case->points }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button
                                    class="px-2 py-2 mr-1 text-white bg-[#FFC7B2] rounded-full edit-btn hover:bg-yellow-600"
                                    data-id="{{ $case->id }}">
                                    <svg class="w-5 h-5 text-[#985E00] hover:text-white" width="24" height="24"
                                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                    </svg>
                                </button>
                                <button class="px-2 py-2 text-[#FF0004] bg-[#FFACAE] rounded-full delete-btn hover:bg-red-600"
                                    data-id="{{ $case->id }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hover:text-white" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                No minor cases found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Modal for Edit -->
        <div id="editModal" class="fixed justify-center inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full" style="z-index: 1000;">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white max-w-md">
                <h2 class="text-xl font-bold mb-4 text-gray-800">Edit Minor Case</h2>
                <form id="editForm">
                    <input type="hidden" id="editId">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="editSprint">Sprint</label>
                        <input type="text" id="editSprint" name="sprint" class="w-full px-3 py-2 border rounded shadow appearance-none">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="editCard">Card</label>
                        <input type="text" id="editCard" name="card" class="w-full px-3 py-2 border rounded shadow appearance-none">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="editDescription">Description</label>
                        <textarea id="editDescription" name="description" class="w-full px-3 py-2 border rounded shadow appearance-none"></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="editMember">Member</label>
                        <select id="editMember" name="member" class="w-full px-3 py-2 border rounded shadow appearance-none">
                            <option value="">Select Member</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="editPoints">Points</label>
                        <input type="number" id="editPoints" name="points" class="w-full px-3 py-2 border rounded shadow appearance-none">
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" id="cancelEdit" class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Confirmation Modal for Delete -->
        <div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center">
            <div class="bg-white p-6 rounded-lg shadow-lg max-w-md w-full">
                <h2 class="text-xl font-bold mb-4 text-gray-800">Confirm Deletion</h2>
                <p class="mb-6">Are you sure you want to delete this minor case? This action cannot be undone.</p>
                <input type="hidden" id="deleteId">
                <div class="flex justify-end space-x-3">
                    <button type="button" id="cancelDelete" class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">Cancel</button>
                    <button type="button" id="confirmDelete" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">Delete</button>
                </div>
            </div>
        </div>

        <style>
            .hidden {
                display: none !important;
            }

            .block {
                display: block;
            }

            .flex {
                display: flex;
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

            /* Animation for modals */
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }

            #editModal, #deleteModal {
                animation: fadeIn 0.3s ease;
            }
        </style>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                // Constants
                const SPRINT_YEAR = 2025;

                // DOM Elements
                const editModal = document.getElementById('editModal');
                const deleteModal = document.getElementById('deleteModal');
                const editForm = document.getElementById('editForm');
                const cancelEdit = document.getElementById('cancelEdit');
                const cancelDelete = document.getElementById('cancelDelete');
                const confirmDelete = document.getElementById('confirmDelete');

                // Setup Dropdowns
                setupDropdowns();

                // Setup Edit Button Events
                document.querySelectorAll('.edit-btn').forEach(button => {
                    button.addEventListener('click', function(e) {
                        e.preventDefault();
                        const id = this.getAttribute('data-id');
                        openEditModal(id);
                    });
                });

                // Setup Delete Button Events
                document.querySelectorAll('.delete-btn').forEach(button => {
                    button.addEventListener('click', function(e) {
                        e.preventDefault();
                        const id = this.getAttribute('data-id');
                        openDeleteModal(id);
                    });
                });

                // Cancel Edit Event
                cancelEdit.addEventListener('click', function() {
                    editModal.classList.add('hidden');
                });

                // Cancel Delete Event
                cancelDelete.addEventListener('click', function() {
                    deleteModal.classList.add('hidden');
                });

                // Confirm Delete Event
                confirmDelete.addEventListener('click', function() {
                    const id = document.getElementById('deleteId').value;
                    // Send AJAX request to delete the record
                    deleteMinorCase(id);
                });

                // Edit Form Submit
                editForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const id = document.getElementById('editId').value;
                    updateMinorCase(id);
                });

                // Close modals when clicking outside
                window.addEventListener('click', function(e) {
                    if (e.target === editModal) {
                        editModal.classList.add('hidden');
                    }
                    if (e.target === deleteModal) {
                        deleteModal.classList.add('hidden');
                    }
                });

                // Setup Sprint Events
                setupSprintEvents();

                // Functions
                function setupDropdowns() {
                    setupDropdown("yearDropdownButton", "yearDropdownMenu", "selectedYear");
                    setupDropdown("sprintDropdownButton", "sprintDropdownMenu", "selectedSprint");
                }

                function setupDropdown(buttonId, menuId, selectedId) {
                    document.getElementById(buttonId).addEventListener("click", function() {
                        document.getElementById(menuId).classList.toggle("hidden");
                    });

                    document.querySelectorAll(`#${menuId} a`).forEach(item => {
                        item.addEventListener("click", function() {
                            document.getElementById(selectedId).textContent = this.textContent;
                            document.getElementById(menuId).classList.add("hidden");

                            if (menuId === "sprintDropdownMenu") {
                                updateDisplayedSprints();
                            } else if (menuId === "yearDropdownMenu") {
                                updateDisplayedDate();
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

                function openEditModal(id) {
                    // Fetch record data via AJAX
                    fetch(`/minor-cases/${id}`)
                        .then(response => response.json())
                        .then(data => {
                            document.getElementById('editId').value = id;
                            document.getElementById('editSprint').value = data.sprint;
                            document.getElementById('editCard').value = data.card;
                            document.getElementById('editDescription').value = data.description || '';
                            document.getElementById('editPoints').value = data.points;

                            // Populate member dropdown and set selected value
                            populateMemberDropdown().then(() => {
                                const memberSelect = document.getElementById('editMember');
                                memberSelect.value = data.member;
                            });

                            editModal.classList.remove('hidden');
                        })
                        .catch(error => {
                            console.error('Error fetching data:', error);
                            alert('Error loading minor case data');
                        });
                }

                function openDeleteModal(id) {
                    document.getElementById('deleteId').value = id;
                    deleteModal.classList.remove('hidden');
                }

                function fetchMinorCaseData(id) {
                    // Replace with actual AJAX call to your backend
                    return fetch(`/minor-cases/${id}`)
                        .then(response => response.json())
                        .catch(error => {
                            console.error('Error fetching data:', error);
                            return {}; // Return empty object on error
                        });
                }

                function updateMinorCase(id) {
                    const formData = {
                        sprint: document.getElementById('editSprint').value,
                        card: document.getElementById('editCard').value,
                        description: document.getElementById('editDescription').value,
                        member: document.getElementById('editMember').value,
                        points: document.getElementById('editPoints').value
                    };

                    // Replace with actual AJAX call
                    fetch(`/minor-cases/${id}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(formData)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Refresh the page or update the row
                            location.reload();
                        } else {
                            alert('Failed to update: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error updating:', error);
                        alert('An error occurred while updating');
                    })
                    .finally(() => {
                        editModal.classList.add('hidden');
                    });
                }

                function deleteMinorCase(id) {
                    // Replace with actual AJAX call
                    fetch(`/minor-cases/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Remove the row or refresh the page
                            location.reload();
                        } else {
                            alert('Failed to delete: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error deleting:', error);
                        alert('An error occurred while deleting');
                    })
                    .finally(() => {
                        deleteModal.classList.add('hidden');
                    });
                }

                function updateDisplayedDate() {
                    const selectedYear = document.getElementById('selectedYear').textContent;
                    const yearValue = parseInt(selectedYear);

                    document.querySelectorAll('.sprint-block').forEach(sprintBlock => {
                        const sprintDateElement = sprintBlock.querySelector('.sprint-date');
                        if (sprintDateElement) {
                            const dateText = sprintDateElement.textContent;
                            const yearMatch = dateText.match(/(\d{4})$/);
                            if (yearMatch) {
                                const sprintYear = parseInt(yearMatch[1]);
                                sprintBlock.style.display = (sprintYear === yearValue) ? 'block' : 'none';
                            }
                        }
                    });
                }

                function updateDisplayedSprints() {
                    const sprintRange = document.getElementById('selectedSprint').textContent;
                    const rangeMatch = sprintRange.match(/Sprint (\d+) ~ (\d+)/);

                    if (rangeMatch) {
                        const startSprint = parseInt(rangeMatch[1]);
                        const endSprint = parseInt(rangeMatch[2]);

                        document.querySelectorAll('.sprint-block').forEach(sprintBlock => {
                            const sprintNumber = parseInt(sprintBlock.dataset.sprintNumber);

                            if (sprintNumber >= startSprint && sprintNumber <= endSprint) {
                                sprintBlock.style.display = 'block';
                            } else {
                                sprintBlock.style.display = 'none';
                            }
                        });
                    }
                }

                function setupSprintEvents() {
                    document.querySelectorAll('.sprint-header').forEach(header => {
                        header.addEventListener('click', () => {
                            const content = header.nextElementSibling;
                            const icon = header.querySelector('.collapse-icon');

                            if (content) {
                                if (content.style.display === 'none' || content.style.display === '') {
                                    content.style.display = 'block';
                                    if (icon) icon.textContent = '▲';
                                } else {
                                    content.style.display = 'none';
                                    if (icon) icon.textContent = '▼';
                                }
                            }
                        });
                    });
                }

                function getSprintDateRange(sprintNumber) {
                    const sprintStartDate = new Date(SPRINT_YEAR, 0, 1);
                    const startDay = (sprintNumber - 1) * 7 + 1;
                    sprintStartDate.setDate(startDay);

                    const sprintEndDate = new Date(sprintStartDate);
                    sprintEndDate.setDate(sprintStartDate.getDate() + 4);

                    const startDateStr = sprintStartDate.getDate();
                    const options = { day: 'numeric', month: 'long' };
                    const endDateStr = sprintEndDate.toLocaleDateString('en-GB', options);

                    return `${startDateStr} - ${endDateStr} ${SPRINT_YEAR}`;
                }

                // Function to populate member dropdown
                async function populateMemberDropdown() {
                    try {
                        const response = await fetch('/api/members');
                        const members = await response.json();
                        const memberSelect = document.getElementById('editMember');

                        // Clear existing options except the first one
                        while (memberSelect.options.length > 1) {
                            memberSelect.remove(1);
                        }

                        // Add new options
                        members.forEach(member => {
                            const option = document.createElement('option');
                            option.value = member.name;
                            option.textContent = member.name;
                            memberSelect.appendChild(option);
                        });
                    } catch (error) {
                        console.error('Error fetching members:', error);
                    }
                }
            });
        </script>
    </div>
@endsection
