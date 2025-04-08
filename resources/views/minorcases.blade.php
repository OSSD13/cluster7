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
                // Fixed year for sprint date ranges
                const SPRINT_YEAR = 2025;

                // Function to get date range for a sprint
                function getSprintDateRange(sprintNumber) {
                    const sprintStartDate = new Date(SPRINT_YEAR, 0, 1); // Jan 1 of fixed year

                    // Calculate the start date (each sprint is 1 week, starting from Jan 1)
                    const startDay = (sprintNumber - 1) * 7 + 1;
                    sprintStartDate.setDate(startDay);

                    // Calculate the end date (5 days later for a work week)
                    const sprintEndDate = new Date(sprintStartDate);
                    sprintEndDate.setDate(sprintStartDate.getDate() + 4);

                    // Format dates
                    const options = {
                        day: 'numeric',
                        month: 'long',
                    };
                    const startDateStr = sprintStartDate.getDate();
                    const endDateStr = sprintEndDate.toLocaleDateString('en-GB', options);

                    return `${startDateStr} - ${endDateStr} ${SPRINT_YEAR}`;
                }

                // Function to toggle dropdown menu visibility
                function setupDropdown(buttonId, menuId, selectedId) {
                    document.getElementById(buttonId).addEventListener("click", function() {
                        document.getElementById(menuId).classList.toggle("hidden");
                    });

                    document.querySelectorAll(`#${menuId} a`).forEach(item => {
                        item.addEventListener("click", function() {
                            document.getElementById(selectedId).textContent = this.textContent;
                            document.getElementById(menuId).classList.add("hidden");

                            // Only update displayed sprints when sprint range changes
                            if (menuId === "sprintDropdownMenu") {
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

                // Function to load report data
                function loadReportData() {
                    // AJAX request to get data from report.blade.php
                    fetch('{{ route("minor-cases.data") }}')
                        .then(response => response.json())
                        .then(data => {
                            // Process the report data
                            populateSprintsFromReport(data);
                        })
                        .catch(error => {
                            console.error('Error loading report data:', error);
                            // Still load some demo data even if report data fails
                            loadDemoData();
                        });
                }

                // Populate sprints with data from report
                function populateSprintsFromReport(reportData) {
                    const sprintContainer = document.getElementById("sprintContainer");
                    sprintContainer.innerHTML = '';

                    // Group data by sprint
                    const sprintGroups = {};

                    reportData.forEach(item => {
                        if (!sprintGroups[item.sprint_number]) {
                            sprintGroups[item.sprint_number] = [];
                        }
                        sprintGroups[item.sprint_number].push(item);
                    });

                    // Sort sprint numbers in descending order (highest first)
                    const sortedSprintNumbers = Object.keys(sprintGroups).sort((a, b) => b - a);

                    // Create sprint sections for each group in descending order
                    sortedSprintNumbers.forEach(sprintNumber => {
                        const sprintItems = sprintGroups[sprintNumber];
                        const sprintDiv = createSprintDiv(sprintNumber, sprintItems);
                        sprintContainer.appendChild(sprintDiv);
                    });

                    // Set up event listeners for the newly created elements
                    setupSprintEventListeners();

                    // Set report-linked sprints to always be visible
                    document.querySelectorAll('.sprint-from-report').forEach(sprintContent => {
                        sprintContent.classList.add('sprint-with-data');
                    });

                    // Update displayed sprints based on filters
                    updateDisplayedSprints();
                }

                // Create a sprint div with data
                function createSprintDiv(sprintNumber, sprintItems) {
                    const sprintDiv = document.createElement("div");
                    sprintDiv.className = "bg-blue-100 rounded-lg p-3 mb-3 sprint-block";
                    sprintDiv.dataset.sprintNumber = sprintNumber;

                    // Determine if this sprint has data
                    const hasData = sprintItems && sprintItems.length > 0;
                    const dataClass = hasData ? "sprint-from-report" : "";

                    // Get the date range for this sprint (always using fixed year)
                    const dateRange = getSprintDateRange(parseInt(sprintNumber));

                    sprintDiv.innerHTML = `
            <div class="sprint-header flex justify-between items-center cursor-pointer text-blue-700 font-bold text-lg" >
                <span>Sprint #${sprintNumber} <span class="sprint-date text-sm text-center font-normal">${dateRange}</span></span>
                <span class="collapse-icon">▲</span>
            </div>
            <div class="sprint-content mt-2 bg-white p-3 rounded-lg shadow-md style="display: block;" ${dataClass}">
                <table class="w-full border-collapse border border-gray-300">
                    <thead>
                        <tr class="bg-blue-200">
                            <th class="border border-gray-300 px-4 py-2">Number</th>
                            <th class="border border-gray-300 px-4 py-2">Card Detail</th>
                            <th class="border border-gray-300 px-4 py-2">Description</th>
                            <th class="border border-gray-300 px-4 py-2">Member</th>
                            <th class="border border-gray-300 px-4 py-2">Point</th>
                            <th class="border border-gray-300 px-4 py-2 w-54">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="card-list">
                        ${hasData ? renderCardRows(sprintItems) : '<tr class="text-gray-500 italic no-data"><td colspan="6" class="text-center py-2">No data available</td></tr>'}
                    </tbody>
                </table>
            </div>
        `;

                    return sprintDiv;
                }

                // Render card rows from sprint items
                function renderCardRows(items) {
                    return items.map(item => `
            <tr>
                <td class="border border-gray-300 px-4 py-2 text-center">#${item.number || ''}</td>
                <td class="border border-gray-300 px-4 py-2 text-center">${item.card_detail || ''}</td>
                <td class="border border-gray-300 px-4 py-2 text-center" style="width: 40%;">${item.description || ''}</td>
                <td class="border border-gray-300 px-4 py-2 text-center"><span class="bg-green-50 text-green-600 text-xs px-2 py-1 rounded mr-2">${item.teamName || ''}</span>${item.member || ''}</td>
                <td class="border border-gray-300 px-4 py-2 text-center">${item.point || ''}</td>
                <td class="border border-gray-300 px-4 py-2 text-center">
                    <button class="edit-btn bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded mr-1">
                        <svg class="h-5 w-5 text-stone-800" width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                        </svg>
                    </button>
                    <button class="delete-btn bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded">
                        <svg class="h-5 w-5 text-red-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </td>
            </tr>
        `).join('');
                }

                // Set up event listeners for sprint elements
                function setupSprintEventListeners() {
                    // Toggle collapse/expand
                    document.querySelectorAll('.sprint-header').forEach(header => {
                        header.addEventListener('click', () => {
                            const content = header.nextElementSibling;
                            const icon = header.querySelector('.collapse-icon');

                            // Don't hide if it has the class for having data from report
                            if (content.classList.contains('sprint-from-report')) {
                                // Only allow toggling the icon
                                icon.textContent = icon.textContent === '▲' ? '▼' : '▲';
                                return;
                            }

                            if (content.style.display === 'none' || content.style.display === '') {
                                content.style.display = 'block';
                                icon.textContent = '▲';
                            } else {
                                content.style.display = 'none';
                                icon.textContent = '▼';
                            }
                        });
                    });

                    // Setup edit buttons
                    document.querySelectorAll(".edit-btn").forEach(button => {
                        button.addEventListener("click", function() {
                            let row = this.closest("tr");
                            let cells = row.querySelectorAll("td:not(:last-child)");

                            // Store original SVG content
                            const originalSvgContent = `<svg class="h-5 w-5 text-stone-800" width="24" height="24" viewBox="0 0 24 24"
         xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor"
         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
    </svg>`;

                            // Check if we're in edit mode by looking for inputs
                            const isEditing = row.querySelector("input") !== null;

                            if (!isEditing) {
                                // Switch to edit mode
                                cells.forEach(cell => {
                                    let value = cell.textContent.trim();
                                    // For the first cell, remove the # symbol if present
                                    if (cell === cells[0] && value.startsWith('#')) {
                                        value = value.substring(1);
                                    }
                                    cell.innerHTML =
                                        `<input type="text" value="${value}" class="border p-1 w-full">`;
                                });
                                this.innerHTML = "Save";
                            } else {
                                // Save edited values
                                cells.forEach((cell, index) => {
                                    let input = cell.querySelector("input");
                                    if (input) {
                                        // For the first cell, add the # symbol back
                                        if (index === 0) {
                                            cell.textContent = `#${input.value}`;
                                        } else {
                                            if (index === 3) {
                                                const [team, ...memberParts] = input.value
                                                    .split(' ');
                                                const member = memberParts.join(' ');
                                                cell.innerHTML =
                                                    `<span class="bg-green-50 text-green-600 text-xs px-2 py-1 rounded mr-2">${team}</span>${member}`;
                                            } else {
                                                cell.textContent = input.value;
                                            }
                                        }
                                    }
                                });
                                // Restore original SVG icon
                                this.innerHTML = originalSvgContent;
                            }
                        });
                    });

                    // Setup delete buttons
                    document.querySelectorAll(".delete-btn").forEach(button => {
                        button.addEventListener("click", function() {
                            let row = this.closest("tr");
                            if (confirm("Are you sure you want to delete this row?")) {
                                row.remove();

                                // Check if this was the last row and update "no data" message if needed
                                const tbody = this.closest("tbody");
                                if (tbody.querySelectorAll("tr").length === 0) {
                                    tbody.innerHTML =
                                        '<tr class="text-gray-500 italic no-data"><td colspan="6" class="text-center py-2">No data available</td></tr>';
                                }
                            }
                        });
                    });

                    // Add card logic for forms
                    document.querySelectorAll('.sprint-content form').forEach(form => {
                        form.addEventListener('submit', (e) => {
                            e.preventDefault();

                            const inputs = form.querySelectorAll('input');
                            const values = Array.from(inputs).map(input => input.value.trim());

                            if (values.some(val => !val)) return;

                            const tbody = form.nextElementSibling.querySelector('.card-list');

                            // Remove "No data" row
                            const noData = tbody.querySelector('.no-data');
                            if (noData) noData.remove();

                            const row = document.createElement('tr');

                            // Add data cells
                            values.forEach((val, index) => {
                                const td = document.createElement('td');
                                td.className = "border border-gray-300 px-4 py-2 text-center";
                                td.textContent = index === 0 ? `#${val}` : val;
                                row.appendChild(td);
                            });

                            // Add action buttons
                            const actionCell = document.createElement('td');
                            actionCell.className = "border border-gray-300 px-4 py-2 text-center";
                            actionCell.innerHTML = `
                    <button class="edit-btn bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded mr-1">
                        <svg class="h-5 w-5 text-stone-800" width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                        </svg>
                    </button>
                    <button class="delete-btn bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded">
                        <svg class="h-5 w-5 text-red-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                `;
                            row.appendChild(actionCell);

                            tbody.appendChild(row);

                            // Set up event listeners for the new buttons
                            const editBtn = actionCell.querySelector('.edit-btn');
                            const deleteBtn = actionCell.querySelector('.delete-btn');

                            // Store original SVG content
                            const originalSvgContent = editBtn.innerHTML;

                            editBtn.addEventListener('click', function() {
                                let cells = row.querySelectorAll("td:not(:last-child)");

                                // Check if we're in edit mode by looking for inputs
                                const isEditing = row.querySelector("input") !== null;

                                if (!isEditing) {
                                    // Switch to edit mode
                                    cells.forEach(cell => {
                                        let value = cell.textContent.trim();
                                        // For the first cell, remove the # symbol if present
                                        if (cell === cells[0] && value.startsWith(
                                                '#')) {
                                            value = value.substring(1);
                                        }
                                        cell.innerHTML =
                                            `<input type="text" value="${value}" class="border p-1 w-full">`;
                                    });
                                    this.innerHTML = "Save";
                                } else {
                                    // Save edited values
                                    cells.forEach((cell, index) => {
                                        let input = cell.querySelector("input");
                                        if (input) {
                                            // For the first cell, add the # symbol back
                                            if (index === 0) {
                                                cell.textContent = `#${input.value}`;
                                            } else {
                                                if (index === 3) {
                                                    const [team, ...memberParts] = input
                                                        .value.split(' ');
                                                    const member = memberParts.join(
                                                        ' ');
                                                    cell.innerHTML =
                                                        `<span class="bg-green-50 text-green-600 text-xs px-2 py-1 rounded mr-2">${team}</span>${member}`;
                                                } else {
                                                    cell.textContent = input.value;
                                                }
                                            }
                                        }
                                    });
                                    // Restore original SVG icon
                                    this.innerHTML = originalSvgContent;
                                }
                            });

                            form.reset();
                        });
                    });
                }

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

                // Function to load demo data if report data is not available
                function loadDemoData() {
                    const demoData = [{
                            sprint_number: 13,
                            data: [{
                                    number: '1',
                                    card_detail: 'Update UI',
                                    description: 'Update login page',
                                    member: 'John',
                                    teamName: 'Alhpa',
                                    point: 3
                                },
                                {
                                    number: '2',
                                    card_detail: 'Fix bug',
                                    description: 'This page displays all backlog bugs from previous sprints',
                                    member: 'Sarah',
                                    teamName: 'Alhpa',
                                    point: 2
                                }
                            ]
                        },
                        {
                            sprint_number: 1,
                            data: [{
                                number: '1',
                                card_detail: 'New feature',
                                description: 'Add dashboard',
                                member: 'Mike',
                                teamName: 'Alhpa',
                                point: 5
                            }]
                        },
                        {
                            sprint_number: 3,
                            data: [{
                                number: '1',
                                card_detail: 'New feature',
                                description: 'Add dashboard',
                                member: 'Mike',
                                teamName: 'Alhpa',
                                point: 5
                            }]
                        },
                        {
                            sprint_number: 14,
                            data: [{
                                number: '1',
                                card_detail: 'New feature',
                                description: 'Add dashboard',
                                member: 'Mike',
                                teamName: 'Alhpa',
                                point: 5
                            }]
                        }
                    ];

                    const formattedData = [];
                    demoData.forEach(sprint => {
                        sprint.data.forEach(item => {
                            formattedData.push({
                                sprint_number: sprint.sprint_number,
                                number: item.number,
                                card_detail: item.card_detail,
                                description: item.description,
                                teamName: item.teamName,
                                member: item.member,
                                point: item.point
                            });
                        });
                    });

                    populateSprintsFromReport(formattedData);
                }

                // Initialize by loading report data
                loadReportData();
            });
        </script>
    </div>
@endsection
