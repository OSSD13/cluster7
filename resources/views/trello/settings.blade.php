@extends('layouts.app')

@section('title', 'Trello API Settings')
@section('page-title', 'Trello API Settings')

@section('content')
<div class="max-w-4xl mx-left">
    <div class="mb-6 flex justify-between items-center">
        <div class="flex items-center space-x-3">
            <h2 class="text-2xl font-semibold text-gray-900">Trello API Settings</h2>
            @if($connectionStatus && $connectionStatus['success'])
                <div class="flex items-center bg-green-50 rounded-full p-1">
                    <div class="h-3 w-3 rounded-full bg-green-500"></div>
                    <div class="text-black-600 ml-2 font-normal text-sm inline-flex items-center py-1">
                        Connected to
                        <span class="ml-2 inline-block px-2 py-1 rounded-full bg-green-400 text-black">
                            {{ $connectionStatus['fullName'] }}
                        </span>
                    </div>
                </div>
            @endif
        </div>
    
        <a href="{{ route('dashboard') }}" class="inline-flex items-center px-3 py-2 border border-gray-300 
        shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-skye-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
            <span class="order-2 ml-1">Back to Dashboard</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 order-1 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
    </div>
  
        <!-- Connection Status information -->
        @if($connectionStatus)
            <div x-data="{ show: true }"
                 x-show="show"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="opacity-100 transform translate-y-0"
                 x-transition:leave-end="opacity-0 transform -translate-y-2"
                 class="p-4 {{ $connectionStatus['success'] ? '' : 'bg-red-50' }}">
                
                <div class="flex items-center">
                    @if($connectionStatus['success'])

                            @if(!empty($connectionStatus['boards']))
                            @endif
                            </p>
                        </div>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <p class="text-red-700 font-medium">{{ $connectionStatus['message'] }}</p>
                            @if(!empty($connectionStatus['details']))
                                <div class="mt-2 text-sm text-red-600">
                                    <details>
                                        <summary>Show error details</summary>
                                        <pre class="mt-2 bg-red-50 p-2 rounded text-xs overflow-auto max-h-40">{{ is_array($connectionStatus['details']) ? json_encode($connectionStatus['details'], JSON_PRETTY_PRINT) : $connectionStatus['details'] }}</pre>
                                    </details>
                                </div>
                            @endif
                            <p class="text-sm text-red-600 mt-1">Please check your API key and token, or try generating a new token from
                                 <a href="https://trello.com/app-key" target="_blank" class="underline">Trello Developer API Keys</a>.</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <div class="p-6 bg-white border-b border-gray-200">
            <form method="POST" action="{{ route('trello.settings.update') }}" id="settings-form">
                @csrf

                <div class="mb-6">
                    <label for="trello_api_key" class="block text-gray-700 text-sm font-bold mb-2">Trello API Key</label>
                    <input
                        type="text"
                        name="trello_api_key"
                        id="trello_api_key"
                        value="{{ old('trello_api_key', $trelloApiKey) }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('trello_api_key') border-red-500 @enderror"
                        required
                    >
                    @error('trello_api_key')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-gray-500 text-xs mt-1">You can get your API Key from <a href="https://trello.com/app-key" target="_blank" class="text-primary-600 hover:underline">Trello Developer API Keys</a>.</p>
                </div>

                <div class="mb-6">
                    <label for="trello_api_token" class="block text-gray-700 text-sm font-bold mb-2">Trello API Token</label>
                    <input
                        type="password"
                        name="trello_api_token"
                        id="trello_api_token"
                        value="{{ old('trello_api_token', $trelloApiToken) }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('trello_api_token') border-red-500 @enderror"
                        required
                    >
                    @error('trello_api_token')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-gray-500 text-xs mt-1">Click "Token" on the <a href="https://trello.com/app-key" target="_blank" class="text-primary-600 hover:underline">Trello Developer API Keys</a> page to generate a token. You need a <strong>Server Token</strong> with read/write permissions.</p>
                </div>

                <div class="flex items-center justify-end space-x-4">
                    <button
                        type="button"
                        id="test-connection-btn"
                        class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg  focus:outline-none focus:shadow-outline"
                    >
                        Test Connection
                    </button>
                    <button
                        type="submit"
                        class="bg-primary-600 hover:bg-primary-700 text-white font-bold py-2 px-4 rounded-lg focus:outline-none focus:shadow-outline"
                    >
                        Save Settings
                    </button>
                </div>
            </form>
        </div>
        <div class="p-6 bg-white border-b border-gray-200">
        <div class="text-lg font-medium text-gray-900 mb-2.5 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-15 w-7 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16" />
                <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z" />
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2.5 ml-2">How to get your Trello API credentials</h3>
        </div>
            <ol class="list-decimal list-inside space-y-2 text-gray-700">
                <li>Go to <a href="https://trello.com/app-key" target="_blank" class="text-primary-600 hover:underline">Trello Developer API Keys</a></li>
                <li>Log in to your Trello account if prompted</li>
                <li>Copy the <strong>API Key</strong> (shown on that page) to the API Key field above</li>
                <li>Click "<strong>Token</strong>" from that page and authorize the application</li>
                <li>Copy the generated <strong>Token</strong> (not "Secret") to the API Token field above</li>
                <li>Click "Save Settings" to store your credentials</li>
            </ol>
            <p class="mt-4 text-sm text-gray-500">Note: These credentials are stored securely and are only used to interact with Trello on your behalf.</p>

            <div class="mt-6 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">Troubleshooting "Invalid Key" Errors</h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <ul class="list-disc pl-5 space-y-1">
                                <li>Make sure you're using the <strong>API Key</strong>, not the secret</li>
                                <li>For the second field, use the <strong>Token</strong> generated when you click "Token" and authorize the app</li>
                                <li>Ensure you've copied the full key and token without any extra spaces</li>
                                <li>Try generating a new token if the current one isn't working</li>
                                <li>Check if your Trello account has administrative privileges if you're using a team account</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        
            <!-- Additional Settings Section -->
            <div class="p-6 bg-white border-b border-gray-200">
            <div class="mt-6 ">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Settings</h3>
                
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 mt-0.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-base font-medium text-gray-900">Sprint Settings</h4>
                            <p class="text-sm text-gray-500 mt-1">
                                Configure sprint durations, start days, and automatic report generation. Sprint reports are automatically saved at the end of each sprint period.
                            </p>
                            <a href="{{ route('settings.sprint') }}" class="mt-2 inline-flex items-center px-3 py-1.5 border border-primary-300 shadow-sm text-sm leading-4 font-medium rounded-md text-primary-700 bg-white hover:bg-primary-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                Manage Sprint Settings
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>
<!-- Connection Test Result Modal -->
<div id="connection-modal" class="fixed inset-0 flex items-center justify-center z-50 hidden">
    <div class="modal-overlay fixed inset-0 bg-black opacity-50"></div>

    <div class="modal-container bg-white w-full max-w-md mx-auto rounded-lg shadow-lg z-50 overflow-y-auto">
        <div class="modal-content py-4 text-left px-6">
            <!-- Modal Header -->
            <div class="flex justify-between items-center pb-3 border-b">
                <h3 class="text-xl font-semibold text-gray-900" id="modal-title">Testing Connection...</h3>
                <button id="close-modal-button" class="modal-close cursor-pointer z-50">
                    <svg class="fill-current text-gray-500 hover:text-gray-800" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18">
                        <path d="M14.53 4.53l-1.06-1.06L9 7.94 4.53 3.47 3.47 4.53 7.94 9l-4.47 4.47 1.06 1.06L9 10.06l4.47 4.47 1.06-1.06L10.06 9z"></path>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="my-4">
                <div id="loading-indicator" class="flex flex-col items-center py-4">
                    <svg class="animate-spin h-8 w-8 text-primary-500 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="text-sm text-gray-600">Testing connection to Trello API...</p>
                </div>

                <div id="success-result" class="flex flex-col items-center py-4 hidden">
                    <div class="mb-2">
                        <div class="h-4 w-4 rounded-full bg-green-500"></div>
                    </div>
                    <p class="text-sm text-gray-600 text-center" id="success-message">Connection successful!</p>
                    <p class="text-sm font-medium text-gray-800 mt-2" id="user-info"></p>

                    <div id="boards-container" class="mt-4 w-full max-w-md hidden">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Your Trello Boards:</h4>
                        <ul id="boards-list" class="text-sm text-gray-600 list-disc ml-5"></ul>
                    </div>
                </div>

                <div id="error-result" class="flex flex-col items-center py-4 hidden">
                    <svg class="h-12 w-12 text-red-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-sm text-red-600 text-center" id="error-message">Unable to connect to Trello API.</p>
                    <button id="show-debug-btn" class="mt-2 text-xs text-blue-600 hover:underline">Show Debug Information</button>
                    <div id="debug-info" class="mt-4 bg-gray-100 p-3 rounded text-xs w-full hidden">
                        <h4 class="font-semibold text-gray-800 mb-2">Debug Information</h4>
                        <pre id="debug-content" class="whitespace-pre-wrap break-all text-gray-600"></pre>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="border-t border-gray-200 pt-3 flex justify-end">
                <button id="modal-close-btn" class="px-4 py-2 bg-gray-200 text-gray-800 text-sm font-medium rounded-md">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const testBtn = document.getElementById('test-connection-btn');
    const modal = document.getElementById('connection-modal');
    const closeBtn = document.getElementById('close-modal-button');
    const modalCloseBtn = document.getElementById('modal-close-btn');
    const apiKeyInput = document.getElementById('trello_api_key');
    const apiTokenInput = document.getElementById('trello_api_token');

    const loadingIndicator = document.getElementById('loading-indicator');
    const successResult = document.getElementById('success-result');
    const errorResult = document.getElementById('error-result');
    const modalTitle = document.getElementById('modal-title');
    const successMessage = document.getElementById('success-message');
    const errorMessage = document.getElementById('error-message');
    const userInfo = document.getElementById('user-info');
    const showDebugBtn = document.getElementById('show-debug-btn');
    const debugInfo = document.getElementById('debug-info');

    function openModal() {
        modal.classList.remove('hidden');
        // Reset modal state
        loadingIndicator.classList.remove('hidden');
        successResult.classList.add('hidden');
        errorResult.classList.add('hidden');
        debugInfo.classList.add('hidden');
        modalTitle.textContent = 'Testing Connection...';
    }

    function closeModal() {
        modal.classList.add('hidden');
    }

    testBtn.addEventListener('click', function() {
        const apiKey = apiKeyInput.value;
        const apiToken = apiTokenInput.value;

        if (!apiKey || !apiToken) {
            alert('Please enter both API Key and API Token');
            return;
        }

        openModal();

        // Send the API request to test the connection
        fetch('{{ url(route('trello.test-connection')) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                trello_api_key: apiKey,
                trello_api_token: apiToken
            })
        })
        .then(response => response.json())
        .then(data => {
            loadingIndicator.classList.add('hidden');

            if (data.success) {
                modalTitle.textContent = 'Connection Successful';
                successResult.classList.remove('hidden');
                successMessage.textContent = data.message;
                userInfo.textContent = `Connected as: ${data.fullName} (${data.username})`;

                // Display boards if available
                if (data.boards && data.boards.length > 0) {
                    const boardsContainer = document.getElementById('boards-container');
                    const boardsList = document.getElementById('boards-list');

                    boardsContainer.classList.remove('hidden');
                    boardsList.innerHTML = '';

                    data.boards.forEach(board => {
                        const li = document.createElement('li');
                        
                        li.innerHTML = `<a href="${board.url}" target="_blank" class="text-blue-600 hover:underline">${board.name}</a>`;
                        boardsList.appendChild(li);
                    });
                }
            } else {
                modalTitle.textContent = 'Connection Failed';
                errorResult.classList.remove('hidden');
                errorMessage.textContent = data.message;

                // Show debug info if available
                if (data.debug_info) {
                    const debugInfo = document.getElementById('debug-info');
                    const debugContent = document.getElementById('debug-content');
                    debugInfo.classList.remove('hidden');
                    debugContent.textContent = JSON.stringify(data.debug_info, null, 2);
                }
            }
        })
        .catch(error => {
            loadingIndicator.classList.add('hidden');
            errorResult.classList.remove('hidden');
            modalTitle.textContent = 'Connection Error';
            errorMessage.textContent = 'An error occurred while testing the connection.';
            console.error('Error:', error);
        });
    });

    closeBtn.addEventListener('click', closeModal);
    modalCloseBtn.addEventListener('click', closeModal);

    // Close when clicking outside the modal
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            closeModal();
        }
    });

    // Add event listener for debugging button
    showDebugBtn.addEventListener('click', function() {
        debugInfo.classList.toggle('hidden');
    });
});
</script>
@endsection
