<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <style type="text/css">
        @page {
            size: A4 portrait;
            margin: 1cm;
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
                font-family: 'TH SarabunPSK', Arial, sans-serif;
                font-size: 10pt;
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
                border-collapse: collapse;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            td {
                vertical-align: middle;
                padding: 4px;
                border: 1px solid #000;
            }

            .intro-cell {
                border: 1px solid #000;
                background-color: #b3c6e7 !important;
            }

            .header-cell {
                background-color: #b3c6e7 !important;
                font-weight: bold;
                text-align: center;
            }

            .team-header {
                background-color: #4caf50 !important;
                color: white !important;
                font-weight: bold;
                text-align: center;
            }

            .plan-header {
                background-color: #b3c6e7 !important;
                font-weight: bold;
                text-align: center;
            }

            .remain-header {
                background-color: #e74c3c !important;
                color: white !important;
                font-weight: bold;
                text-align: center;
            }

            .percent-header {
                background-color: #17a2b8 !important;
                font-weight: bold;
                text-align: center;
            }

            .sprint-header {
                background-color: #2962b9 !important;
                color: white !important;
                font-weight: bold;
                text-align: center;
            }

            .sprint-value {
                background-color: #ffc107 !important;
            }

            .points-header {
                background-color: #b3c6e7 !important;
                font-weight: bold;
                text-align: center;
            }

            .sum-header {
                background-color: #ff9800 !important;
                font-weight: bold;
                text-align: center;
            }

            .backlog-header {
                background-color: #ff9800 !important;
                color: white !important;
                font-weight: bold;
                text-align: center;
            }

            .extra-header {
                background-color: #673ab7 !important;
                color: white !important;
                font-weight: bold;
                text-align: center;
            }

            .sum-final-header {
                background-color: #e74c3c !important;
                font-weight: bold;
                text-align: center;
            }

            .report-logo {
                position: absolute;
                top: 10px;
                right: 20px;
                width: 80px;
                height: 80px;
            }

            /* Adjust header font sizes */
            .document-title {
                font-size: 20pt !important;
            }

            .document-subtitle {
                font-size: 12pt !important;
            }

            .section-header {
                font-size: 14pt !important;
            }

            .field-label {
                font-size: 11pt !important;
            }

            .field-value {
                font-size: 11pt !important;
            }

            .team-header {
                font-size: 14pt !important;
            }

            .metric-header {
                font-size: 11pt !important;
            }

            .metric-value {
                font-size: 11pt !important;
            }

            .table-header {
                font-size: 10pt !important;
            }

            .table-data {
                font-size: 10pt !important;
            }

            .signature-text {
                font-size: 10pt !important;
            }
        }

        @media screen {
            html, body {
                font-family: 'TH SarabunPSK', Arial, sans-serif;
                font-size: 10pt;
            }

            table {
                border-collapse: collapse;
            }

            td {
                border: 1px solid #000;
                padding: 4px;
                vertical-align: middle;
            }

            .report-logo {
                position: absolute;
                top: 10px;
                right: 20px;
                width: 80px;
                height: 80px;
            }
        }
    </style>
