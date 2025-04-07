@extends('layouts.app')

<!-- ระบุชื่อหน้าและหัวข้อ -->
@section('title', 'Trello Teams')
@section('page-title', 'Trello Teams')

@section('content')
<div class="rounded-[2vw] h-full w-full bg-gray-100 px-5 py-2">
<!-- คอนเทนเนอร์หลักพร้อมความกว้างสูงสุด -->
<div class="max-w-7xl mx-auto">
    <!-- ส่วนหัวพร้อมไอคอนและปุ่มต่างๆ -->
    <div class="mb-6 flex justify-between items-">
        <!-- หัวข้อหลักและไอคอนข้อมูล -->
        <h2 class="text-2xl font-bold text-gray-900">
           
          
                <!-- ไอคอนข้อมูลและป๊อปอัพ -->
              
                    <!-- ไอคอนที่คลิกได้เพื่อแสดงป๊อปอัพ -->
                    <div class="mb-4 pt-6 flex items-center">
                    <div class="ml-7 w-20 h-20 rounded-full bg-sky-100 flex justify-center items-center ">
                    <svg xmlns="http://www.w3.org/2000/svg" width = "33" height = "33" fill="none" viewBox="0 0 24 24" stroke="#13A7FD">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div>
                         <h2 class="ml-4 pt-1 text-[#13A7FD] text-5xl font-bold italic">Trello Teams</h2>
                         
                    </div>
                    <!-- ไอคอนข้อมูลและป๊อปอัพ -->
                     <div class="flex items-center" x-data="{ showInfo: false }">
                <div class="relative">
                    <!-- ไอคอนที่คลิกได้เพื่อแสดงป๊อปอัพ -->
                    <svg @click="showInfo = !showInfo"
                        xmlns="http://www.w3.org/2000/svg"
                        width="18"
                        height="18"
                        fill="#0096D6"
                        class="bi bi-exclamation-circle ml-2 mt-1 cursor-pointer hover:opacity-100"
                        viewBox="0 0 16 16">
                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16" />
                        <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z" />
                    </svg>
                   
                </div>
                       
                 
                    <!-- เนื้อหาป๊อปอัพ -->

                    <div class="relative inline-block">
    <!-- ไอคอนหรือปุ่ม -->
    <button @click="showInfo = !showInfo">
        <i class="fas fa-info-circle text-blue-500 text-xl"></i>
    </button>

    <!-- ป๊อปอัพ -->
    <div class="relative inline-block">
    <!-- ไอคอนหรือปุ่ม -->
    <button @click="showInfo = !showInfo">
        <i class="fas fa-info-circle text-blue-500 text-xl"></i>
    </button>

            <!-- ป๊อปอัพ -->
            <div class="relative inline-block">
            <button @click="showInfo = !showInfo">
                <i class="fas fa-info-circle text-blue-500 text-xl"></i>
            </button>

            <div x-show="showInfo" 
                @click.away="showInfo = false"
                class="absolute top-full left-[-20px] mt-3 w-80 shadow-lg bg-white rounded-xl ring-1 ring-black ring-opacity-5 p-4 z-50">
                
                <p class="text-base font-semibold">About</p>
                <p class="bg-blue-200 text-sm text-[#13A7FD] rounded-full px-2 py-1 my-2 inline-block font-semibold">Trello Teams</p>
                <p class="text-sm font-normal mb-2">This section displays all your Trello teams and their members.</p>
                <div class="flex items-center bg-yellow-100 rounded-md px-2 py-1 text-xs">
                    <span class="font-thin">Click the info icon to close this popup</span>
                </div>
            </div>
        </div>

                </div>
        </h2>

        <!-- ปุ่มดำเนินการต่างๆ -->
        <div class="flex items-center space-x-4 mr-8 mt-5">
            <!-- ปุ่มรีเฟรชข้อมูล -->
            <a href="{{ route('trello.teams.refresh') }}" class="bg-[#13A7FD] hover:bg-[#13A7FD] text-white py-2 px-4 rounded-full flex items-center ">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Refresh Data
            </a>
            <!-- ปุ่มตั้งค่า API -->
            <a href="{{ route('trello.settings.index') }}" class="text-[#13A7FD] hover:text-[#13A7FD] flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Trello API Settings
            </a>
        </div>
    </div>

    <!-- ส่วนแสดงรายการทีม -->
    @if(count($teams) > 0)
    <!-- แท็บแสดงทีมต่างๆ -->
    <div class="bg-white shadow overflow-hidden sm:rounded-xl ml-8 pt-1  mb-10 mr-8" x-data="{ activeTab: '{{ $teams[0]['id'] }}' }">
        <!-- เมนูแท็บ -->
        <div class="border-b border-gray-200 overflow-x-auto">
            <nav class="flex -mb-px whitespace-nowrap">
                @foreach($teams as $index => $team)
                <button
                    @click="activeTab = '{{ $team['id'] }}'"
                    :class="{ 'border-[#13A7FD] text-[#13A7FD]': activeTab === '{{ $team['id'] }}', 'border-transparent text-gray-500 hover:text-[#13A7FD] hover:border-gray-300': activeTab !== '{{ $team['id'] }}' }"
                    class="py-4 px-6 border-b-2 font-medium text-lg focus:outline-none transition-colors duration-200">
                    <div class="flex items-center">
                        <span>{{ $team['name'] }}</span>
                        <span class="ml-2 px-2 py-0.5 bg-cyan-100 text-xs text-[#13A7FD] rounded-full">{{ count($team['members']) }}</span>
                    </div>
                </button>
                @endforeach
                <div class="flex items-start justify-betweenflex flex justify-end ">
                   
                   @if(isset($team['url']))
                   <a href="{{ $team['url'] }}" target="_blank" class="text-sm text-[#13A7FD] hover:underline flex items-center ">
                       <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                       </svg>
                       Open in Trello
                   </a>
                   @endif
               </div>
        </div>
            </nav>
          

        <!-- เนื้อหาของแต่ละทีม -->
        <div>
            @foreach($teams as $team)
            <!-- ตารางแสดงสมาชิก -->
            <div x-show="activeTab === '{{ $team['id'] }}'" class="p-4">
                
                <!-- ส่วนหัวของทีม -->

                <!-- ตารางแสดงรายชื่อสมาชิก -->
                <div class="mt-4  rounded-xl p-4">
                    <h4 class="text-base font-medium text-[#13A7FD] mb- ">Team Members</h4>
                    <div class="overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($team['members'] as $member)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8">
                                                <div class="h-8 w-8 rounded-full flex items-center justify-center text-xs font-medium"
                                                    style="background-color: {{ '#' . substr(md5($member['fullName']), 0, 6) }}; color: white;">
                                                    {{ strtoupper(substr($member['fullName'], 0, 2)) }}
                                                </div>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900">{{ $member['fullName'] }}</p>
                                                <p class="text-xs text-gray-500">{{ $member['username'] }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            @if(isset($member['email']) && !empty($member['email']))
                                            {{ $member['email'] }}
                                            @else
                                            <span class="text-gray-500 italic">Email not found</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        @if($member['isRegistered'])
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Registered
                                        </span>
                                        @if(isset($member['role']))
                                        <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                                {{ $member['role'] == 'admin' ? 'bg-red-100 text-red-800' : 
                                                                   ($member['role'] == 'tester' ? 'bg-blue-100 text-blue-800' : 
                                                                    'bg-green-100 text-green-800') }}">
                                            {{ ucfirst($member['role']) }}
                                        </span>
                                        @endif
                                        @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            Not Registered
                                        </span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                        No members found for this team.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @else
    <!-- ข้อความเมื่อไม่พบทีม -->
    <div class="bg-white shadow overflow-hidden sm:rounded-md p-6 text-center">
        <p class="text-gray-500">No Trello teams found.</p>
        <p class="mt-1 text-sm">You don't have access to any teams or there's a permission issue.</p>
    </div>
    @endif
</div>
@endsection