<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        /* Custom styles to ensure checkboxes look correct */
        .checkbox-custom {
            border-width: 2px;
            transition: all 0.2s ease;
            outline: none;
        }
        .checkbox-custom.checked {
            background-color:rgb(147, 208, 255);
            border-color:rgb(147, 208, 255);
        }
        .checkbox-custom:not(.checked) {
            border-color: rgb(147, 197, 253);
            background-color: transparent;
        }
        
        /* Animation for error message */
        .error-message {
            animation: fadeIn 0.3s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>

<body class="bg-blue bg-opacity-50 flex justify-center items-center h-screen m-0">
    <div class="bg-blue rounded-xl shadow-xl w-full max-w-2xl p-4">
        
        <!-- Header -->
        <div class="pb-2">
            <div class="ml-5 text-gray-500 text-1xl mb-0">Email</div>
            <h1 class="ml-5 text-4xl font-bold leading-tight ">Alpha</h1>
        </div>
        <div class="flex gap-1 m-0.5 ml-5">
            <button class="px-3 py-1 rounded-full font-medium text-sm bg-orange-100 text-orange-300">Sprint 1</button>
            <button class="px-3 py-1 rounded-full font-medium text-sm bg-orange-100 text-orange-300">V.2.0</button>
        </div>
        
        <!-- Error Message Container -->
        <div id="errorContainer" class="mx-1 mt-1 hidden justify-end text-right">
            <div class="bg-red-100 text-red-500 rounded-full px-2 py-2 items-center error-message inline-flex text-xs">
        <!-- ไอคอนข้อมูล -->
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-exclamation-circle mr-3" viewBox="0 0 16 16">
            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
            <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z"/>
        </svg>
        <!-- ข้อความเตือน -->
        <span>  Please select at least one recipient.</span>
    </div>
</div>


        
        <!-- Content -->
        <div class="flex gap-4 mt-4 ml-5">
            
            <!-- Recipients List -->
            <div class="bg-gray-50 rounded-lg p-3 flex-1">
                <div class="px-6 py-1 rounded-full font-medium text-sm bg-sky-100 text-sky-500 mb-2 w-fit">Alpha</div>
                
                <div class="flex items-center p-2 border-b border-sky-100">
                    <div class="w-4 h-4 rounded-full checkbox-custom mr-2 flex items-center justify-center cursor-pointer" id="selectAll"></div>
                    <div class="font-medium text-sm">Select all</div>
                </div>
                
                <div class="flex items-center p-2 border-b border-sky-100">
                    <div class="w-4 h-4 rounded-full checkbox-custom checked mr-2 flex items-center justify-center cursor-pointer" id="check1" data-name="Pawarit" data-email="Pawan777@gmail.com">
                        <span class="text-white text-xs">✓</span>
                    </div>
                    <div class="font-medium text-sm mr-2">Pawarit</div>
                    <div class="text-sky-500 text-xs bg-sky-100 px-2 py-1 rounded-full">Pawan777@gmail.com</div>
                </div>
                
                <div class="flex items-center p-2 border-b border-sky-100">
                    <div class="w-4 h-4 rounded-full checkbox-custom mr-2 flex items-center justify-center cursor-pointer" id="check2" data-name="Narathip" data-email="Dekpadwat@gmail.com"></div>
                    <div class="font-medium text-sm mr-2">Narathip</div>
                    <div class="text-sky-500 text-xs bg-sky-100 px-2 py-1 rounded-full">Dekpadwat@gmail.com</div>
                </div>
                
                <div class="flex items-center p-2 border-b border-sky-100">
                    <div class="w-4 h-4 rounded-full checkbox-custom mr-2 flex items-center justify-center cursor-pointer" id="check3" data-name="Peerawat" data-email="dourtung@gmail.com"></div>
                    <div class="font-medium text-sm mr-2">Peerawat</div>
                    <div class="text-sky-500 text-xs bg-sky-100 px-2 py-1 rounded-full">dourtung@gmail.com</div>
                </div>
                
                <div class="flex items-center p-2">
                    <div class="w-4 h-4 rounded-full checkbox-custom mr-2 flex items-center justify-center cursor-pointer" id="check4" data-name="Aritach" data-email="Sahut@gmail.com"></div>
                    <div class="font-medium text-sm mr-2">Aritach</div>
                    <div class="text-sky-500 text-xs bg-sky-100 rounded-full px-2 py-1">Sahut@gmail.com</div>
                </div>
            </div>
            
            <!-- Selected Person -->
            <div class="bg-gray-50 rounded-lg p-3 w-2/5">
                <div class="font-bold text-sm mb-3">Selected Person</div>
                
                <div id="selectedPersonContainer">
                    <!-- Selected persons will be dynamically added here -->
                    <div class="flex justify-between mb-1">
                        <div class="font-medium text-sm">Pawarit</div>
                        <div class="text-sky-500 text-xs">Pawan777@gmail.com</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Buttons -->
        <div class="flex justify-end gap-2 mt-4">
            <button class="px-10 py-1 rounded-full font-medium text-sm border border-gray-300 bg-white">Cancel</button>
            <!-- ปุ่ม Send ใน sendEmail.blade.php -->
            <a id="sendButton" href="{{ route('confirm') }}" class="px-10 py-2 rounded-full font-medium text-sm bg-blue-500 text-white">Send</a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const checkboxes = document.querySelectorAll('[id^="check"]');
            const selectAll = document.getElementById('selectAll');
            const selectedPersonContainer = document.getElementById('selectedPersonContainer');
            const sendButton = document.getElementById('sendButton');
            const errorContainer = document.getElementById('errorContainer');

            // Function to update checkbox appearance
            function updateCheckbox(checkbox, isChecked) {
                if (isChecked) {
                    checkbox.classList.add('checked');
                    checkbox.innerHTML = '<span class="text-white text-xs">✓</span>';
                } else {
                    checkbox.classList.remove('checked');
                    checkbox.innerHTML = '';
                }
            }

            // Function to update selected persons display
            function updateSelectedPersons() {
                // Clear the current selected persons
                selectedPersonContainer.innerHTML = '';
                
                // Add all checked persons to the selected persons container
                let hasSelectedRecipients = false;
                checkboxes.forEach(cb => {
                    if (cb.classList.contains('checked')) {
                        hasSelectedRecipients = true;
                        const name = cb.getAttribute('data-name');
                        const email = cb.getAttribute('data-email');
                        
                        if (name && email) {
                            const personElement = document.createElement('div');
                            personElement.className = 'flex justify-between mb-1';
                            personElement.innerHTML = `
                                <div class="font-medium text-sm">${name}</div>
                                <div class="text-sky-500 text-xs">${email}</div>
                            `;
                            selectedPersonContainer.appendChild(personElement);
                        }
                    }
                });
                
                // If no one is selected, show empty message
                if (!hasSelectedRecipients) {
                    const emptyElement = document.createElement('div');
                    emptyElement.className = 'text-gray-400 text-sm';
                    emptyElement.textContent = 'No person selected';
                    selectedPersonContainer.appendChild(emptyElement);
                    
                    // Show error message
                    errorContainer.classList.remove('hidden');
                } else {
                    errorContainer.classList.add('hidden');
                }
                
                return hasSelectedRecipients;
            }

            // Initialize the selected persons on page load
            updateSelectedPersons();

            // Select All checkbox
            selectAll.addEventListener('click', function() {
                const isAllChecked = this.classList.contains('checked');
                updateCheckbox(this, !isAllChecked);
                
                checkboxes.forEach(cb => {
                    updateCheckbox(cb, !isAllChecked);
                });
                
                updateSelectedPersons();
            });

            // Individual checkboxes
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('click', function() {
                    const isChecked = this.classList.contains('checked');
                    updateCheckbox(this, !isChecked);
                    updateSelectedPersons();
                });
            });
            
            // Send button validation
            sendButton.addEventListener('click', function(event) {
                const hasRecipients = updateSelectedPersons();
                
                if (!hasRecipients) {
                    // Prevent default behavior (link navigation)
                    event.preventDefault();
                }
            });
        });
    </script>   
</body>
</html>
