<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} - Register</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-xl shadow-lg">
            <div class="flex flex-col items-center">
                <div class="w-20 h-20 rounded-full bg-primary-500 flex items-center justify-center shadow-md">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                    </svg>
                </div>
                <h1 class="mt-2 text-center text-2xl font-bold text-primary-600">{{ config('app.name', 'DevPerf') }}</h1>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Create your account
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Or
                    <a href="{{ route('login') }}" class="font-medium text-primary-600 hover:text-primary-500">
                        sign in to your account
                    </a>
                </p>
            </div>
            <form class="mt-8 space-y-6" action="{{ route('register') }}" method="POST">
                @csrf
                <div class="rounded-md shadow-sm space-y-3">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full name</label>
                        <input id="name" name="name" type="text" required 
                            class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm @error('name') border-red-500 @enderror" 
                            placeholder="Enter your full name"
                            value="{{ old('name') }}">
                        @error('name')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email address</label>
                        <input id="email" name="email" type="email" required 
                            class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm @error('email') border-red-500 @enderror" 
                            placeholder="Enter your email"
                            value="{{ old('email') }}">
                        @error('email')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                        <select id="role" name="role" required
                            class="appearance-none relative block w-full px-3 py-2 border border-gray-300 text-gray-900 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm @error('role') border-red-500 @enderror">
                            <option value="" disabled selected>Select your role</option>
                            <option value="dev" {{ old('role') == 'dev' ? 'selected' : '' }}>Developer</option>
                            <option value="tester" {{ old('role') == 'tester' ? 'selected' : '' }}>Tester</option>
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrator</option>
                        </select>
                        @error('role')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input id="password" name="password" type="password" required 
                            class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm @error('password') border-red-500 @enderror" 
                            placeholder="Enter your password">
                        @error('password')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required 
                            class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm" 
                            placeholder="Confirm your password">
                    </div>
                </div>

                <div>
                    <button type="submit"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-primary-500 group-hover:text-primary-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                        </span>
                        Create account
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html> 