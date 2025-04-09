<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link type="text/css" rel="stylesheet" href="{{ asset('css/sheet.css') }}">
    <style type="text/css">
      /* Ensure color printing */
      @media print {
        * {
          -webkit-print-color-adjust: exact !important;
          print-color-adjust: exact !important;
          color-adjust: exact !important;
        }

        /* Force background colors to be visible when printing */
        .ritz .waffle td, .ritz .waffle th {
          print-color-adjust: exact;
          -webkit-print-color-adjust: exact;
        }
      }

      .ritz .waffle a {
        color: inherit;
      }

      .ritz .waffle .s16 {
        border-bottom: 1px SOLID #000000;
        border-right: 1px SOLID #000000;
        background-color: #46bdc6;
        text-align: center;
        font-weight: bold;
        color: #000000;
        font-family: Arial;
        font-size: 10pt;
        vertical-align: middle;
        white-space: nowrap;
        direction: ltr;
        padding: 2px 3px 2px 3px;
      }

      .ritz .waffle .s19 {
        border-bottom: 1px SOLID #000000;
        border-right: 1px SOLID #000000;
        background-color: #ff0000;
        text-align: center;
        font-weight: bold;
        color: #000000;
        font-family: Arial;
        font-size: 10pt;
        vertical-align: middle;
        white-space: nowrap;
        direction: ltr;
        padding: 2px 3px 2px 3px;
      }

      .ritz .waffle .s32 {
        background-color: #ffffff;
        text-align: center;
        color: #000000;
        font-family: "docs-TH SarabunPSK", Arial;
        font-size: 18pt;
        vertical-align: middle;
        white-space: nowrap;
        direction: ltr;
        padding: 2px 3px 2px 3px;
      }

      .ritz .waffle .s2 {
        border-bottom: 1px SOLID #000000;
        border-right: 1px SOLID #000000;
        background-color: #ffffff;
        text-align: left;
        color: #000000;
        font-family: "docs-TH SarabunPSK", Arial;
        font-size: 15pt;
        vertical-align: middle;
        white-space: nowrap;
        direction: ltr;
        padding: 2px 3px 2px 3px;
      }

      .ritz .waffle .s29 {
        border-bottom: 1px SOLID #000000;
        border-right: 1px SOLID #000000;
        background-color: #ffffff;
        text-align: left;
        color: #000000;
        font-family: Arial;
        font-size: 11pt;
        vertical-align: middle;
        white-space: nowrap;
        direction: ltr;
        padding: 2px 3px 2px 3px;
      }

      .ritz .waffle .s22 {
        border-right: 1px SOLID #000000;
        background-color: #e69138;
        text-align: center;
        font-weight: bold;
        color: #ffffff;
        font-family: Arial;
        font-size: 10pt;
        vertical-align: middle;
        white-space: normal;
        overflow: hidden;
        word-wrap: break-word;
        direction: ltr;
        padding: 2px 3px 2px 3px;
      }

      .ritz .waffle .s27 {
        border-bottom: 1px SOLID #000000;
        border-right: 1px SOLID #000000;
        background-color: #95b3d7;
        text-align: center;
        color: #000000;
        font-family: Arial;
        font-size: 10pt;
        vertical-align: middle;
        white-space: nowrap;
        direction: ltr;
        padding: 2px 3px 2px 3px;
      }

      .ritz .waffle .s26 {
        border-bottom: 1px SOLID #000000;
        border-right: 1px SOLID #000000;
        background-color: #e69138;
        text-align: center;
        font-weight: bold;
        color: #ffffff;
        font-family: Arial;
        font-size: 10pt;
        vertical-align: middle;
        white-space: normal;
        overflow: hidden;
        word-wrap: break-word;
        direction: ltr;
        padding: 2px 3px 2px 3px;
      }

      .ritz .waffle .s14 {
        border-right: 1px SOLID #000000;
        background-color: #ffffff;
        text-align: left;
        color: #000000;
        font-family: Arial;
        font-size: 10pt;
        vertical-align: middle;
        white-space: nowrap;
        direction: ltr;
        padding: 2px 3px 2px 3px;
      }

      .ritz .waffle .s25 {
        border-bottom: 1px SOLID #000000;
        border-right: 1px SOLID #000000;
        background-color: #ff6d01;
        text-align: center;
        color: #000000;
        font-family: Arial;
        font-size: 10pt;
        vertical-align: middle;
        white-space: nowrap;
        direction: ltr;
        padding: 2px 3px 2px 3px;
      }

      .ritz .waffle .s0 {
        border-bottom: 1px SOLID #000000;
        background-color: #ffffff;
        text-align: center;
        font-weight: bold;
        color: #000000;
        font-family: "docs-TH SarabunPSK", Arial;
        font-size: 11pt;
        vertical-align: middle;
        white-space: nowrap;
        direction: ltr;
        padding: 2px 3px 2px 3px;
      }

      .ritz .waffle .s17 {
        border-bottom: 1px SOLID #000000;
        border-right: 1px SOLID #000000;
        background-color: #1155cc;
        text-align: center;
        font-weight: bold;
        color: #ffffff;
        font-family: Arial;
        font-size: 10pt;
        vertical-align: middle;
        white-space: nowrap;
        direction: ltr;
        padding: 2px 3px 2px 3px;
      }

      .ritz .waffle .s30 {
        border-bottom: 1px SOLID #000000;
        border-right: 1px SOLID #000000;
        background-color: #ffffff;
        text-align: center;
        color: #000000;
        font-family: Arial;
        font-size: 10pt;
        vertical-align: middle;
        white-space: normal;
        overflow: hidden;
        word-wrap: break-word;
        direction: ltr;
        padding: 2px 3px 2px 3px;
      }

      .ritz .waffle .s5 {
        border-bottom: 1px SOLID #000000;
        border-right: 1px SOLID #000000;
        background-color: #ffffff;
        text-align: left;
        color: #000000;
        font-family: Arial;
        font-size: 10pt;
        vertical-align: middle;
        white-space: nowrap;
        direction: ltr;
        padding: 2px 3px 2px 3px;
      }

      .ritz .waffle .s31 {
        background-color: #ffffff;
        text-align: center;
        font-weight: bold;
        color: #000000;
        font-family: "docs-TH SarabunPSK", Arial;
        font-size: 14pt;
        vertical-align: middle;
        white-space: nowrap;
        direction: ltr;
        padding: 2px 3px 2px 3px;
      }

      .ritz .waffle .s7 {
        border-bottom: 1px SOLID #000000;
        border-right: 1px SOLID #000000;
        background-color: #d9ead3;
        text-align: left;
        color: #000000;
        font-family: Arial;
        font-size: 10pt;
        vertical-align: middle;
        white-space: nowrap;
        direction: ltr;
        padding: 2px 3px 2px 3px;
      }

      .ritz .waffle .s8 {
        background-color: #ffffff;
        text-align: left;
        color: #000000;
        font-family: Arial;
        font-size: 10pt;
        vertical-align: bottom;
        white-space: nowrap;
        direction: ltr;
        padding: 2px 3px 2px 3px;
      }

      .ritz .waffle .s21 {
        border-bottom: 1px SOLID #000000;
        border-right: 1px SOLID #000000;
        background-color: #ff6d01;
        text-align: center;
        font-weight: bold;
        color: #000000;
        font-family: Arial;
        font-size: 10pt;
        vertical-align: middle;
        white-space: nowrap;
        direction: ltr;
        padding: 2px 3px 2px 3px;
      }

      .ritz .waffle .s12 {
        border-bottom: 1px SOLID #000000;
        border-right: 1px SOLID #000000;
        background-color: #95b3d7;
        text-align: center;
        font-weight: bold;
        color: #000000;
        font-family: Arial;
        font-size: 10pt;
        vertical-align: middle;
        white-space: nowrap;
        direction: ltr;
        padding: 2px 3px 2px 3px;
      }

      .ritz .waffle .s24 {
        border-bottom: 1px SOLID #000000;
        border-right: 1px SOLID #000000;
        background-color: #1155cc;
        text-align: center;
        color: #ffffff;
        font-family: Arial;
        font-size: 10pt;
        vertical-align: middle;
        white-space: nowrap;
        direction: ltr;
        padding: 2px 3px 2px 3px;
      }

      .ritz .waffle .s20 {
        border-bottom: 1px SOLID #000000;
        border-right: 1px SOLID #000000;
        background-color: #ffffff;
        text-align: right;
        color: #000000;
        font-family: Arial;
        font-size: 10pt;
        vertical-align: middle;
        white-space: nowrap;
        direction: ltr;
        padding: 2px 3px 2px 3px;
      }

      .ritz .waffle .s18 {
        border-bottom: 1px SOLID #000000;
        background-color: #ffffff;
        text-align: center;
        font-weight: bold;
        color: #ffffff;
        font-family: Arial;
        font-size: 10pt;
        vertical-align: middle;
        white-space: nowrap;
        direction: ltr;
        padding: 2px 3px 2px 3px;
      }

      .ritz .waffle .s1 {
        border-bottom: 1px SOLID #000000;
        border-right: 1px SOLID #000000;
        background-color: #95b3d7;
        text-align: left;
        font-weight: bold;
        color: #000000;
        font-family: "docs-TH SarabunPSK", Arial;
        font-size: 18pt;
        vertical-align: middle;
        white-space: nowrap;
        direction: ltr;
        padding: 2px 3px 2px 3px;
      }

      .ritz .waffle .s9 {
        background-color: #6aa84f;
        text-align: center;
        font-weight: bold;
        color: #ffffff;
        font-family: Arial;
        font-size: 15pt;
        vertical-align: middle;
        white-space: nowrap;
        direction: ltr;
        padding: 2px 3px 2px 3px;
      }

      .ritz .waffle .s4 {
        border-bottom: 1px SOLID #000000;
        border-right: 1px SOLID #000000;
        background-color: #95b3d7;
        text-align: right;
        font-weight: bold;
        color: #000000;
        font-family: "docs-TH SarabunPSK", Arial;
        font-size: 15pt;
        vertical-align: middle;
        white-space: nowrap;
        direction: ltr;
        padding: 2px 3px 2px 3px;
      }

      .ritz .waffle .s13 {
        border-bottom: 1px SOLID #000000;
        border-right: 1px SOLID #000000;
        background-color: #ffffff;
        text-align: center;
        color: #000000;
        font-family: Arial;
        font-size: 10pt;
        vertical-align: middle;
        white-space: nowrap;
        direction: ltr;
        padding: 2px 3px 2px 3px;
      }

      .ritz .waffle .s15 {
        border-bottom: 1px SOLID #000000;
        border-right: 1px SOLID #000000;
        background-color: #cc0000;
        text-align: center;
        font-weight: bold;
        color: #ffffff;
        font-family: Arial;
        font-size: 10pt;
        vertical-align: middle;
        white-space: nowrap;
        direction: ltr;
        padding: 2px 3px 2px 3px;
      }

      .ritz .waffle .s28 {
        border-right: 1px SOLID #000000;
        background-color: #ffffff;
        text-align: right;
        color: #ffffff;
        font-family: Arial;
        font-size: 10pt;
        vertical-align: middle;
        white-space: nowrap;
        direction: ltr;
        padding: 2px 3px 2px 3px;
      }

      .ritz .waffle .s11 {
        background-color: #ffffff;
        text-align: left;
        color: #000000;
        font-family: Arial;
        font-size: 10pt;
        vertical-align: middle;
        white-space: nowrap;
        direction: ltr;
        padding: 2px 3px 2px 3px;
      }

      .ritz .waffle .s3 {
        border-bottom: 1px SOLID #000000;
        background-color: #ffffff;
        text-align: left;
        color: #000000;
        font-family: Arial;
        font-size: 10pt;
        vertical-align: bottom;
        white-space: nowrap;
        direction: ltr;
        padding: 2px 3px 2px 3px;
      }

      .ritz .waffle .s6 {
        border-bottom: 1px SOLID #000000;
        border-right: 1px SOLID #000000;
        background-color: #ffd966;
        text-align: left;
        color: #000000;
        font-family: Arial;
        font-size: 10pt;
        vertical-align: middle;
        white-space: nowrap;
        direction: ltr;
        padding: 2px 3px 2px 3px;
      }

      .ritz .waffle .s23 {
        border-right: 1px SOLID #000000;
        background-color: #741b47;
        text-align: center;
        font-weight: bold;
        color: #ffffff;
        font-family: Arial;
        font-size: 10pt;
        vertical-align: middle;
        white-space: normal;
        overflow: hidden;
        word-wrap: break-word;
        direction: ltr;
        padding: 2px 3px 2px 3px;
      }

      .ritz .waffle .s10 {
        border-bottom: 1px SOLID #000000;
        background-color: #ffffff;
        text-align: left;
        color: #000000;
        font-family: Arial;
        font-size: 10pt;
        vertical-align: middle;
        white-space: nowrap;
        direction: ltr;
        padding: 2px 3px 2px 3px;
      }
    </style>
