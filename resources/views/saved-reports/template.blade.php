<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <style type="text/css">
        @page {
            size: A4 landscape;
            margin: 0.3cm;
        }

        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }

            html, body {
                width: 100%;
                height: 100%;
                margin: 0;
                padding: 0;
                font-size: 9pt;
            }

            .print-container {
                width: 100%;
                max-width: 100%;
                padding: 0;
                margin: 0 auto;
            }

            table {
                table-layout: fixed;
                width: 100%;
                page-break-inside: auto;
                font-size: 8pt;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            .text-xs, .text-sm, .text-10pt {
                font-size: 8pt !important;
            }

            .text-15pt {
                font-size: 10pt !important;
            }

            .text-18pt {
                font-size: 12pt !important;
            }

            .text-24pt {
                font-size: 16pt !important;
            }

            .py-2 {
                padding-top: 0.3rem !important;
                padding-bottom: 0.3rem !important;
            }

            .px-3 {
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
            }

            .report-logo {
                position: absolute;
                top: 1px;
                right: 20px;
                width: 80px;
                height: 80px;
            }
        }

        .report-logo {
            position: absolute;
            top: 1px;
            right: 20px;
            width: 80px;
            height: 80px;
        }
    </style>
</head>
<body class="bg-white">
    @if(isset($report->logo))
    <div class="report-logo">
        <img src="{{ asset('tttLogo.png') }}" alt="Logo" class="w-full h-full">
    </div>
    @endif

    <div class="print-container max-w-6xl mx-auto p-2">
        <table class="w-full border-collapse">
            <tbody>
                <tr>
                    <td class="border-b border-r border-black bg-white text-center font-bold text-black font-['docs-TH_SarabunPSK',Arial] text-11pt py-2 px-3" colspan="10">
                        <span class="text-24pt">เอกสารประเมินทีม DEV รายสัปดาห์<br></span>
                        <span class="text-14pt">(Document Weekly Developer Report)</span>
                    </td>
                </tr>
                <tr>
                    <td class="border-b border-r border-black bg-blue-100 text-left font-bold text-black font-['docs-TH_SarabunPSK',Arial] text-18pt py-2 px-3" colspan="10">Introduction</td>
                </tr>
                <tr>
                    <td class="border-b border-r border-black bg-white text-left text-black font-['docs-TH_SarabunPSK',Arial] text-15pt py-2 px-3" colspan="10">
                        เอกสารชุดนี้เรียกว่า Document Weekly Developer Report
                        มีวัถตุประสงค์เพื่อ<br> 1. เป็นเอกสารบันทึกข้อมูลผลลัพธืการประเมินทีม Developer รายสัปดาห์<br> 2.
                        ใช้สำหรับแสดงผลการประเมินการทำงานของทีม Developer<br> 3. สำหรับการดำเนินการติดตามผลลัพธ์รายสัปดาห์
                    </td>
                </tr>
                <tr>
                    <td class="border-b border-r border-black bg-blue-100 text-right font-bold text-black font-['docs-TH_SarabunPSK',Arial] text-15pt py-2 px-3" colspan="2">Author :</td>
                    <td class="border-b border-r border-black bg-white text-left text-black font-['docs-TH_SarabunPSK',Arial] text-15pt py-2 px-3" colspan="8">{{ $report->author ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="border-b border-r border-black bg-blue-100 text-right font-bold text-black font-['docs-TH_SarabunPSK',Arial] text-15pt py-2 px-3" colspan="2">Date Start :</td>
                    <td class="border-b border-r border-black bg-white text-left text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3" colspan="3">{{ $report->date_start ?? 'N/A' }}</td>
                    <td class="border-b border-r border-black bg-blue-100 text-right font-bold text-black font-['docs-TH_SarabunPSK',Arial] text-15pt py-2 px-3" colspan="2">Date Finish :</td>
                    <td class="border-b border-r border-black bg-white text-left text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3" colspan="3">{{ $report->date_finish ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="border-b border-r border-black bg-blue-100 text-right font-bold text-black font-['docs-TH_SarabunPSK',Arial] text-15pt py-2 px-3" colspan="2">Sprint :</td>
                    <td class="border-b border-r border-black bg-orange-200 text-left text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3" colspan="3">{{ $report->sprint ?? 'N/A' }}</td>
                    <td class="border-b border-r border-black bg-blue-100 text-right font-bold text-black font-['docs-TH_SarabunPSK',Arial] text-15pt py-2 px-3" colspan="2">Last update :</td>
                    <td class="border-b border-r border-black bg-green-100 text-left text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3" colspan="3">{{ $report->last_update ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="bg-green-600 text-center font-bold text-white font-['docs-TH_SarabunPSK',Arial] text-15pt py-2 px-3" colspan="10">{{ $report->team_name ?? 'Raizeros Team' }}</td>
                </tr>
                <tr>
                    <td class="border-b border-r border-black bg-blue-200 text-center font-bold text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3" colspan="2">PlanPoint</td>
                    <td class="border-b border-r border-black bg-white text-center text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3" colspan="2">
                        @php
                            // Calculate sum of personal points
                            $sumPersonalPoints = 0;
                            foreach ($report->developers ?? [] as $dev) {
                                $sumPersonalPoints += $dev->point_personal;
                            }

                            // Use input value if it exists, otherwise use sum of personal points
                            $planPoint = isset($report->plan_point) ? $report->plan_point : $sumPersonalPoints;
                        @endphp
                        {{ $planPoint }}
                    </td>
                    <td class="border-r border-black bg-white"></td>
                    <td class="border-b border-r border-black bg-blue-200 text-center font-bold text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3" colspan="2">ActualPoint</td>
                    <td class="border-b border-r border-black bg-white text-center text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3" colspan="2">
                        @php
                            $actualPoint = 0;
                            foreach ($report->developers ?? [] as $dev) {
                                $actualPoint += $dev->test_pass;
                            }
                        @endphp
                        {{ $actualPoint }}
                    </td>
                    <td class="bg-white"></td>
                </tr>
                <tr>
                    <td class="border-b border-r border-black bg-red-600 text-center font-bold text-white font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3" colspan="2">Remain</td>
                    <td class="border-b border-r border-black bg-white text-center text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3" colspan="2">
                        @php
                            $remain = $planPoint > 0 ? round((($planPoint - $actualPoint)/$planPoint)*100) : 0;
                        @endphp
                        {{ $remain }}%
                    </td>
                    <td class="border-r border-black bg-white"></td>
                    <td class="border-b border-r border-black bg-cyan-500 text-center font-bold text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3" colspan="2">Percent</td>
                    <td class="border-b border-r border-black bg-white text-center text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3" colspan="2">
                        @php
                            $percent = $planPoint > 0 ? round(($actualPoint / $planPoint) * 100) : 0;
                        @endphp
                        {{ $percent }}%
                    </td>
                    <td class="bg-white"></td>
                </tr>
                <tr>
                    <td class="border-b border-r border-black bg-blue-600 text-center font-bold text-white font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3" colspan="2">Point Current Sprint</td>
                    <td class="border-b border-r border-black bg-white text-center text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3" colspan="2">
                        @php
                            $currentSprintPoint = 0;
                            foreach ($report->developers ?? [] as $dev) {
                                $currentSprintPoint += $dev->point_personal;
                            }
                        @endphp
                        {{ $currentSprintPoint }}
                    </td>
                    <td class="border-r border-black bg-white"></td>
                    <td class="border-b border-r border-black bg-blue-600 text-center font-bold text-white font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3" colspan="2">ActualPoint Current Sprint</td>
                    <td class="border-b border-r border-black bg-white text-center text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3" colspan="2">
                        @php
                            $actualCurrentSprintPoint = 0;
                            $totalExtraPoints = 0;

                            // Calculate sum of final points
                            foreach ($report->developers ?? [] as $dev) {
                                $actualCurrentSprintPoint += $dev->test_pass;
                            }

                            // Calculate sum of extra points
                            foreach ($report->extra_points ?? [] as $extraPoint) {
                                $totalExtraPoints += $extraPoint->points ?? $extraPoint->extra_point ?? 0;
                            }

                            // Final value is sum of test_pass + extra points
                            $actualCurrentSprintPoint += $totalExtraPoints;
                        @endphp
                        {{ $actualCurrentSprintPoint }}
                    </td>
                    <td class="bg-white"></td>
                </tr>
                <tr>
                    <td class="border-b border-r border-black bg-blue-200 text-center font-bold text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3" colspan="2">Points from current sprint</td>
                    <td class="border-b border-r border-black bg-blue-200 text-center font-bold text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3">Point Personal</td>
                    <td class="border-b border-r border-black bg-blue-200 text-center font-bold text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3">Test Pass</td>
                    <td class="border-b border-r border-black bg-blue-200 text-center font-bold text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3">Bug</td>
                    <td class="border-b border-r border-black bg-blue-200 text-center font-bold text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3">Final Pass point</td>
                    <td class="border-b border-r border-black bg-blue-200 text-center font-bold text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3">Cancel</td>
                    <td class="border-b border-r border-black bg-red-600 text-center font-bold text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3">Sum Final</td>
                    <td class="border-b border-r border-black bg-blue-200 text-center font-bold text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3">Remark</td>
                    <td class="border-b border-r border-black bg-blue-200 text-center font-bold text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3">Day Off</td>
                </tr>
                @foreach($report->developers ?? [] as $developer)
                <tr>
                    <td class="border-b border-r border-black bg-white text-left text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3" colspan="2">{{ $developer->name }}</td>
                    <td class="border-b border-r border-black bg-white text-right text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3">{{ $developer->point_personal }}</td>
                    <td class="border-b border-r border-black bg-white text-right text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3">{{ $developer->test_pass }}</td>
                    <td class="border-b border-r border-black bg-white text-right text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3">{{ $developer->bug }}</td>
                    <td class="border-b border-r border-black bg-white text-center text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3">
                        @php
                            // Calculate pass percentage: (passPoint / pointPersonal) * 100
                            $passPercentage = $developer->point_personal > 0 ?
                                round(($developer->test_pass / $developer->point_personal) * 100) :
                                0;
                        @endphp
                        {{ $passPercentage }}%
                    </td>
                    <td class="border-b border-r border-black bg-white text-right text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3">{{ $developer->cancel }}</td>
                    <td class="border-b border-r border-black bg-white text-right text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3">{{ $developer->sum_final }}</td>
                    <td class="border-b border-r border-black bg-white text-left text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3">{{ $developer->remark }}</td>
                    <td class="border-b border-r border-black bg-white text-center text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3">{{ $developer->day_off }}</td>
                </tr>
                @endforeach
                <tr>
                    <td class="border-b border-r border-black bg-orange-500 text-center font-bold text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3" colspan="2">Sum</td>
                    <td class="border-b border-r border-black bg-white text-right text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3">{{ $report->sum_point_personal ?? 0 }}</td>
                    <td class="border-b border-r border-black bg-white text-right text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3">{{ $report->sum_test_pass ?? 0 }}</td>
                    <td class="border-b border-r border-black bg-white text-right text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3">{{ $report->sum_bug ?? 0 }}</td>
                    <td class="border-b border-r border-black bg-white text-center text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3">{{ $report->sum_final_pass_point ?? 0 }}</td>
                    <td class="border-b border-r border-black bg-white text-right text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3">{{ $report->sum_cancel ?? 0 }}</td>
                    <td class="border-b border-r border-black bg-white text-right text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3">{{ $report->sum_final ?? 0 }}</td>
                    <td class="bg-white text-left text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3" colspan="2"></td>
                </tr>

                <!-- Two Column Layout for Backlog and Extra Points -->
                <tr>
                    <td colspan="7" class="p-0">
                        <table class="w-full border-collapse">
                            <tr>
                                <td class="border-r border-black bg-orange-500 text-center font-bold text-white font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3">Backlog</td>
                            </tr>
                            <tr>
                                <td class="border-b border-r border-black bg-blue-200 text-center font-bold text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3">#Sprint</td>
                                <td class="border-b border-r border-black bg-blue-200 text-center font-bold text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3">Personal</td>
                                <td class="border-b border-r border-black bg-blue-200 text-center font-bold text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3">Point All</td>
                                <td class="border-b border-r border-black bg-blue-200 text-center font-bold text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3">Test Pass</td>
                                <td class="border-b border-r border-black bg-blue-200 text-center font-bold text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3">Bug</td>
                                <td class="border-b border-r border-black bg-blue-200 text-center font-bold text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3">Cancel</td>
                            </tr>
                            @foreach($report->backlog ?? [] as $backlog)
                            <tr>
                                <td class="border-b border-r border-black bg-white text-left text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3">{{ $backlog->sprint }}</td>
                                <td class="border-b border-r border-black bg-white text-left text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3">{{ $backlog->personal }}</td>
                                <td class="border-b border-r border-black bg-white text-left text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3">{{ $backlog->point_all }}</td>
                                <td class="border-b border-r border-black bg-white text-left text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3">{{ $backlog->test_pass }}</td>
                                <td class="border-b border-r border-black bg-white text-left text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3">{{ $backlog->bug }}</td>
                                <td class="border-b border-r border-black bg-white text-left text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3">{{ $backlog->cancel }}</td>
                            </tr>
                            @endforeach
                        </table>
                    </td>
                    <td colspan="3" class="p-0">
                        <table class="w-full border-collapse">
                            <tr>
                                <td class="border-r border-black bg-purple-800 text-center font-bold text-white font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3" colspan="2">Extra Point</td>
                            </tr>
                            <tr>
                                <td class="border-b border-r border-black bg-blue-200 text-center font-bold text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3">Personal</td>
                                <td class="border-b border-r border-black bg-blue-200 text-center font-bold text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3">Point</td>
                            </tr>
                            @foreach($report->extra_points ?? [] as $extraPoint)
                            <tr>
                                <td class="border-b border-r border-black bg-white text-left text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3">{{ $extraPoint->member_name ?? $extraPoint->extra_personal ?? '' }}</td>
                                <td class="border-b border-r border-black bg-white text-right text-black font-['docs-TH_SarabunPSK',Arial] text-10pt py-2 px-3">{{ $extraPoint->points ?? $extraPoint->extra_point ?? 0 }}</td>
                            </tr>
                            @endforeach
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Signature Section -->
        <table class="w-full border-collapse mt-2">
            <tr>
                <td class="w-1/5 text-center py-2">
                    <div class="text-center">
                        <p class="font-['docs-TH_SarabunPSK',Arial] text-10pt">Software Tester</p>
                        <p class="font-['docs-TH_SarabunPSK',Arial] text-10pt">ลงชื่อ: (................................................)</p>
                        <p class="font-['docs-TH_SarabunPSK',Arial] text-10pt">วันที่: ................................................</p>
                    </div>
                </td>
                <td class="w-1/5 text-center py-2">
                    <div class="text-center">
                        <p class="font-['docs-TH_SarabunPSK',Arial] text-10pt">Developer Team</p>
                        <p class="font-['docs-TH_SarabunPSK',Arial] text-10pt">ลงชื่อ: (................................................)</p>
                        <p class="font-['docs-TH_SarabunPSK',Arial] text-10pt">วันที่: ................................................</p>
                    </div>
                </td>
                <td class="w-1/5 text-center py-2">
                    <div class="text-center">
                        <p class="font-['docs-TH_SarabunPSK',Arial] text-10pt">Project Manager</p>
                        <p class="font-['docs-TH_SarabunPSK',Arial] text-10pt">ลงชื่อ: (................................................)</p>
                        <p class="font-['docs-TH_SarabunPSK',Arial] text-10pt">วันที่: ................................................</p>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <script>
        // Import data from localStorage first
        try {
            const importedData = localStorage.getItem('printTemplateData');
            if (importedData) {
                const parsedData = JSON.parse(importedData);
                console.log('Imported data for print:', parsedData);

                // Set global variables from imported data
                window.storyPointsData = parsedData.storyPointsData;
                window.cachedData = parsedData.cachedData;

                // If the server didn't provide full data, use our client-side data
                if (!window.storyPointsData || !window.storyPointsData.totalPoints) {
                    console.log('Using imported data instead of server data');
                    // Notify the backend about this by posting a message
                    try {
                        fetch('{{ route("trello.log") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                message: 'Using client-side data for print',
                                data: {
                                    hasStoryPointsData: !!window.storyPointsData,
                                    hasCachedData: !!window.cachedData
                                }
                            })
                        }).catch(err => console.error('Error logging to server:', err));
                    } catch (error) {
                        console.error('Could not log to server, but continuing execution:', error);
                    }
                }
            }
        } catch (error) {
            console.error('Error importing data from localStorage:', error);
        }

        // Set the story points data from cached data
        if (window.cachedData && window.cachedData.storyPoints) {
            window.storyPointsData = {
                ...window.cachedData.storyPoints,
                // Make sure all essential fields are defined with correct values
                totalPoints: window.cachedData.storyPoints.total || window.cachedData.storyPoints.totalPoints || 0,
                totalCompletedPoints: window.cachedData.storyPoints.completed || window.cachedData.storyPoints.totalCompletedPoints || 0,
                inProgress: window.cachedData.storyPoints.inProgress || 0,
                todo: window.cachedData.storyPoints.todo || 0,
                percentComplete: window.cachedData.storyPoints.percentComplete || 0
            };
            console.log('Fixed Story Points Data:', window.storyPointsData);
        }

        // Auto-trigger print dialog when page loads
        window.addEventListener('load', function() {
            // Check if autoprint parameter is present in URL or passed from controller
            const urlParams = new URLSearchParams(window.location.search);
            const autoPrintUrl = urlParams.get('autoprint') === 'true';
            const autoPrintController = {{ isset($autoprint) && $autoprint ? 'true' : 'false' }};

            if (autoPrintUrl || autoPrintController) {
                // Small delay to ensure everything is rendered properly
                setTimeout(function() {
                    window.print();
                }, 1000);
            }
        });
    </script>
</body>
</html>
