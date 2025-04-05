<!-- resources/views/emails/confirmation.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Confirmation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex justify-center items-center h-screen m-0 font-sans">
    <div class="bg-white rounded-lg shadow-md p-10 text-center w-full max-w-2xl mx-4">
        <!-- Circle with checkmark icon -->
        <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mb-4 mx-auto">
            <i class="bi bi-check-circle text-blue-500 text-6xl flex items-center"></i>
        </div>
        
        <h1 class="text-2xl font-bold text-gray-800 mb-4">Send email completed</h1>
        
        <p class="text-gray-600 mb-8">
            The report will be sent shortly,<br>
            please wait.
        </p>
        
        <button onclick="window.close()" class="bg-blue-400 hover:bg-blue-500 text-white font-medium py-3 px-10 rounded-full transition duration-300">
            Close
        </button>
    </div>
</body>
</html>