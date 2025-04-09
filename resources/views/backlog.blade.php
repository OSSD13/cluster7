@extends('layouts.app')

@section('title', 'Backlog')
@section('page-title', 'Backlog')

@section('content')

<div class="rounded-[2vw] h-full w-full bg-gray-100 p-5">
    <div class="grid grid-cols-11 gap-1 ps-5 pt-3">
        <div class="w-20 h-20 rounded-full bg-sky-100 flex justify-center items-center ">
            <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" fill="#13A7FD" class="bi bi-bug"
                viewBox="0 0 16 16">
                <path
                    d="M4.355.522a.5.5 0 0 1 .623.333l.291.956A5 5 0 0 1 8 1c1.007 0 1.946.298 2.731.811l.29-.956a.5.5 0 1 1 .957.29l-.41 1.352A5 5 0 0 1 13 6h.5a.5.5 0 0 0 .5-.5V5a.5.5 0 0 1 1 0v.5A1.5 1.5 0 0 1 13.5 7H13v1h1.5a.5.5 0 0 1 0 1H13v1h.5a1.5 1.5 0 0 1 1.5 1.5v.5a.5.5 0 1 1-1 0v-.5a.5.5 0 0 0-.5-.5H13a5 5 0 0 1-10 0h-.5a.5.5 0 0 0-.5.5v.5a.5.5 0 1 1-1 0v-.5A1.5 1.5 0 0 1 2.5 10H3V9H1.5a.5.5 0 0 1 0-1H3V7h-.5A1.5 1.5 0 0 1 1 5.5V5a.5.5 0 0 1 1 0v.5a.5.5 0 0 0 .5.5H3c0-1.364.547-2.601 1.432-3.503l-.41-1.352a.5.5 0 0 1 .333-.623M4 7v4a4 4 0 0 0 3.5 3.97V7zm4.5 0v7.97A4 4 0 0 0 12 11V7zM12 6a4 4 0 0 0-1.334-2.982A3.98 3.98 0 0 0 8 2a3.98 3.98 0 0 0-2.667 1.018A4 4 0 0 0 4 6z" />
            </svg>
        </div>
        <p
            class="flex items-center font-style: italic font-weight: text-[#009eff] text-6xl font-bold inline-block align-middle pb-3">
            Backlog</p>


        <!-- Dropdown -->
        <div class=" col-start-8 col-span-4 grid grid-rows-2 gap-y-2">
            @if(auth()->user()->isAdmin())
            <form class="grid grid-cols-12  w-30">
                <label for="team" class="col-start-1 block col-span-2 text-sm font-medium text-gray-900 dark:text-black pt-2 ">Team :</label>
                <select
                    id="team" class=" col-start-3 col-span-9 bg-gray-50 border-gray-300 text-gray-900 text-sm rounded-3xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-white dark:border-gray-600 dark:placeholder-gray-400 dark:text-black dark:focus:ring-blue-500 dark:focus:border-blue-500">
                    <option selected class="p-center ">All</option>
                    <option value="A">Alpha</option>
                    <option value="B">beta</option>
                    <option value="D">delta</option>
                </select>
            </form>
            @endif
            <!-- Dropdown2 -->
            <form class="grid grid-cols-12  w-30">
                <label for="year" class="col-start-1 block col-span-2 text-sm font-medium text-gray-900 dark:text-black pt-2 ">Year :</label>
                <select
                    id="year" class=" col-start-3 col-span-3 bg-gray-50 border-gray-300 text-gray-900 text-sm rounded-3xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-white dark:border-gray-600 dark:placeholder-gray-400 dark:text-black dark:focus:ring-blue-500 dark:focus:border-blue-500">
                    <option selected class="p-center ">2025</option>
                    <option value="A">2024</option>
                    <option value="B">2023</option>
                    <option value="D">2022</option>
                </select>
                <label for="sprint" class="col-start-7 block col-span-2 text-sm font-medium text-gray-900 dark:text-black pt-2 ">Sprint :</label>
                <select
                    id="sprint" class=" col-start-9 col-span-3 bg-gray-50 border-gray-300 text-gray-900 text-sm rounded-3xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-white dark:border-gray-600 dark:placeholder-gray-400 dark:text-black dark:focus:ring-blue-500 dark:focus:border-blue-500">
                    <option selected class="p-center ">1 ~ 10</option>
                    <option value="A">11 ~ 20</option>
                    <option value="B">21 ~ 30</option>
                    <option value="D">31 ~ 40</option>
                </select>
            </form>
        </div>
    </div>
    <!-- -------- -->
    <!-- Backlog Task(ตัว Task Backlog) -->
    <div class="bg-white rounded-3xl p-5 w-96 mt-10">
        <div class="">
            <div class="flex items-center gap-3">
                <!-- point -->
                <div class="w-10 h-9 rounded-full bg-[#FFE2B9] flex justify-center items-center text-[#FFA954] font-bold">
                    10
                </div>
                <!-- Title Backlog -->
                <div class="text-lg bg-gray-100 rounded-full w-full h-8 p-2 flex justify-between items-center">
                    <div class="font-bold text-base">Google SSO</div>
                    <!--  -->
                    <span class="text-gray-400 bg-white rounded-full text-sm h-6 p-1 flex justify-between items-center">Sprint 1</span>
                </div>
            </div>
        </div>
        <div>
            <!-- Info in Backlog -->
            <div class="text-lg rounded-full w-full h-8 p-2 grid grid-cols-11 gap-1 mb-20 mt-3">
                <div class="flex justify-between items-center col-span-7">Add Google Login Feature hjgggggffffhhhhg</div>
                <button type="button" class="text-[#985E00] bg-[#FFC7B2] hover:bg-[#FFA954] focus:outline-none font-medium rounded-full px-2 py-2 text-center ms-3 h-8 w-8 col-start-9">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class=" bi bi-pencil-square" viewBox="0 0 16 16 ">
                        <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z" />
                        <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z" />
                    </svg>
                </button>

                <button type="button" class="text-[#FF0004] bg-[#FFACAE] hover:bg-[#FF7C7E] focus:outline-none font-medium rounded-full px-2 py-2 text-center  h-8 w-8  col-start-11">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3" viewBox="0 0 16 16">
                        <path d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5M11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1zm1.958 1-.846 10.58a1 1 0 0 1-.997.92h-6.23a1 1 0 0 1-.997-.92L3.042 3.5zm-7.487 1a.5.5 0 0 1 .528.47l.5 8.5a.5.5 0 0 1-.998.06L5 5.03a.5.5 0 0 1 .47-.53Zm5.058 0a.5.5 0 0 1 .47.53l-.5 8.5a.5.5 0 1 1-.998-.06l.5-8.5a.5.5 0 0 1 .528-.47M8 4.5a.5.5 0 0 1 .5.5v8.5a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5" />
                    </svg>
                </button>
            </div>
        </div>

        <div class="flex justify-between items-center">
            <div class="flex items-center gap-2">
                <div class="text-black">Assign to:</div>
                <div class="bg-[#BAF3FF] text-[#13A7FD] px-2 py-1 rounded-full font-bold">Alpha</div>
                <div>Pawarit</div>
            </div>

            <div class="flex items-center gap-2 ">
                <div class="text-black">Status:</div>
                <div class="bg-[#DDFFEC] text-[#82DF3C] px-2 py-1 rounded-full font-bold">Success</div>
            </div>
        </div>

    </div>

</div>


@endsection