</head>
<body class="bg-white">
    @php
        // Extract sprint number if it's not already numeric
        $sprintNumber = $report->sprint ?? 'N/A';
        if (is_string($sprintNumber) && !is_numeric($sprintNumber) && strpos($sprintNumber, 'Sprint') === false) {
            // Try to extract just the number if it's in a format like "Points from Team Alpha"
            $sprintObj = \App\Models\Sprint::getCurrentSprint();
            if ($sprintObj) {
                $sprintNumber = "{$sprintObj->sprint_number}";
            }
        }

        // Get the sprint object
        $sprintObj = \App\Models\Sprint::getCurrentSprint();

        // Get dates from sprint settings
        if ($sprintObj) {
            // Use exact dates from the sprint object - no formatting
            $sprintStartDate = $sprintObj->start_date;
            $sprintEndDate = $sprintObj->end_date;

            // Use the same format as in the TrelloController's getCurrentSprintInfo method
            $formattedStartDate = $sprintStartDate->format('d/m/Y');
            $formattedEndDate = $sprintEndDate->format('d/m/Y');
        } else {
            // Fallback to report dates if sprint is not available
            $sprintStartDate = $report->date_start ?? now()->format('Y-m-d');
            $sprintEndDate = $report->date_finish ?? now()->addDays(7)->format('Y-m-d');

            // Convert to Carbon instances if they're strings
            if (is_string($sprintStartDate)) {
                $sprintStartDate = \Carbon\Carbon::parse($sprintStartDate);
            }

            if (is_string($sprintEndDate)) {
                $sprintEndDate = \Carbon\Carbon::parse($sprintEndDate);
            }

            $formattedStartDate = $sprintStartDate->format('d/m/Y');
            $formattedEndDate = $sprintEndDate->format('d/m/Y');
        }
    @endphp

    @if(isset($report->logo))
    <div class="report-logo">
        <img src="{{ asset('tttLogo.png') }}" alt="Logo" class="w-full h-full">
    </div>
    @endif

    <div class="print-container max-w-6xl mx-auto p-2">
        <table class="w-full border-collapse">
            <tbody>
                <tr>
                    <td class="text-center font-bold" colspan="10" style="border: 1px solid black; padding: 10px;">
                        <div style="font-size: 20pt;">เอกสารประเมินทีม DEV รายสัปดาห์</div>
                        <div style="font-size: 12pt;">(Document Weekly Developer Report)</div>
                    </td>
                </tr>
                <tr>
                    <td class="intro-cell text-left font-bold" colspan="10" style="background-color: #b3c6e7; font-size: 14pt; padding: 6px;">Introduction</td>
                </tr>
                <tr>
                    <td class="text-left" colspan="10" style="font-size: 11pt; padding: 6px;">
                        เอกสารชุดนี้เรียกว่า Document Weekly Developer Report มีวัถตุประสงค์เพื่อ<br>
                        1. เป็นเอกสารบันทึกข้อมูลผลลัพธืการประเมินทีม Developer รายสัปดาห์<br>
                        2. ใช้สำหรับแสดงผลการประเมินการทำงานของทีม Developer<br>
                        3. สำหรับการดำเนินการติดตามผลลัพธ์รายสัปดาห์
                    </td>
                </tr>
                <tr>
                    <td class="header-cell text-right font-bold" colspan="2" style="background-color: #b3c6e7; font-size: 11pt;">Author :</td>
                    <td class="text-left" colspan="8" style="font-size: 11pt;">{{ $report->author ?? 'Mr. Apiwit Chatsiriwech' }}</td>
                </tr>
                <tr>
                    <td class="header-cell text-right font-bold" colspan="2" style="background-color: #b3c6e7; font-size: 11pt;">Date Start :</td>
                    <td class="text-left" colspan="3" style="font-size: 11pt;">{{ $formattedStartDate }}</td>
                    <td class="header-cell text-right font-bold" colspan="2" style="background-color: #b3c6e7; font-size: 11pt;">Date Finish :</td>
                    <td class="text-left" colspan="3" style="font-size: 11pt;">{{ $formattedEndDate }}</td>
                </tr>
                <tr>
                    <td class="header-cell text-right font-bold" colspan="2" style="background-color: #b3c6e7; font-size: 11pt;">Sprint :</td>
                    <td class="sprint-value text-left" colspan="3" style="background-color: #ffc107; font-size: 11pt;">#{{ $sprintNumber }}</td>
                    <td class="header-cell text-right font-bold" colspan="2" style="background-color: #b3c6e7; font-size: 11pt;">Last update :</td>
                    <td class="text-left" colspan="3" style="background-color: #d9ead3; font-size: 11pt;">{{ $report->last_update ?? now()->format('d/m/Y - H:i ช.') }}</td>
                </tr>
                <tr>
                    <td class="team-header" colspan="10" style="background-color: #4caf50; color: white; font-size: 14pt;">{{ $report->team_name ?? 'Raizeros Team' }}</td>
                </tr>
                <tr>
                    <td class="plan-header" colspan="2" style="background-color: #b3c6e7; font-size: 11pt;">PlanPoint</td>
                    <td class="text-center" colspan="2" style="font-size: 11pt;">
                        @php
                            // ใช้ค่า plan_point จากรายงานโดยตรงถ้ามี
                            $planPoint = 0;
                            
                            // ตรวจสอบว่ามีค่า plan_point ใน report หรือไม่
                            if (isset($report->plan_point) && is_numeric($report->plan_point)) {
                                $planPoint = $report->plan_point;
                            } 
                            // ถ้าไม่มี ให้คำนวณจาก personal points
                            else {
                                foreach ($report->developers ?? [] as $dev) {
                                    $planPoint += isset($dev->point_personal) ? $dev->point_personal : 0;
                                }
                            }
                        @endphp
                        {{ $planPoint }}
                    </td>
                    <td style="border: none;"></td>
                    <td class="plan-header" colspan="2" style="background-color: #b3c6e7; font-size: 11pt;">ActualPoint</td>
                    <td class="text-center" colspan="2" style="font-size: 11pt;">
                        @php
                            $actualPoint = 0;
                            foreach ($report->developers ?? [] as $dev) {
                                $actualPoint += $dev->test_pass;
                            }
                            
                            // รวม extra points เข้ากับ actual points เพื่อให้สอดคล้องกับหน้ารายงานหลัก
                            foreach ($report->extra_points ?? [] as $extraPoint) {
                                $actualPoint += $extraPoint->points ?? $extraPoint->extra_point ?? 0;
                            }
                        @endphp
                        {{ $actualPoint }}
                    </td>
                    <td style="border: none;"></td>
                </tr>
                <tr>
                    <td class="remain-header" colspan="2" style="background-color: #e74c3c; color: white; font-size: 11pt;">Remain</td>
                    <td class="text-center" colspan="2" style="font-size: 11pt;">
                        @php
                            $remain = $planPoint > 0 ? round((($planPoint - $actualPoint)/$planPoint)*100) : 0;
                        @endphp
                        {{ $remain }}%
                    </td>
                    <td style="border: none;"></td>
                    <td class="percent-header" colspan="2" style="background-color: #17a2b8; font-size: 11pt;">Percent</td>
                    <td class="text-center" colspan="2" style="font-size: 11pt;">
                        @php
                            $percent = $planPoint > 0 ? round(($actualPoint / $planPoint) * 100) : 0;
                        @endphp
                        {{ $percent }}%
                    </td>
                    <td style="border: none;"></td>
                </tr>
                <tr>
                    <td class="sprint-header" colspan="2" style="background-color: #2962b9; color: white; font-size: 11pt;">Point Current Sprint</td>
                    <td class="text-center" colspan="2" style="font-size: 11pt;">
                        @php
                            $currentSprintPoint = 0;
                            foreach ($report->developers ?? [] as $dev) {
                                $currentSprintPoint += $dev->point_personal;
                            }
                        @endphp
                        {{ $currentSprintPoint }}
                    </td>
                    <td style="border: none;"></td>
                    <td class="sprint-header" colspan="2" style="background-color: #2962b9; color: white; font-size: 11pt;">ActualPoint Current Sprint</td>
                    <td class="text-center" colspan="2" style="font-size: 11pt;">
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
                    <td style="border: none;"></td>
                </tr>
                <tr>
                    <td class="points-header" colspan="2" style="background-color: #b3c6e7; font-size: 10pt;">Points from current sprint</td>
                    <td class="points-header" style="background-color: #b3c6e7; font-size: 10pt;">Point Personal</td>
                    <td class="points-header" style="background-color: #b3c6e7; font-size: 10pt;">Test Pass</td>
                    <td class="points-header" style="background-color: #b3c6e7; font-size: 10pt;">Bug</td>
                    <td class="points-header" style="background-color: #b3c6e7; font-size: 10pt;">Final Pass point</td>
                    <td class="points-header" style="background-color: #b3c6e7; font-size: 10pt;">Cancel</td>
                    <td class="sum-final-header" style="background-color: #e74c3c; font-size: 10pt;">Sum Final</td>
                    <td class="points-header" style="background-color: #b3c6e7; font-size: 10pt;">Remark</td>
                    <td class="points-header" style="background-color: #b3c6e7; font-size: 10pt;">Day Off</td>
                </tr>
                @foreach($report->developers ?? [] as $developer)
                <tr>
                    <td class="text-left" colspan="2" style="font-size: 10pt;">{{ $developer->name }}</td>
                    <td class="text-right" style="font-size: 10pt;">{{ $developer->point_personal }}</td>
                    <td class="text-right" style="font-size: 10pt;">{{ $developer->test_pass }}</td>
                    <td class="text-right" style="font-size: 10pt;">{{ $developer->bug }}</td>
                    <td class="text-center" style="font-size: 10pt;">
                        @php
                            // Calculate pass percentage: (passPoint / pointPersonal) * 100
                            $passPercentage = $developer->point_personal > 0 ?
                                round(($developer->test_pass / $developer->point_personal) * 100) :
                                0;
                        @endphp
                        {{ $passPercentage }}%
                    </td>
                    <td class="text-right" style="font-size: 10pt;">{{ $developer->cancel }}</td>
                    <td class="text-right" style="font-size: 10pt;">{{ $developer->sum_final }}</td>
                    <td class="text-left" style="font-size: 10pt;">{{ $developer->remark }}</td>
                    <td class="text-center" style="font-size: 10pt; background-color: {{ $developer->day_off == 'Not Test' ? '#e74c3c' : 'white' }}; color: {{ $developer->day_off == 'Not Test' ? 'white' : 'black' }};">{{ $developer->day_off }}</td>
                </tr>
                @endforeach
                <tr>
                    <td class="sum-header" colspan="2" style="background-color: #ff9800; font-size: 10pt;">Sum</td>
                    <td class="text-right" style="font-size: 10pt;">{{ $report->sum_point_personal ?? 0 }}</td>
                    <td class="text-right" style="font-size: 10pt;">{{ $report->sum_test_pass ?? 0 }}</td>
                    <td class="text-right" style="font-size: 10pt;">{{ $report->sum_bug ?? 0 }}</td>
                    <td class="text-center" style="font-size: 10pt;">{{ $report->sum_final_pass_point ?? 0 }}%</td>
                    <td class="text-right" style="font-size: 10pt;">{{ $report->sum_cancel ?? 0 }}</td>
                    <td class="text-right" style="font-size: 10pt;">{{ $report->sum_final ?? 0 }}</td>
                    <td class="text-left" colspan="2" style="font-size: 10pt;"></td>
                </tr>

                <!-- Two Column Layout for Backlog and Extra Points -->
                <tr>
                    <td colspan="7" style="padding: 0;">
                        <table class="w-full border-collapse">
                            <tr>
                                <td class="backlog-header" style="background-color: #ff9800; color: white; font-size: 10pt; border: 1px solid black;">Backlog</td>
                            </tr>
                            <tr>
                                <td class="points-header" style="background-color: #b3c6e7; font-size: 10pt; border: 1px solid black;">#Sprint</td>
                                <td class="points-header" style="background-color: #b3c6e7; font-size: 10pt; border: 1px solid black;">Personal</td>
                                <td class="points-header" style="background-color: #b3c6e7; font-size: 10pt; border: 1px solid black;">Point All</td>
                                <td class="points-header" style="background-color: #b3c6e7; font-size: 10pt; border: 1px solid black;">Test Pass</td>
                                <td class="points-header" style="background-color: #b3c6e7; font-size: 10pt; border: 1px solid black;">Bug</td>
                                <td class="points-header" style="background-color: #b3c6e7; font-size: 10pt; border: 1px solid black;">Cancel</td>
                            </tr>
                            @foreach($report->backlog ?? [] as $backlog)
                            <tr>
                                {{-- <td class="text-left" style="font-size: 10pt; border: 1px solid black;">{{ $backlog->sprint ?? null }}</td>
                                <td class="text-left" style="font-size: 10pt; border: 1px solid black;">{{ $backlog->personal ?? null }}</td>
                                <td class="text-left" style="font-size: 10pt; border: 1px solid black;">{{ $backlog->point_all ?? null }}</td>
                                <td class="text-left" style="font-size: 10pt; border: 1px solid black;">{{ $backlog->test_pass ?? null }}</td>
                                <td class="text-left" style="font-size: 10pt; border: 1px solid black;">{{ $backlog->bug ?? null }}</td>
                                <td class="text-left" style="font-size: 10pt; border: 1px solid black;">{{ $backlog->cancel ?? null }}</td> --}}
                            </tr>
                            @endforeach
                        </table>
                    </td>
                    <td colspan="3" style="padding: 0;">
                        <table class="w-full border-collapse">
                            <tr>
                                <td class="extra-header" colspan="2" style="background-color: #673ab7; color: white; font-size: 10pt; border: 1px solid black;">Extra Point</td>
                            </tr>
                            <tr>
                                <td class="points-header" style="background-color: #b3c6e7; font-size: 10pt; border: 1px solid black;">Personal</td>
                                <td class="points-header" style="background-color: #b3c6e7; font-size: 10pt; border: 1px solid black;">Point</td>
                            </tr>
                            @foreach($report->extra_points ?? [] as $extraPoint)
                            <tr>
                                <td class="text-left" style="font-size: 10pt; border: 1px solid black;">{{ $extraPoint->member_name ?? $extraPoint->extra_personal ?? 'Ziwi' }}</td>
                                <td class="text-right" style="font-size: 10pt; border: 1px solid black;">{{ $extraPoint->points ?? $extraPoint->extra_point ?? 0.5 }}</td>
                            </tr>
                            @endforeach
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Minor Case Section -->
        <table class="w-full border-collapse mt-2">
            <tr>
                <td class="points-header" style="background-color: #9e9e9e; font-size: 10pt; border: 1px solid black;" colspan="10">Minor Case</td>
            </tr>
            <tr>
                <td class="points-header" style="background-color: white; font-size: 10pt; border: 1px solid black;">Sprint</td>
                <td class="points-header" style="background-color: white; font-size: 10pt; border: 1px solid black;">Card Detail</td>
                <td class="points-header" style="background-color: white; font-size: 10pt; border: 1px solid black;">Defect Detail</td>
                <td class="points-header" style="background-color: white; font-size: 10pt; border: 1px solid black;">Personal</td>
                <td class="points-header" style="background-color: white; font-size: 10pt; border: 1px solid black;">Point</td>
            </tr>
            <!-- Add your minor case data rows here -->
        </table>

        <!-- Signature Section -->
        <table class="w-full border-collapse mt-4">
            <tr>
                <td class="w-1/3 text-center py-2">
                    <div class="text-center">
                        <p style="font-size: 10pt;">Software Tester</p>
                        <p style="font-size: 10pt;">ลงชื่อ: (................................................)</p>
                        <p style="font-size: 10pt;">วันที่: ................................................</p>
                    </div>
                </td>
                <td class="w-1/3 text-center py-2">
                    <div class="text-center">
                        <p style="font-size: 10pt;">Developer Team Leader </p>
                        <p style="font-size: 10pt;">ลงชื่อ: (................................................)</p>
                        <p style="font-size: 10pt;">วันที่: ................................................</p>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2" class="text-center py-2">
                    <div class="text-center">
                        <p style="font-size: 10pt;">Project Manager</p>
                        <p style="font-size: 10pt;">ลงชื่อ: (................................................)</p>
                        <p style="font-size: 10pt;">วันที่: ................................................</p>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <script>
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

