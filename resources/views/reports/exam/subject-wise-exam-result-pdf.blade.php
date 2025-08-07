<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $settings['school_name'] ?? 'School Name' }} {{ $result->session_year->name ?? 'Session Year' }} -
        {{ __('Result') }} </title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @media print {
            @page {
                size: A4;
                margin: 0;
            }

            body {
                margin: 0;
                padding: 0;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .report-card {
                width: 100%;
                max-width: none;
                margin: 0;
                box-shadow: none;
                border-radius: 0;
            }

            .header {
                padding: 20px;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .student-profile {
                padding: 15px 20px;
            }

            .content-section {
                padding: 15px 20px;
            }

            .grades-table th,
            .co-scholastic-table th,
            .grade-system-table th {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .footer {
                position: fixed;
                bottom: 0;
                width: 100%;
                background: white;
                padding: 10px 20px;
            }

            /* Hide elements not needed in print */
            .no-print {
                display: none !important;
            }

            /* Ensure tables don't break across pages */
            table {
                page-break-inside: avoid;
            }

            /* Ensure sections don't break across pages */
            .section-title {
                page-break-after: avoid;
            }

            /* Adjust font sizes for print */
            body {
                font-size: 12pt;
            }

            .student-name {
                font-size: 18pt;
            }

            .section-title {
                font-size: 14pt;
            }

            .info-item {
                font-size: 10pt;
            }

            /* Ensure colors print properly */
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }

        :root {
            --primary: {{ $settings['theme_color'] ?? '#22577a' }};
            --secondary: {{ $settings['secondary_color'] ?? '#38a3a5' }};
            --accent: {{ $settings['primary_color'] ?? '#22577a' }};
            --light: {{ $settings['primary_background_color'] ?? '#f2f5f7' }};
            --dark: {{ $settings['text_secondary_color'] ?? '#2d2c2fb5' }};
            --success: #4CAF50;
            --info: #2196F3;
            --warning: #FF9800;
            --danger: #f72585;
            --hover: {{ $settings['primary_hover_color'] ?? '#143449' }};
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--light);
            color: var(--dark);
            margin: 0;
            padding: 0;
        }

        .report-card {
            max-width: 1000px;
            margin: 0px auto;
            background-color: white;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            overflow: hidden;
            position: relative;
        }

        .header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 25px 20px;
            text-align: center;
            position: relative;
        }

        .header h2 {
            font-weight: 700;
            margin-bottom: 0;
            letter-spacing: 1px;
        }

        .student-profile {
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
        }

        .student-details {
            flex: 1;
        }

        .student-name {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--dark);
        }

        .student-info {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .info-item {
            background-color: var(--light);
            padding: 8px 15px;
            border-radius: 6px;
            font-size: 14px;
        }

        .percentage-circle {
            width: 120px;
            height: 120px;
            background-color: white;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .percentage-value {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 2px;
        }

        .grade-value {
            font-size: 18px;
            font-weight: 600;
            color: var(--secondary);
            margin-bottom: 2px;
        }

        .percentage-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--dark);
        }

        .content-section {
            padding: 20px;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--primary);
            display: flex;
            align-items: center;
        }

        .section-title i {
            margin-right: 10px;
        }

        .grades-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }

        .grades-table th {
            background-color: var(--primary);
            color: white;
            font-weight: 500;
            text-align: center;
            padding: 12px;
        }

        .grades-table td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #eee;
        }

        .grades-table td:first-child {
            text-align: left;
            font-weight: 500;
        }

        .grades-table tr:last-child td {
            border-bottom: none;
        }

        .grades-table tr:nth-child(even) {
            background-color: var(--light);
        }

        .total-row {
            background-color: var(--light) !important;
            font-weight: 600;
        }

        .marks-obtained {
            font-weight: 600;
            color: var(--primary);
        }

        .two-columns {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .column {
            flex: 1;
        }

        .co-scholastic-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .co-scholastic-table th {
            background-color: var(--secondary);
            color: white;
            font-weight: 500;
            text-align: left;
            padding: 12px;
        }

        .co-scholastic-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        .co-scholastic-table td:last-child {
            text-align: center;
            font-weight: 600;
        }

        .co-scholastic-table tr:last-child td {
            border-bottom: none;
        }

        .grade-A-plus {
            color: var(--success);
        }

        .grade-A {
            color: var(--info);
        }

        .grade-B {
            color: var(--warning);
        }

        .attendance-box {
            background-color: var(--light);
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            margin-top: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .attendance-value {
            font-size: 24px;
            font-weight: 600;
            color: var(--primary);
        }

        .reflection-box {
            background-color: var(--light);
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .grade-system-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .grade-system-table th {
            background-color: var(--secondary);
            color: white;
            font-weight: 500;
            text-align: center;
            padding: 8px;
            font-size: 14px;
        }

        .grade-system-table td {
            padding: 8px;
            text-align: center;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }

        .grade-system-table tr:last-child td {
            border-bottom: none;
        }

        .signature-area {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .signature-box {
            text-align: center;
        }

        .signature-line {
            width: 200px;
            height: 1px;
            background-color: var(--dark);
            margin: 8px auto;
        }

        .signature-name {
            font-weight: 600;
            margin-bottom: 0;
            color: var(--dark);
        }

        .signature-title {
            font-size: 12px;
            color: var(--dark);
        }

        .footer {
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #eee;
            font-size: 14px;
            color: var(--dark);
        }

        .issued-date {
            font-weight: 500;
        }

        .page-number {
            color: var(--dark);
        }

        @media (max-width: 768px) {
            .student-profile {
                flex-direction: column;
                text-align: center;
            }

            .percentage-circle {
                margin-top: 20px;
            }

            .two-columns {
                flex-direction: column;
            }

            .signature-area {
                flex-direction: column;
                gap: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="report-card">
        <div class="header">
            <div class="row align-items-center">
                <div class="col-md-2">
                    <div class="school-logo px-2 py-4 bg-white border border rounded-3">
                        @if (
                            (isset($settings['horizontal_logo']) && $settings['horizontal_logo']) ||
                                (isset($settings['vertical_logo']) && $settings['vertical_logo']))
                            <img src="{{ $settings['horizontal_logo'] ?? asset('assets/landing_page_images/Logo1.svg') }}"
                                alt="">
                        @else
                            <img src="{{ asset('assets/vertical-logo.svg') }}" alt=""
                                style="max-height: 80px;">
                        @endif
                    </div>
                </div>
                <div class="col-md-8 d-flex align-items-center justify-content-center">
                    <h2>{{ $settings['school_name'] }} {{ $result->session_year->name }}</h2>
                </div>
            </div>

        </div>

        <div class="student-profile">
            <div class="student-details">
                <div class="student-name">{{ $result->user->first_name }} {{ $result->user->last_name }}</div>
                <div class="student-info">
                    <div class="info-item"><i class="fas fa-user-graduate"></i> Class:
                        {{ $result->user->student->class_section->class->name }} -
                        {{ $result->user->student->class_section->section->name }}</div>
                    <div class="info-item"><i class="fas fa-id-card"></i> Roll No:
                        {{ $result->user->student->roll_number }}</div>
                    <div class="info-item"><i class="fas fa-hashtag"></i> Gr. No:
                        {{ $result->user->student->admission_no }}</div>
                    <div class="info-item"><i class="fas fa-calendar"></i> DOB:
                        {{ date('d-M-Y', strtotime($result->user->student->dob)) }}</div>
                </div>
            </div>
            <div class="percentage-circle">
                <div class="percentage-value">{{ number_format($result->percentage, 2) }}%</div>
                @php
                    $overallGrade = '';
                    foreach ($grades as $grade) {
                        if (
                            $result->percentage >= $grade->starting_range &&
                            $result->percentage <= $grade->ending_range
                        ) {
                            $overallGrade = $grade->grade;
                            break;
                        }
                    }
                @endphp
                <div class="grade-value">{{ $overallGrade }}</div>
                <div class="percentage-label">Overall</div>
            </div>
        </div>

        <div class="content-section">
            <div class="section-title">
                <i class="fas fa-book"></i> Scholastic Achievements
            </div>

            <table class="grades-table">
                <thead>
                    <tr>
                        <th>SUBJECT</th>
                        @foreach ($exams as $exam)
                            <th>{{ $exam->name }}<br><small>( {{ $exam->timetable->first()->total_marks ?? 0 }}
                                    )</small></th>
                        @endforeach
                        <th>TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $subject_marks = [];
                        $total_marks = 0;
                    @endphp
                    @foreach ($result->user->exam_marks as $mark)
                        @php
                            $subject = $mark->class_subject->subject->name . ' (' . $mark->class_subject->subject->type . ')';
                            if (!isset($subject_marks[$subject])) {
                                $subject_marks[$subject] = [];
                            }
                            $subject_marks[$subject][$mark->timetable->exam_id] = $mark->obtained_marks;
                        @endphp
                    @endforeach

                    @foreach ($subject_marks as $subject => $marks)
                        <tr>
                            <td>{{ $subject }}</td>
                            @php
                                $subject_total = 0;
                            @endphp
                            @foreach ($exams as $exam)
                                <td>{{ $marks[$exam->id] ?? '-' }}</td>
                                @php
                                    $subject_total += $marks[$exam->id] ?? 0;
                                @endphp
                            @endforeach
                            <td class="marks-obtained">{{ $subject_total }}</td>
                        </tr>
                    @endforeach
                    <tr class="total-row">
                        <td>Total Marks</td>
                        @php
                            $exam_totals = [];
                        @endphp
                        @foreach ($exams as $exam)
                            @php
                                $exam_total = 0;
                                foreach ($subject_marks as $marks) {
                                    $exam_total += $marks[$exam->id] ?? 0;
                                }
                                $exam_totals[] = $exam_total;
                            @endphp
                            <td>{{ $exam_total }}</td>
                        @endforeach
                        <td class="marks-obtained">{{ array_sum($exam_totals) }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="two-columns">
                <div class="column">
                    <div class="section-title">
                        <i class="fas fa-palette"></i> Co-Scholastic Activities
                    </div>

                    <table class="co-scholastic-table">
                        <thead>
                            <tr>
                                <th>ACTIVITY</th>
                                <th>GRADE</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($result->co_scholastic_marks ?? [] as $activity)
                                <tr>
                                    <td>{{ $activity->activity_name }}</td>
                                    <td class="grade-{{ $activity->grade }}">{{ $activity->grade }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="attendance-box">
                        <div class="attendance-title">ATTENDANCE</div>
                        <div class="attendance-value">{{ $studentAttendanceCount }} / {{ $attendanceTotal }} days
                        </div>
                        <div class="attendance-percentage">
                            {{ number_format(($studentAttendanceCount / ($attendanceTotal ?: 1)) * 100, 1) }}%
                        </div>
                    </div>

                </div>

                <div class="column">
                    <div class="section-title">
                        <i class="fas fa-chart-bar"></i> Grading System
                    </div>

                    <table class="grade-system-table">
                        <thead>
                            <tr>
                                <th>MARKS RANGE</th>
                                <th>GRADE</th>
                                <th>DESCRIPTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($grades as $grade)
                                <tr>
                                    <td>{{ $grade->starting_range }} - {{ $grade->ending_range }}</td>
                                    <td class="grade-{{ $grade->grade }}">{{ $grade->grade }}</td>
                                    <td>{{ $grade->description }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-center" style="margin-top: 20px;">
                        <div id="performanceChart" style="height: 200px; width: 100%;"></div>
                    </div>
                </div>
            </div>

            <div class="signature-area">
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div class="signature-name">{{ $settings['principal_name'] ?? 'Principal' }}</div>
                    <div class="signature-title">PRINCIPAL</div>
                </div>

                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div class="signature-name">{{ $result->class_teacher_name ?? 'Class Teacher' }}</div>
                    <div class="signature-title">HOMEROOM TEACHER</div>
                </div>
            </div>
        </div>

        <div class="footer d-flex justify-content-between align-items-center">
            <div class="issued-date">Issued Date: {{ date('d-M-Y') }}</div>
            <div class="flex-grow-1 text-center">&copy; {{ __('Driven by') }} {{ $settings['school_name'] }}</div>
            <div class="page-number">Page: 1 of 1</div>
        </div>

    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Wait for fonts and CSS to load
            setTimeout(function() {
                // print the result
                window.print();
            }, 1000); // 1 second delay
        });
    </script>
</body>

</html>
