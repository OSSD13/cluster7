<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Email</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex justify-center items-center h-screen">
    <div class="bg-white rounded-xl shadow-xl p-6 w-96 text-center">
        <div class="flex justify-center mb-4">
            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="bi bi-question-circle text-blue-500 text-2xl  flex items-center"></i>
            </div>
        </div>
        <p class="text-lg font-semibold">Are you confirm to send?</p>
        <div class="flex justify-center gap-4 mt-6">
             <a href="{{ route('complete') }}" class="px-5 py-1 rounded-full text-blue-500 bg-blue-200 hover:bg-blue-500 hover:text-white">Yes</a>
           
            <button class="px-5 py-1 rounded-full text-red-500 bg-red-200 hover:bg-red-400 hover:text-white">No</button>
        </div>
    </div>
</body>
</html>