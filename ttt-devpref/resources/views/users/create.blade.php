@extends('layouts.app')

@section('title', 'Create User')
@section('page-title', 'Create User')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-900">Create New User</h2>
        <a href="{{ route('users.index') }}" class="text-primary-600 hover:text-primary-800 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Users
        </a>
    </div>
    
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                
                <div class="mb-4">
                    <label for="name" class="block text-gray-700 text-sm font-medium mb-2">Full Name</label>
                    <input type="text" name="name" id="name" class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm @error('name') border-red-500 @enderror" value="{{ old('name') }}" required>
                    @error('name')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 text-sm font-medium mb-2">Email Address</label>
                    <input type="email" name="email" id="email" class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm @error('email') border-red-500 @enderror" value="{{ old('email') }}" required>
                    @error('email')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="mb-4">
                    <label for="password" class="block text-gray-700 text-sm font-medium mb-2">Password</label>
                    <input type="password" name="password" id="password" class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm @error('password') border-red-500 @enderror" required>
                    @error('password')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="mb-6">
                    <label for="password_confirmation" class="block text-gray-700 text-sm font-medium mb-2">Confirm Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm" required>
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        Create User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 