<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} - Login</title>
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
    <div class="flex items-center justify-center min-h-screen px-4 py-12 sm:px-6 lg:px-8">
        <div class="w-full max-w-md p-8 space-y-8">

            <!-- วัตถุที่ 1 -->
        <div class="absolute w-[500px] h-[350px] bg-blue-700 opacity-20 rounded-3xl blur-3xl"
        style="top: 15%; left: 25%; animation: moveCircle1 15s ease-in-out infinite alternate;"></div>


    <!-- วัตถุที่ 2 -->
    <div class="absolute w-[550px] h-[400px] bg-blue-400 opacity-20 rounded-3xl blur-3xl"
        style="top: 55%; left: 65%; animation: moveCircle2 18s linear infinite alternate-reverse;"></div>


    <!-- วัตถุที่ 3 -->
    <div class="absolute w-[450px] h-[320px] bg-blue-500 opacity-20 rounded-3xl blur-3xl"
        style="top: 35%; left: 40%; animation: moveCircle3 20s ease-in-out infinite alternate;"></div>


            <!-- แสดงข้อความแจ้งเตือนข้อผิดพลาด (Error Message) ในกรณีที่มีข้อผิดพลาดเกิดขึ้นกับฟิลด์ approval ในฟอร์ม -->
            @error('approval')
                <div class="p-4 rounded-md bg-yellow-50">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                {{ $message }}
                            </p>
                        </div>
                    </div>
                </div>
            @enderror

            <!-- Logo -->
            <div class="flex items-center justify-center mb-1">
                <img src="{{ asset('Group 553.png') }}" class="mr-0 rounded-lg w-96" />
            </div>

            <!-- Form Container -->
            <form class="space-y-4" action="{{ route('login') }}" method="POST">
                @csrf

                <!-- Input Email -->
                <div class="space-y-3 active:translate-y-0.5 active:shadow-inner transition-transform">
                    <label for="email" class="sr-only">Email</label>
                    <input type="email" name="email" type="email" required
                        class ="opacity-75 appearance-none relative block w-full px-3 py-2 border placeholder-gray-500 border-gray-300 text-gray-800 rounded-full focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm @error('email') border-red-500  @enderror"
                        placeholder="Email" value="{{ old('email') }}">
                    @error('email')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Input Password-->
                <div class="space-y-3 active:translate-y-0.5 active:shadow-inner transition-transform">
                    <label for="password" class="sr-only">Password</label>
                    <input id="password" name="password" type="password" required
                        class="opacity-75 appearance-none relative block w-full px-3 py-2 border placeholder-gray-500 border-gray-300 text-gray-800 rounded-full focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm @error('password') border-red-500 @enderror"
                        placeholder="Password">
                    @error('password')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Check Remember me-->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember_me" name="remember" type="checkbox"
                            class="w-4 h-4 border-gray-300 rounded text-primary-600 focus:ring-primary-500">
                        <label for="remember_me" class="block ml-2 text-sm text-gray-900">
                            Remember me
                        </label>
                    </div>
                </div>

                <!-- Botton Sign in-->
                <div class="active:translate-y-0.5 active:shadow-inner transition-transform">
                    <button type="submit"
                        class="relative flex justify-center w-full px-4 py-3 text-sm font-medium text-white bg-black border border-transparent rounded-full group focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 hover:bg-blue-500">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="w-5 h-5 text-gray-500 group-hover:text-white" xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                    clip-rule="evenodd" />
                            </svg>
                        </span>
                        Sign in
                    </button>
                </div>
            </form>


            <!-- Create An Account If don't have account-->
            <div class="flex flex-col items-center">

                <p class="text-sm text-center text-gray-600">
                    Or
                    <a href="{{ route('register') }}" class="font-medium text-primary-600 hover:text-primary-500">
                        create a new account
                    </a>
                </p>
            </div>

            <!-- Footer -->
        </div>
        <div class="absolute text-sm text-gray-500 -translate-x-1/2 bottom-5 left-1/2">
            © 2025 TTT Developer Performance
        </div>
    </div>
    </div>
</body>

</html>
