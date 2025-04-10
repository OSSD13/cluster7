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

        // Check if this is a SprintReport or SavedReport and extract data accordingly
        $isSprintReport = $report instanceof \App\Models\SprintReport;

        if ($isSprintReport) {
            // For SprintReport, get values directly from the object
            $reportName = $report->report_name;
            $teamName = $report->team_name;
            $developers = $report->report_data['developers'] ?? [];
            $extraPoints = $report->report_data['extra_points'] ?? [];

            // Calculate sums
            $sumPointPersonal = 0;
            $sumTestPass = 0;
            $sumBug = 0;
            $sumCancel = 0;
            $sumFinal = 0;

            // Calculate sums from developers
            foreach ($developers as $dev) {
                $sumPointPersonal += $dev->point_personal ?? 0;
                $sumTestPass += $dev->test_pass ?? 0;
                $sumBug += $dev->bug ?? 0;
                $sumCancel += $dev->cancel ?? 0;
                $sumFinal += $dev->sum_final ?? 0;
            }

            // Calculate percentages
            $planPoint = isset($report->plan_point) && is_numeric($report->plan_point) ? $report->plan_point : $sumPointPersonal;
            $actualPoint = $sumTestPass;

            // Add extra points to actual points
            foreach ($extraPoints as $extraPoint) {
                $actualPoint += $extraPoint->points ?? $extraPoint->extra_point ?? 0;
            }

            // Calculate percentages
            $sumFinalPassPoint = $sumPointPersonal > 0 ? round(($sumTestPass / $sumPointPersonal) * 100) : 0;

        } else {
            // For SavedReport, extract from report_data
            $reportName = $report->name ?? $report->report_data['report_name'] ?? 'Report';
            $teamName = $report->report_data['team_name'] ?? null;
            $developers = $report->report_data['developers'] ?? [];
            $extraPoints = $report->report_data['extra_points'] ?? [];

            // Get calculated values from report_data if available
            $sumPointPersonal = $report->report_data['sum_point_personal'] ?? 0;
            $sumTestPass = $report->report_data['sum_test_pass'] ?? 0;
            $sumBug = $report->report_data['sum_bug'] ?? 0;
            $sumCancel = $report->report_data['sum_cancel'] ?? 0;
            $sumFinal = $report->report_data['sum_final'] ?? 0;
            $sumFinalPassPoint = $report->report_data['sum_final_pass_point'] ?? 0;

            // Calculate plan and actual points
            $planPoint = $report->report_data['plan_point'] ?? $sumPointPersonal;
            $actualPoint = $sumTestPass;

            // Add extra points to actual points
            foreach ($extraPoints as $extraPoint) {
                $actualPoint += $extraPoint->points ?? $extraPoint->extra_point ?? 0;
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

    <!-- PHP for Backlog Data Processing -->
    @php
        // Extract backlog data using BacklogController's method
        $backlogData = [];
        $groupedBacklogData = [];

        try {
            // Get backlog data from the BacklogController
            $backlogController = new \App\Http\Controllers\BacklogController(app(\App\Services\TrelloService::class));
            $backlogDataResult = $backlogController->getBacklogData();

            // Use backlog data from the report first if it exists (prefer direct source)
            if (isset($report->backlog_data) && !empty($report->backlog_data)) {
                if (is_array($report->backlog_data)) {
                    $backlogItems = $report->backlog_data;
                } else {
                    $backlogItems = json_decode($report->backlog_data, true);
                }

                if (!empty($backlogItems)) {
                    foreach ($backlogItems as $key => $item) {
                        $backlogData[] = (object)[
                            'sprint' => $item['sprint_number'] ?? $sprintNumber,
                            'personal' => $item['assigned_to'] ?? $item['assigned'] ?? 'Unassigned',
                            'point_all' => $item['points'] ?? 0,
                            'test_pass' => $item['test_pass'] ?? 0,
                            'bug' => $item['bug_count'] ?? 0,
                            'cancel' => isset($item['cancelled']) && $item['cancelled'] ? 'Yes' : 'No',
                            'team' => $item['team'] ?? null
                        ];
                    }
                }
            }
            // Otherwise, use the controller's data
            else if (isset($backlogDataResult['allBugs']) && !empty($backlogDataResult['allBugs'])) {
                foreach ($backlogDataResult['allBugs'] as $bug) {
                    $backlogData[] = (object)[
                        'sprint' => $bug['sprint_number'] ?? $sprintNumber,
                        'personal' => $bug['assigned_to'] ?? $bug['assigned'] ?? 'Unassigned',
                        'point_all' => $bug['points'] ?? 0,
                        'test_pass' => $bug['test_pass'] ?? 0,
                        'bug' => $bug['bug_count'] ?? 0,
                        'cancel' => isset($bug['cancelled']) && $bug['cancelled'] ? 'Yes' : 'No',
                        'team' => $bug['team'] ?? null
                    ];
                }
            }
            // Finally, check report_data
            else if (isset($report->report_data['backlog'])) {
                // New format where backlog data is stored directly in report_data
                foreach ($report->report_data['backlog'] as $teamName => $bugs) {
                    foreach ($bugs as $bug) {
                        // Skip completed bugs
                        if (isset($bug['status']) && $bug['status'] === 'completed') {
                            continue;
                        }

                        $backlogData[] = (object)[
                            'sprint' => $bug['sprint_number'] ?? $sprintNumber,
                            'personal' => $bug['assigned_to'] ?? $bug['assigned'] ?? 'Unassigned',
                            'point_all' => $bug['points'] ?? 0,
                            'test_pass' => $bug['test_pass'] ?? 0,
                            'bug' => $bug['bug_count'] ?? 0,
                            'cancel' => isset($bug['cancelled']) && $bug['cancelled'] ? 'Yes' : 'No',
                            'team' => $bug['team'] ?? $teamName ?? null
                        ];
                    }
                }
            } else if (isset($report->report_data['bug_cards'])) {
                // Try to extract from bug_cards data for compatibility
                foreach ($report->report_data['bug_cards'] as $teamName => $bugs) {
                    foreach ($bugs as $bug) {
                        // Only include backlog items
                        if (isset($bug['label']) && $bug['label'] === 'Backlog') {
                            $backlogData[] = (object)[
                                'sprint' => $bug['sprint_number'] ?? $sprintNumber,
                                'personal' => $bug['assigned_to'] ?? $bug['assigned'] ?? 'Unassigned',
                                'point_all' => $bug['points'] ?? 0,
                                'test_pass' => $bug['test_pass'] ?? 0,
                                'bug' => $bug['bug_count'] ?? 0,
                                'cancel' => isset($bug['cancelled']) && $bug['cancelled'] ? 'Yes' : 'No',
                                'team' => $bug['team'] ?? $teamName ?? null
                            ];
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Log error and continue with empty backlog data
            \Illuminate\Support\Facades\Log::error('Error processing backlog data: ' . $e->getMessage());
            $backlogData = [];
        }

        // Group backlog data by sprint and personal name
        foreach ($backlogData as $item) {
            // Create a key that combines sprint and personal name to group by
            $key = $item->sprint . '-' . $item->personal;

            if (!isset($groupedBacklogData[$key])) {
                // Create a new entry for this sprint-personal combination
                $groupedBacklogData[$key] = (object)[
                    'sprint' => $item->sprint,
                    'personal' => $item->personal,
                    'point_all' => $item->point_all,
                    'test_pass' => $item->test_pass,
                    'bug' => $item->bug,
                    'cancel' => $item->cancel,
                    'team' => $item->team ?? null
                ];
            } else {
                // Add to existing entry (sum the values)
                $groupedBacklogData[$key]->point_all += $item->point_all;
                $groupedBacklogData[$key]->test_pass += $item->test_pass;
                $groupedBacklogData[$key]->bug += $item->bug;
                // For cancel, keep 'Yes' if any item has 'Yes'
                if ($item->cancel === 'Yes') {
                    $groupedBacklogData[$key]->cancel = 'Yes';
                }
            }
        }

        // Convert the associative array to indexed array for use in the blade template
        $backlogData = array_values($groupedBacklogData);

        // Filter backlog data to show only entries from the current team
        // and only people who have names in Trello (not 'Unassigned')
        $filteredBacklogData = [];
        foreach ($backlogData as $item) {
            // Check if team matches or item has no team property
            // If currentTeamName is set, we want to match it specifically
            // If currentTeamName is null, we'll include all items
            $teamMatches = false;
            if (empty($teamName)) {
                // If no team name is set, include all
                $teamMatches = true;
            } else if (isset($item->team)) {
                // If item has team property, check for match
                $teamMatches = ($item->team == $teamName);
            } else {
                // If item has no team property, include it (default behavior)
                $teamMatches = true;
            }

            // Check if personal is not empty and not 'Unassigned'
            $hasValidName = !empty($item->personal) && $item->personal !== 'Unassigned';

            // Only include items that match both conditions
            if ($teamMatches && $hasValidName) {
                $filteredBacklogData[] = $item;
            }
        }

        // Replace the original array with the filtered one
        $backlogData = $filteredBacklogData;

        // Sort the backlog data by sprint and then by personal name
        usort($backlogData, function($a, $b) {
            // First sort by sprint number
            if ($a->sprint != $b->sprint) {
                return $a->sprint <=> $b->sprint;
            }
            // Then sort by personal name
            return $a->personal <=> $b->personal;
        });
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
                    <td class="text-left" colspan="8" style="font-size: 11pt;">{{ $report->author ?? $report->user->name ?? 'Mr. Apiwit Chatsiriwech' }}</td>
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
                    <td class="team-header" colspan="10" style="background-color: #4caf50; color: white; font-size: 14pt;">{{ $teamName ?? 'Raizeros Team' }}</td>
                </tr>
                <tr>
                    <td class="plan-header" colspan="2" style="background-color: #b3c6e7; font-size: 11pt;">PlanPoint</td>
                    <td class="text-center" colspan="2" style="font-size: 11pt;">{{ $planPoint }}</td>
                    <td style="border: none;"></td>
                    <td class="plan-header" colspan="2" style="background-color: #b3c6e7; font-size: 11pt;">ActualPoint</td>
                    <td class="text-center" colspan="2" style="font-size: 11pt;">{{ $actualPoint }}</td>
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
                    <td class="text-center" colspan="2" style="font-size: 11pt;">{{ $sumPointPersonal }}</td>
                    <td style="border: none;"></td>
                    <td class="sprint-header" colspan="2" style="background-color: #2962b9; color: white; font-size: 11pt;">ActualPoint Current Sprint</td>
                    <td class="text-center" colspan="2" style="font-size: 11pt;">{{ $actualPoint }}</td>
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
                @foreach($developers ?? [] as $developer)
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
                    <td class="text-right" style="font-size: 10pt;">{{ $sumPointPersonal }}</td>
                    <td class="text-right" style="font-size: 10pt;">{{ $sumTestPass }}</td>
                    <td class="text-right" style="font-size: 10pt;">{{ $sumBug }}</td>
                    <td class="text-center" style="font-size: 10pt;">{{ $sumFinalPassPoint }}%</td>
                    <td class="text-right" style="font-size: 10pt;">{{ $sumCancel }}</td>
                    <td class="text-right" style="font-size: 10pt;">{{ $sumFinal }}</td>
                    <td class="text-left" colspan="2" style="font-size: 10pt;"></td>
                </tr>

                <!-- Two Column Layout for Backlog and Extra Points -->
                <tr>
                    <td colspan="7" style="padding: 0;">
                        <table class="w-full" style="border-collapse: collapse;">
                            <tr>
                                <td class="backlog-header" style="background-color: #ff9800; color: white; font-size: 10pt; border: 1px solid black;">Backlog</td>
                            </tr>
                        </table>
                        <table class="w-full" style="border-collapse: collapse;">
                            <tr>
                                <td class="header-cell" style="width: 15%; background-color: #b3c6e7; font-size: 10pt; border: 1px solid black;">#sprint</td>
                                <td class="header-cell" style="width: 25%; background-color: #b3c6e7; font-size: 10pt; border: 1px solid black;">personal</td>
                                <td class="header-cell" style="width: 15%; background-color: #b3c6e7; font-size: 10pt; border: 1px solid black;">point all</td>
                                <td class="header-cell" style="width: 15%; background-color: #b3c6e7; font-size: 10pt; border: 1px solid black;">test pass</td>
                                <td class="header-cell" style="width: 15%; background-color: #b3c6e7; font-size: 10pt; border: 1px solid black;">bug</td>
                                <td class="header-cell" style="width: 15%; background-color: #b3c6e7; font-size: 10pt; border: 1px solid black;">cancel</td>
                            </tr>
                            @forelse($backlogData as $backlog)
                            <tr>
                                <td class="text-left" style="font-size: 10pt; border: 1px solid black;">{{ $backlog->sprint }}</td>
                                <td class="text-left" style="font-size: 10pt; border: 1px solid black;">{{ $backlog->personal }}</td>
                                <td class="text-right" style="font-size: 10pt; border: 1px solid black;">{{ $backlog->point_all }}</td>
                                <td class="text-right" style="font-size: 10pt; border: 1px solid black;">{{ $backlog->test_pass }}</td>
                                <td class="text-right" style="font-size: 10pt; border: 1px solid black;">{{ $backlog->bug }}</td>
                                <td class="text-right" style="font-size: 10pt; border: 1px solid black;">{{ $backlog->cancel }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td class="text-center" colspan="6" style="font-size: 10pt; border: 1px solid black;">ไม่พบ Backlog Cards สำหรับทีมนี้</td>
                            </tr>
                            @endforelse
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
                            @foreach($extraPoints ?? [] as $extraPoint)
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
            @php
                // Get minor cases from the database
                $minorCases = [];
                $teamName = $report->team_name ?? null;

                // สร้างรายการชื่อสมาชิกในทีม
                $teamMembers = [];
                if (!empty($report->developers)) {
                    foreach ($report->developers as $dev) {
                        if (!empty($dev->name)) {
                            $teamMembers[] = $dev->name;
                        }
                    }
                }

                try {
                    // ตรวจสอบว่ามีข้อมูลทั้งหมดกี่รายการ
                    $allMinorCases = \Illuminate\Support\Facades\DB::table('minor_cases')->get();
                    $totalMinorCases = count($allMinorCases);

                    // ดึงข้อมูลโดยไม่กรองเพื่อตรวจสอบ
                    $minorCases = \Illuminate\Support\Facades\DB::table('minor_cases')
                        ->orderBy('created_at', 'desc')
                        ->limit(15)
                        ->get();

                    // ถ้าพบข้อมูล และมี sprint number ที่ถูกระบุ
                    if (!empty($sprintNumber) && $sprintNumber != 'N/A' && count($minorCases) > 0) {
                        // กรองตาม sprint ที่ระบุ
                        $filteredBySprint = \Illuminate\Support\Facades\DB::table('minor_cases')
                            ->where('sprint', $sprintNumber)
                            ->orderBy('created_at', 'desc')
                            ->get();
                    } else {
                        // If no sprint information is available, get all minor cases
                        $minorCases = \App\Models\MinorCase::orderBy('created_at', 'desc')
                            ->limit(15) // Limit to a reasonable number
                            ->get();
                    }
                } catch (\Exception $e) {
                    // If MinorCase model doesn't exist or any other error occurs, keep empty array
                }
            @endphp

            @forelse($minorCases as $minorCase)
            <tr>
                <td class="text-left" style="font-size: 10pt; border: 1px solid black;">{{ $minorCase->sprint }}</td>
                <td class="text-left" style="font-size: 10pt; border: 1px solid black;">{{ $minorCase->card }}</td>
                <td class="text-left" style="font-size: 10pt; border: 1px solid black;">{{ $minorCase->description }}</td>
                <td class="text-left" style="font-size: 10pt; border: 1px solid black;">{{ $minorCase->member }}</td>
                <td class="text-right" style="font-size: 10pt; border: 1px solid black;">{{ $minorCase->points }}</td>
            </tr>
            @empty
            <tr>
                <td class="text-center" colspan="5" style="font-size: 10pt; border: 1px solid black;">ไม่พบ Minor Cases สำหรับทีมนี้</td>
            </tr>
            @endforelse
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

