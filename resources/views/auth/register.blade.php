<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} - Register</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- movement ทิศทางการเคลื่อนที่ เเต่ละวัตถุ -->
    <style>
        @keyframes moveCircle1 {
            0% {
                transform: translate(0, 0) rotate(0deg) scale(1);
            }

            25% {
                transform: translate(10%, -10%) rotate(5deg) scale(1.1);
            }

            50% {
                transform: translate(-10%, 15%) rotate(-5deg) scale(1.2);
            }

            75% {
                transform: translate(5%, -5%) rotate(3deg) scale(1);
            }

            100% {
                transform: translate(0, 0) rotate(0deg) scale(1);
            }
        }

        @keyframes moveCircle2 {
            0% {
                transform: translate(0, 0) rotate(0deg) scale(1);
            }

            20% {
                transform: translate(-70%, 70%) rotate(-15deg) scale(1.1);
            }

            40% {
                transform: translate(90%, -90%) rotate(15deg) scale(1.3);
            }

            60% {
                transform: translate(-50%, -50%) rotate(-10deg) scale(1.2);
            }

            85% {
                transform: translate(30%, 60%) rotate(5deg) scale(1);
            }

            100% {
                transform: translate(0, 0) rotate(0deg) scale(1);
            }
        }

        @keyframes moveCircle3 {
            0% {
                transform: translate(0, 0) rotate(0deg) scale(1);
            }

            25% {
                transform: translate(80%, -50%) rotate(-20deg) scale(1.2);
            }

            50% {
                transform: translate(-100%, 80%) rotate(10deg) scale(1.1);
            }

            75% {
                transform: translate(60%, 30%) rotate(15deg) scale(1.3);
            }

            100% {
                transform: translate(0, 0) rotate(0deg) scale(1);
            }
        }
    </style>
</head>


<body class="flex items-center justify-center min-h-screen overflow-hidden bg-white">
    <div class="flex items-center justify-center min-h-screen px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-md p-8 space-y-8 ">

            <!-- วัตถุที่ 1 -->
            <div class="absolute w-[500px] h-[350px] bg-blue-700 opacity-20 rounded-3xl blur-3xl"
                style="top: 15%; left: 25%; animation: moveCircle1 15s ease-in-out infinite alternate;"></div>


            <!-- วัตถุที่ 2 -->
            <div class="absolute w-[550px] h-[400px] bg-blue-400 opacity-20 rounded-3xl blur-3xl"
                style="top: 55%; left: 65%; animation: moveCircle2 18s linear infinite alternate-reverse;"></div>


            <!-- วัตถุที่ 3 -->
            <div class="absolute w-[450px] h-[320px] bg-blue-500 opacity-20 rounded-3xl blur-3xl"
                style="top: 35%; left: 40%; animation: moveCircle3 20s ease-in-out infinite alternate;"></div>


            <div></div>
            <!-- Logo -->
            <div class="flex items-start mt-10 mb-4 ">
                <img src="{{ asset('Group 553.png') }}" class="mr-0 rounded-lg w-96" />
            </div>


            <div class="mb-10 font-sans text-2xl font-bold text-center black text te">
                <h1>Create an account</h1>
            </div>



            <!-- Form Container -->
            <form class="space-y-4" action="{{ route('register') }}" method="POST">
                @csrf

                <!-- Input Full Name -->
                <div class="space-y-1  active:translate-y-0.5 active:shadow-inner transition-transform">
                    <label for="name" class="sr-only">Full name</label>
                    <input id="name" name="name" type="text" required
                        class="opacity-75 appearance-none relative block w-full px-3 py-2 border placeholder-gray-500 border-gray-300 text-gray-800 rounded-full focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm @error('name') border-red-500 @enderror"
                        placeholder="Name" value="{{ old('name') }}">
                    @error('name')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 gap-3">
                    <!-- Input Email -->
                    <div class="space-y-0 active:translate-y-0.5 active:shadow-inner transition-transform">
                        <label for="email" class="sr-only">Email address</label>
                        <input id="email" name="email" type="email" required
                            class="opacity-75 appearance-none relative block w-full px-3 py-2 border placeholder-gray-500 border-gray-300 text-gray-800 rounded-full focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm @error('email') border-red-500 @enderror"
                            placeholder="Email" value="{{ old('email') }}">
                        @error('email')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Select Role -->
                    <div class="space-y-0 active:translate-y-0.5 active:shadow-inner transition-transform">
                        <label for="role" class="sr-only">Role</label>
                        <select id="role" name="role" required
                            class="opacity-75 appearance-none relative block w-full px-3 py-2 border placeholder-gray-500 border-gray-300 text-gray-800 rounded-full focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm @error('role') border-red-500 @enderror">
                            <option value="" class="text-gray-300" disabled selected>Select your role</option>
                            <option value="dev" {{ old('role') == 'dev' ? 'selected' : '' }}>Developer</option>
                            <option value="tester" {{ old('role') == 'tester' ? 'selected' : '' }}>Tester</option>
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrator</option>
                        </select>
                        @error('role')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>


                <div class="grid grid-cols-2 gap-3">
                    <!-- Input Password -->
                    <div class="space-y-0 active:translate-y-0.5 active:shadow-inner transition-transform">
                        <label for="password" class="sr-only">Password</label>
                        <input id="password" name="password" type="password" required
                            class="opacity-75 appearance-none relative block w-full px-3 py-2 border placeholder-gray-500 border-gray-300 text-gray-800 rounded-full focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm @error('password') border-red-500 @enderror"
                            placeholder="Password">
                        @error('password')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Confirm Email -->
                    <div class="space-y-0 active:translate-y-0.5 active:shadow-inner transition-transform">
                        <label for="password_confirmation" class="sr-only">Confirm password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required
                            class="relative block w-full px-3 py-2 text-gray-800 placeholder-gray-500 border border-gray-300 rounded-full opacity-75 appearance-none focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm"
                            placeholder="Confirm your password">
                    </div>
                </div>



                <!-- Botton Create Account -->
                <div class="active:translate-y-0.5 active:shadow-inner transition-transform">
                    <button type="submit"
                        class="relative flex justify-center w-full px-4 py-3 text-sm font-medium text-white bg-black border border-transparent rounded-full group focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 hover:bg-blue-500">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="w-5 h-5 text-primary-500 group-hover:text-white"
                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                                    clip-rule="evenodd" />
                            </svg>
                        </span>
                        Create account
                    </button>
                </div>
                <div class="flex flex-col items-center mb-14">
                    <p class="mt-2 text-sm text-center text-gray-600">
                        Or
                        <a href="{{ route('login') }}" class="font-medium text-primary-600 hover:text-primary-500">
                            sign in to your account
                        </a>
                    </p>
                </div>

            </form>
            <div class="absolute text-sm text-gray-500 -translate-x-1/2 bottom-5 left-1/2">
                © 2025 TTT Developer Performance
            </div>
        </div>
    </div>
</body>

</html>