</head>
<body>
<div class="ritz grid-container" dir="ltr">
    <table class="waffle" cellspacing="0" cellpadding="0">
        <tbody>
            <tr style="height: 91px">
                <td class="s0" colspan="10">
                    <span style="font-size:24pt;">เอกสารประเมินทีม DEV รายสัปดาห์<br></span>
                    <span style="font-size:14pt;">(Document Weekly Developer Report)</span>
                </td>
            </tr>
            <tr style="height: 20px">
                <td class="s1" colspan="10">Introduction</td>
            </tr>
            <tr style="height: 20px">
                <td class="s2" dir="ltr" colspan="10">
                    เอกสารชุดนี้เรียกว่า Document Weekly Developer Report
                    มีวัถตุประสงค์เพื่อ<br> 1. เป็นเอกสารบันทึกข้อมูลผลลัพธืการประเมินทีม Developer รายสัปดาห์<br> 2.
                    ใช้สำหรับแสดงผลการประเมินการทำงานของทีม Developer<br> 3. สำหรับการดำเนินการติดตามผลลัพธ์รายสัปดาห์
                </td>
            </tr>
            <tr style="height: 20px">
                <td class="s4" colspan="2">Author :</td>
                <td class="s2" colspan="8">{{ $report->author ?? 'N/A' }}</td>
            </tr>
            <tr style="height: 20px">
                <td class="s4" colspan="2">Date Start :</td>
                <td class="s5" dir="ltr" colspan="3">{{ $report->date_start ?? 'N/A' }}</td>
                <td class="s4" colspan="2">Date Finish :</td>
                <td class="s5" dir="ltr" colspan="3">{{ $report->date_finish ?? 'N/A' }}</td>
            </tr>
            <tr style="height: 20px">
                <td class="s4" colspan="2">Sprint :</td>
                <td class="s6" dir="ltr" colspan="3">{{ $report->sprint ?? 'N/A' }}</td>
                <td class="s4" colspan="2">Last update :</td>
                <td class="s7" dir="ltr" colspan="3">{{ $report->last_update ?? 'N/A' }}</td>
            </tr>
            <tr style="height: 20px">
                <td class="s9" colspan="10">{{ $report->team_name ?? 'Raizeros Team' }}</td>
            </tr>
            <tr style="height: 20px">
                <td class="s12" colspan="2">PlanPoint</td>
                <td class="s13" colspan="2">{{ $report->plan_point ?? 0 }}</td>
                <td class="s14"></td>
                <td class="s12" colspan="2">ActualPoint</td>
                <td class="s13" colspan="2">{{ $report->actual_point ?? 0 }}</td>
                <td class="s11"></td>
            </tr>
            <tr style="height: 20px">
                <td class="s15" colspan="2">Remain</td>
                <td class="s13" colspan="2">{{ $report->remain ?? 0 }}</td>
                <td class="s14"></td>
                <td class="s16" colspan="2">Percent</td>
                <td class="s13" colspan="2">{{ $report->percent ?? 0 }}%</td>
                <td class="s11"></td>
            </tr>
            <tr style="height: 20px">
                <td class="s17" colspan="2">Point Current Sprint</td>
                <td class="s13" colspan="2">{{ $report->current_sprint_point ?? 0 }}</td>
                <td class="s14"></td>
                <td class="s17" colspan="2">ActualPoint Current Sprint</td>
                <td class="s13" colspan="2">{{ $report->current_sprint_actual_point ?? 0 }}</td>
                <td class="s11"></td>
            </tr>
            <tr style="height: 20px">
                <td class="s12" colspan="2">Points from current sprint</td>
                <td class="s12">Point Personal</td>
                <td class="s12">Test Pass</td>
                <td class="s12">Bug</td>
                <td class="s12">Final Pass point</td>
                <td class="s12">Cancel</td>
                <td class="s19">Sum Final</td>
                <td class="s12">Remark</td>
                <td class="s12">Day Off</td>
            </tr>
            @foreach($report->developers ?? [] as $developer)
            <tr style="height: 20px">
                <td class="s5" colspan="2">{{ $developer->name }}</td>
                <td class="s20" dir="ltr">{{ $developer->point_personal ?? 0 }}</td>
                <td class="s20" dir="ltr">{{ $developer->test_pass ?? 0 }}</td>
                <td class="s20" dir="ltr">{{ $developer->bug ?? 0 }}</td>
                <td class="s13">{{ $developer->final_pass_point ?? 0 }}</td>
                <td class="s20">{{ $developer->cancel ?? 0 }}</td>
                <td class="s20">{{ $developer->sum_final ?? 0 }}</td>
                <td class="s5">{{ $developer->remark ?? '' }}</td>
                <td class="s13" dir="ltr">{{ $developer->day_off ?? 'Not Test' }}</td>
            </tr>
            @endforeach
            <tr style="height: 20px">
                <td class="s21" colspan="2">Sum</td>
                <td class="s20">{{ $report->sum_point_personal ?? 0 }}</td>
                <td class="s20">{{ $report->sum_test_pass ?? 0 }}</td>
                <td class="s20">{{ $report->sum_bug ?? 0 }}</td>
                <td class="s13">{{ $report->sum_final_pass_point ?? 0 }}</td>
                <td class="s20">{{ $report->sum_cancel ?? 0 }}</td>
                <td class="s20">{{ $report->sum_final ?? 0 }}</td>
                <td class="s11" colspan="2"></td>
            </tr>
            <tr style="height: 20px">
                <td class="s22" rowspan="2">Backlog</td>
                <td class="s12">#Sprint</td>
                <td class="s12">Personal</td>
                <td class="s12">Point All</td>
                <td class="s12">Test Pass</td>
                <td class="s12">Bug</td>
                <td class="s12">Cancel</td>
                <td class="s23" rowspan="2">Extra Point</td>
                <td class="s12">Personal</td>
                <td class="s12">Point</td>
            </tr>
            
            <!-- Extra Points Section -->
            @if(isset($report->extra_points) && count($report->extra_points) > 0)
                @foreach($report->extra_points as $extraPoint)
                <tr style="height: 20px">
                    <td class="s5"></td>
                    <td class="s5"></td>
                    <td class="s5"></td>
                    <td class="s5"></td>
                    <td class="s5"></td>
                    <td class="s5"></td>
                    <td class="s5"></td>
                    <td class="s5">{{ $extraPoint->extra_personal }}</td>
                    <td class="s20">{{ $extraPoint->extra_point }}</td>
                </tr>
                @endforeach
            @endif
            
            @foreach($report->backlog ?? [] as $backlog)
            <tr style="height: 20px">
                <td class="s5">{{ $backlog->sprint }}</td>
                <td class="s5">{{ $backlog->personal }}</td>
                <td class="s5">{{ $backlog->point_all }}</td>
                <td class="s5">{{ $backlog->test_pass }}</td>
                <td class="s5">{{ $backlog->bug }}</td>
                <td class="s5">{{ $backlog->cancel }}</td>
                <td class="s5">{{ $backlog->extra_personal }}</td>
                <td class="s5">{{ $backlog->extra_point }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@if(isset($report->logo))
<div id='embed_246369636' class='waffle-embedded-object-overlay' style='width: 89px; height: 89px; display: block;'>
    <img src="{{ asset('tttLogo.png') }}" alt="Logo" style="width: 100%; height: 100%;">
</div>
@endif
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

  function posObj(sheet, id, row, col, x, y) {
    var rtl = false;
    var sheetElement = document.getElementById(sheet);
    if (!sheetElement) {
      sheetElement = document.getElementById(sheet + '-grid-container');
    }
    if (sheetElement) {
      rtl = sheetElement.getAttribute('dir') == 'rtl';
    }
    var r = document.getElementById(sheet + 'R' + row);
    var c = document.getElementById(sheet + 'C' + col);
    if (r && c) {
      var objElement = document.getElementById(id);
      var s = objElement.style;
      var t = y;
      while (r && r != sheetElement) {
        t += r.offsetTop;
        r = r.offsetParent;
      }
      var offsetX = x;
      while (c && c != sheetElement) {
        offsetX += c.offsetLeft;
        c = c.offsetParent;
      }
      if (rtl) {
        offsetX -= objElement.offsetWidth;
      }
      s.left = offsetX + 'px';
      s.top = t + 'px';
      s.display = 'block';
      s.border = '1px solid #000000';
    }
  }

  function posObjs() {
    posObj('0', 'embed_246369636', 0, 0, 15, 0);
  } posObjs();

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
