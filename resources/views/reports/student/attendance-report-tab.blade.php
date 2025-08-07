<!-- Student Attendance Report Tab -->
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="form-group">
                <label for="month">{{ __('Select Month') }} <span class="text-danger">*</span></label>
                {!! Form::selectMonth('month', now()->month, ['class' => 'form-control', 'id' => 'attendance_month']) !!}
            </div>
        </div>
    </div>

    <div class="attendance-summary card">
        <div class="card-body px-0 py-0">
            <h5 class="card-title">{{ __('Attendance Summary') }}</h5>
            <div class="row">
                <div class="col-md-2 col-sm-6 mb-3">
                    <div class="attendance-stat bg-primary text-white p-3 rounded">
                        <h3 id="total_days">0</h3>
                        <p class="mb-0">{{ __('Total Days') }}</p>
                    </div>
                </div>
                <div class="col-md-2 col-sm-6 mb-3">
                    <div class="attendance-stat bg-success text-white p-3 rounded">
                        <h3 id="present_count">0</h3>
                        <p class="mb-0">{{ __('Present Days') }}</p>
                    </div>
                </div>
                <div class="col-md-2 col-sm-6 mb-3">
                    <div class="attendance-stat bg-danger text-white p-3 rounded">
                        <h3 id="absent_count">0</h3>
                        <p class="mb-0">{{ __('Absent Days') }}</p>
                    </div>
                </div>
                <div class="col-md-2 col-sm-6 mb-3">
                    <div class="attendance-stat bg-info text-white p-3 rounded">
                        <h3 id="holiday_count">0</h3>
                        <p class="mb-0">{{ __('Holidays') }}</p>
                    </div>
                </div>
                <div class="col-md-2 col-sm-6 mb-3">
                    <div class="attendance-stat bg-secondary text-white p-3 rounded">
                        <h3 id="attendance_percentage">0%</h3>
                        <p class="mb-0">{{ __('Attendance %') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">{{ __('Monthly Attendance') }}</h5>
            <div class="attendance-legends">
                <span class="legend-item mx-2"><i class="fa fa-circle text-success"></i> {{ __('Present (P)') }} </span>
                <span class="legend-item mx-2"><i class="fa fa-circle text-danger"></i> {{ __('Absent (A)') }}</span>
                <span class="legend-item mx-2"><i class="fa fa-circle text-info"></i> {{ __('Holiday (H)') }}</span>
                <span class="legend-item mx-2"><i class="fa fa-circle text-secondary"></i>
                    {{ __('Not Marked (-)') }}</span>
            </div>
        </div>
        <div class="card-body px-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover attendance-table table-sm">
                    <thead>
                        <tr>
                            <th>{{ __('Date') }} / {{ __('Month') }}</th>
                            @for($i = 1; $i <= 31; $i++)
                                <th>{{ $i }}</th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody id="attendance_data">
                        <!-- Attendance data will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Initial load of attendance data
        loadAttendanceData();

        // Event listeners for month change
        document.getElementById('attendance_month').addEventListener('change', loadAttendanceData);

        // Function to load attendance data based on selected month
        function loadAttendanceData() {
            const month = document.getElementById('attendance_month').value;
            const year = new Date().getFullYear(); // Use current year
            const studentId = '{{ $student->user_id ?? $student->id }}'; // Get student ID from the parent view

            // Show loading state
            document.getElementById('attendance_data').innerHTML = '<tr><td colspan="32" class="text-center">Loading...</td></tr>';

            // AJAX request to get attendance data
            fetch(`{{ route('reports.student.attendance.report') }}?month=${month}&attendance_year=${year}&student_id=${studentId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    renderAttendanceData(data, parseInt(month), parseInt(year));
                })
                .catch(error => {
                    console.error('Error fetching attendance data:', error);
                    document.getElementById('attendance_data').innerHTML =
                        '<tr><td colspan="32" class="text-center text-danger">Failed to load attendance data</td></tr>';
                });
        }

        // Function to render attendance data
        function renderAttendanceData(data, month, year) {
            const tbody = document.getElementById('attendance_data');
            const daysInMonth = new Date(year, month, 0).getDate();

            if (data.success && data.attendance && data.attendance.length >= 0) {
                // Clear the table
                tbody.innerHTML = '';

                // Create row for month display
                const row = document.createElement('tr');

                // Add month name cell
                const monthCell = document.createElement('td');
                monthCell.className = 'font-weight-bold text-center';
                monthCell.innerText = new Date(year, month - 1, 1).toLocaleString('default', { month: 'long' }) + ' ';
                row.appendChild(monthCell);

                // Add attendance status cells
                for (let i = 1; i <= 31; i++) {
                    const cell = document.createElement('td');
                    cell.classList.add('text-center', 'p-1');
                    
                    if (i <= daysInMonth) {
                        // Format date to match API date format (YYYY-MM-DD)
                        const currentDate = `${year}-${month.toString().padStart(2, '0')}-${i.toString().padStart(2, '0')}`;

                        // Check if this day is a holiday
                        const holiday = data.holiday && data.holiday.length > 0 ?
                            data.holiday.find(h => h.date === currentDate) : null;

                        // Check if we have attendance data for this day
                        const attendance = data.attendance && data.attendance.length > 0
                            ? data.attendance.find(a => {
                                return a.get_date_original === currentDate;
                            })
                            : null;

                        if (holiday) {
                            // Holiday
                            cell.innerHTML = '<span class="badge badge-info text-white" data-toggle="tooltip" title="' +
                                (holiday.title || 'Holiday') + '">H</span>';
                                cell.classList.add('bg-info-light');
                        } else if (attendance) {
                            if (attendance.type === 1 || attendance.type === '1') {
                                // Present
                                cell.innerHTML = '<span class="badge badge-success text-white">P</span>';
                                cell.classList.add('bg-success-light');
                            } else if (attendance.type === 0 || attendance.type === '0') {
                                // Absent
                                cell.innerHTML = '<span class="badge badge-danger text-white">A</span>';
                                cell.classList.add('bg-danger-light');
                            } else if (attendance.type === 3 || attendance.type === '3') {
                                // Holiday (marked in attendance)
                                cell.innerHTML = '<span class="badge badge-info text-white">H</span>';
                                cell.classList.add('bg-info-light');
                            } else {
                                // Unknown status
                                cell.innerHTML = '<span class="text-muted">-</span>';
                            }
                        } else {
                            // No attendance data for this day
                            cell.innerHTML = '<span class="text-muted">-</span>';
                        }
                    } else {
                        // Outside the month's days
                        cell.classList.add('bg-light');
                        cell.innerHTML = '';
                    }

                    row.appendChild(cell);
                }

                tbody.appendChild(row);

                // Update summary stats from the API response
                if (data.summary) {
                    document.getElementById('total_days').innerText = data.summary.total_days || 0;
                    document.getElementById('present_count').innerText = data.summary.present_count || 0;
                    document.getElementById('absent_count').innerText = data.summary.absent_count || 0;
                    document.getElementById('holiday_count').innerText = data.summary.holiday_count || 0;
                    document.getElementById('attendance_percentage').innerText = `${data.summary.attendance_percentage || 0}%`;
                }

                // Initialize tooltips for holidays
                if (typeof $ !== 'undefined') {
                    $('[data-toggle="tooltip"]').tooltip();
                }
            } else {
                // No data available or error
                tbody.innerHTML = '<tr><td colspan="32" class="text-center">No attendance records found for this month</td></tr>';

                // Reset summary stats
                document.getElementById('total_days').innerText = '0';
                document.getElementById('present_count').innerText = '0';
                document.getElementById('absent_count').innerText = '0';
                document.getElementById('holiday_count').innerText = '0';
                document.getElementById('attendance_percentage').innerText = '0%';
            }
        }
    });
</script>

<style>
    .attendance-stat {
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .attendance-legends {
        font-size: 0.8rem;
    }

    .legend-item {
        display: inline-flex;
        align-items: center;
        margin-left: 12px;
    }

    .legend-item i {
        font-size: 10px;
        margin-right: 5px;
    }

    .attendance-table th {
        text-align: center;
        font-size: 0.9rem;
        padding: 8px 4px;
    }

    .attendance-table td {
        padding: 4px;
        vertical-align: middle;
    }

    .bg-success-light {
        background-color: rgba(40, 167, 69, 0.1);
    }

    .bg-danger-light {
        background-color: rgba(220, 53, 69, 0.1);
    }

    .bg-info-light {
        background-color: rgba(23, 162, 184, 0.1);
    }

    .badge {
        font-size: 0.75em;
        padding: 0.25em 0.4em;
    }
</style>