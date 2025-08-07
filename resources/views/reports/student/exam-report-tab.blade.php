<!-- Student Exam Report Tab -->
<div class="container-fluid">
    <div id="exam_reports_container">
        <!-- Loading message shown initially -->
        <div class="text-center py-5" id="exam_loading">
            <div class="spinner-border text-theme" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <p class="mt-2">{{ __('Loading exam results...') }}</p>
        </div>
        
        <!-- No data message (hidden initially) -->
        <div class="alert alert-info text-center" id="no_exam_data" style="display: none;">
            {{ __('No exam results available for this student.') }}
        </div>
        
        <!-- Exam results will be displayed here -->
        <div id="exam_results_wrapper" style="display: none;">
            <!-- Template for exam results - will be populated via JavaScript -->
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initial load of exam data
    loadExamData();
    
    // Event listener for session year change
    // document.getElementById('exam_session_year').addEventListener('change', loadExamData);
    
    // Function to load exam data based on selected session year
    function loadExamData() {
        // const sessionYearId = document.getElementById('exam_session_year').value;
        const sessionYearId = '{{ $sessionYear->id }}';
        const studentId = '{{ $student->user_id }}'; // Get student ID from the parent view
        
        // Show loading, hide results
        document.getElementById('exam_loading').style.display = 'block';
        document.getElementById('no_exam_data').style.display = 'none';
        document.getElementById('exam_results_wrapper').style.display = 'none';
        
        // AJAX request to get exam data
        fetch(`{{ route('reports.student.exam.report') }}?student_id=${studentId}&session_year_id=${sessionYearId}`)
            .then(response => response.json())
            .then(data => {
                renderExamData(data);
            })
            .catch(error => {
                console.error('Error fetching exam data:', error);
                document.getElementById('exam_loading').style.display = 'none';
                document.getElementById('no_exam_data').style.display = 'block';
                document.getElementById('no_exam_data').textContent = 'Failed to load exam data. Please try again.';
            });
    }
    
    // Function to render exam data
    function renderExamData(data) {
        // Hide loading indicator
        document.getElementById('exam_loading').style.display = 'none';
        
        // Check if we have exam data
        if (data.success && data.exams && data.exams.length > 0) {

            // Create student info section first
            const resultsWrapper = document.getElementById('exam_results_wrapper');
            resultsWrapper.innerHTML = ''; // Clear previous results
            
            // Group exams by type if possible
            const onlineExams = data.exams.filter(exam => exam.exam_type === 'Online Exam');
            const offlineExams = data.exams.filter(exam => !exam.exam_type || exam.exam_type === 'Offline Exam');
            const allExams = [...offlineExams, ...onlineExams]; // Offline first, then online

            // Create a section for each exam
            allExams.forEach(exam => {
                // Check if the exam object has the necessary properties
                if (exam && Object.keys(exam).length > 0) {
                    // console.log(exam);
                    const examCard = createExamCard(exam);
                    resultsWrapper.appendChild(examCard);
                } else {
                    const emptyRow = document.createElement('tr');
                    emptyRow.innerHTML = `
                        <div class="alert alert-info mt-3">
                            {{ __('No exam data available for this student.') }}
                        </div>
                    `;
                    resultsWrapper.appendChild(emptyRow);
                }
            });
            
            // Show the results
            resultsWrapper.style.display = 'block';
            
            // Initialize tooltips
            if (typeof $().tooltip === 'function') {
                $('[data-toggle="tooltip"]').tooltip();
            }
        } else {
            // Show no data message
            document.getElementById('no_exam_data').style.display = 'block';
        }
    }

    // Function to create exam card
    function createExamCard(exam) {
        const card = document.createElement('div');
        card.className = 'card mb-4';
        
        // Apply different styling based on exam type
        const isOnline = exam.exam_type === 'Online Exam';
        if (isOnline) {
            card.classList.add('border-primary');
        } else {
            card.classList.add('border-success');
        }
        
        // Create card header with exam name and type badge
        const cardHeader = document.createElement('div');
        cardHeader.className = 'card-header d-flex justify-content-between align-items-center';
        cardHeader.style.backgroundColor = isOnline ? 'rgba(0, 123, 255, 0.1)' : 'rgba(40, 167, 69, 0.1)';
        
        // Add exam name on left and type badge + PDF button on right if PDF URL exists
        let headerTitle = `<h5 class="mb-0">${exam.name || exam.exam_title || '{{ __("Exam") }}'}</h5>`;
        
        let headerBadges = `
            <div>
                <span class="badge ${isOnline ? 'badge-primary' : 'badge-success'} mr-2">
                    ${isOnline ? '{{ __("Online Exam") }}' : '{{ __("Offline Exam") }}'}
                </span>
        `;
        
        // Add PDF download link if available
        if (exam.summary && exam.summary.pdf_url) {
            headerBadges += `
                <a href="${exam.summary.pdf_url}" target="_blank" class="btn btn-sm btn-outline-info" 
                    data-toggle="tooltip" title="{{ __('Download Result PDF') }}">
                    <i class="fa fa-file-pdf-o mr-1"></i> {{ __('PDF') }}
                </a>
            `;
        }
        
        headerBadges += `</div>`;
        
        cardHeader.innerHTML = headerTitle + headerBadges;
        
        // Create card body with exam details and subjects table
        const cardBody = document.createElement('div');
        cardBody.className = 'card-body p-0';
        
        // Add exam date and description if available
        let examInfo = '';
        if (exam.start_date || exam.description) {
            examInfo = `<div class="p-3 bg-light border-bottom">`;
            
            if (exam.start_date) {
                examInfo += `<p class="mb-1"><strong>{{ __('Date') }}:</strong> ${formatDate(exam.start_date)}</p>`;
            }
            
            if (exam.description) {
                examInfo += `<p class="mb-0"><strong>{{ __('Description') }}:</strong> ${exam.description}</p>`;
            }
            
            examInfo += `</div>`;
        }
        
        // Create subjects table
        const table = document.createElement('table');
        table.className = 'table table-striped table-bordered mb-0';
        
        // Create table header
        const thead = document.createElement('thead');
        thead.innerHTML = `
            <tr class="bg-light">
                <th>{{ __('Subject') }}</th>
                <th class="text-center">{{ __('Max Marks') }}</th>
                <th class="text-center">{{ __('Min Marks') }}</th>
                <th class="text-center">{{ __('Marks Obtained') }}</th>
                <th class="text-center">{{ __('Result') }}</th>
                <th class="text-center">{{ __('Grade') }}</th>
            </tr>
        `;
        
        // Create table body with subjects
        const tbody = document.createElement('tbody');
        
        // For online exams that might not have the same structure
        if (isOnline) {
            // For online exams, we might only have overall result
            const row = document.createElement('tr');
            const isPass = exam.status === 'Pass';
            
            row.innerHTML = `
                <td>
                    <div>
                        <strong>${exam.subject_name || '{{ __("Overall") }}'}</strong>
                        ${exam.subject_code ? ` <small class="text-muted">(${exam.subject_code})</small>` : ''}
                        ${exam.subject_type ? `<span class="badge badge-${getSubjectTypeBadgeClass(exam.subject_type)}">${exam.subject_type}</span>` : ''}
                    </div>
                </td>
                <td class="text-center">${exam.total_marks || exam.exam_total_marks || 0}</td>
                <td class="text-center">${Math.round((exam.total_marks || exam.exam_total_marks || 100) * 0.33)}</td>
                <td class="text-center">${exam.total_obtained_marks || 0}</td>
                <td class="text-center">
                    <span class="badge ${isPass ? 'badge-success' : 'badge-danger'}">
                        ${exam.status || '{{ __("Not Available") }}'}
                    </span>
                </td>
                <td class="text-center">-</td>
            `;
            tbody.appendChild(row);
        } 
        // Standard handling for offline exams or properly structured online exams
        else if (exam.subjects && exam.subjects.length > 0) {
            // We have subjects, show them
            exam.subjects.forEach(subject => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>
                        <div>
                            <strong>${subject.name}</strong>
                            ${subject.code ? ` <small class="text-muted">(${subject.code})</small>` : ''}
                        </div>
                        ${subject.type ? `<span class="badge badge-${getSubjectTypeBadgeClass(subject.type)}">${subject.type}</span>` : ''}
                    </td>
                    <td class="text-center">${subject.max_marks}</td>
                    <td class="text-center">${subject.min_marks}</td>
                    <td class="text-center">${subject.obtained_marks}</td>
                    <td class="text-center">
                        <span class="badge ${subject.is_pass ? 'badge-success' : 'badge-danger'}">
                            ${subject.is_pass ? '{{ __("Pass") }}' : '{{ __("Fail") }}'}
                        </span>
                    </td>
                    <td class="text-center">${subject.grade || '-'}</td>
                `;
                tbody.appendChild(row);
            });
        }
        // If no subjects but we have summary data - create a default row
        else if (exam.summary && exam.summary.max_marks > 0) {
            const row = document.createElement('tr');
            let result = exam.summary.result;
            let isPass = result === 'Pass';
            
            row.innerHTML = `
                <td>{{ __('Overall Result') }}</td>
                <td class="text-center">${exam.summary.max_marks}</td>
                <td class="text-center">${Math.round(exam.summary.max_marks * 0.33)}</td>
                <td class="text-center">${exam.summary.obtained_marks}</td>
                <td class="text-center">
                    <span class="badge ${isPass ? 'badge-success' : 'badge-danger'}">
                        ${result}
                    </span>
                </td>
                <td class="text-center">-</td>
            `;
            tbody.appendChild(row);
        }
        // If no subjects and exam is Not Attempted, show that message
        else if (exam.summary && exam.summary.result === 'Not Attempted') {
            const emptyRow = document.createElement('tr');
            emptyRow.innerHTML = `
                <td colspan="6" class="text-center">{{ __('This exam was not attempted by the student') }}</td>
            `;
            tbody.appendChild(emptyRow);
        } 
        // Otherwise show the standard no data message
        else {
            const emptyRow = document.createElement('tr');
            emptyRow.innerHTML = `
                <td colspan="6" class="text-center">{{ __('No subject data available') }}</td>
            `;
            tbody.appendChild(emptyRow);
        }
        
        // Add summary row if we have subject data and a summary
        if (!isOnline && exam.subjects && exam.subjects.length > 0 && exam.summary) {
            const hasSummarySubject = exam.subjects.some(s => s.name === 'Overall Result' || s.code === 'ALL');
            
            if (!hasSummarySubject) {
                const summaryRow = document.createElement('tr');
                summaryRow.className = 'bg-light font-weight-bold';
                summaryRow.innerHTML = `
                    <td>{{ __('Total') }}</td>
                    <td class="text-center">${exam.summary.max_marks}</td>
                    <td class="text-center">-</td>
                    <td class="text-center">${exam.summary.obtained_marks}</td>
                    <td class="text-center">
                        <span class="badge ${getResultBadgeClass(exam.summary.result)}">
                            ${exam.summary.result}
                        </span>
                    </td>
                    <td class="text-center">-</td>
                `;
                tbody.appendChild(summaryRow);
            }
        }
        
        // Assemble the table
        table.appendChild(thead);
        table.appendChild(tbody);
        
        // Add exam info and table to card body
        cardBody.innerHTML = examInfo;
        cardBody.appendChild(table);
        
        // Create card footer with summary details
        const cardFooter = document.createElement('div');
        cardFooter.className = 'card-footer bg-light';
        
        // For online exams
        if (isOnline) {
            cardFooter.innerHTML = `
                <div class="row">
                    <div class="col-md-4 col-sm-6 mb-2">
                        <strong>{{ __('Percentage') }}:</strong> ${Math.round(exam.percentage || 0)}%
                    </div>
                    <div class="col-md-4 col-sm-6 mb-2">
                        <strong>{{ __('Result') }}:</strong> 
                        <span class="badge ${exam.status === 'Pass' ? 'badge-success' : 'badge-danger'}">
                            ${exam.status || '{{ __("Not Available") }}'}
                        </span>
                    </div>
                    <div class="col-md-4 col-sm-6 mb-2">
                        <strong>{{ __('Date Taken') }}:</strong> ${formatDate(exam.created_at || new Date())}
                    </div>
                </div>
            `;
        }
        // For offline exams with summary data
        else if (exam.summary) {
            cardFooter.innerHTML = `
                <div class="row">
                    <div class="col-md-3 col-sm-6 mb-2">
                        <strong>{{ __('Percentage') }}:</strong> ${Math.round(exam.summary.percentage || 0)}%
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2">
                        <strong>{{ __('Result') }}:</strong> 
                        <span class="badge ${getResultBadgeClass(exam.summary.result)}">
                            ${exam.summary.result}
                        </span>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2">
                        <strong>{{ __('Division') }}:</strong> ${exam.summary.division || '-'}
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2">
                        <strong>{{ __('Rank') }}:</strong> ${exam.summary.rank || 'N/A'}
                    </div>
                </div>
            `;
        } else {
            cardFooter.innerHTML = `
                <div class="text-center text-muted">
                    {{ __('No summary data available') }}
                </div>
            `;
        }
        
        // Assemble the card
        card.appendChild(cardHeader);
        card.appendChild(cardBody);
        card.appendChild(cardFooter);
        
        return card;
    }
    
    // Helper function to get the right badge class based on result
    function getResultBadgeClass(result) {
        switch(result) {
            case 'Pass':
                return 'badge-success';
            case 'Fail':
                return 'badge-danger';
            case 'Not Attempted':
                return 'badge-secondary';
            default:
                return 'badge-info';
        }
    }
    
    // Helper function to format dates
    function formatDate(dateString) {
        if (!dateString) return '-';
        
        try {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-GB', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        } catch (e) {
            return dateString; // Return as is if parsing fails
        }
    }

    // Helper function to get the right badge class based on subject type
    function getSubjectTypeBadgeClass(type) {
        switch(type.toLowerCase()) {
            case 'practical':
                return 'badge-purple';
            case 'theory':
                return 'badge-info';
            case 'optional':
                return 'badge-orange';
            case 'compulsory':
                return 'badge-cyan';
            default:
                return 'badge-secondary';
        }
    }
});
</script>

<style>
    .table th, .table td {
        vertical-align: middle;
    }
    
    .badge-success {
        background-color: #28a745;
    }
    
    .badge-danger {
        background-color: #dc3545;
    }
    
    .badge-info {
        background-color: #17a2b8;
    }
    
    .badge-secondary {
        background-color: #6c757d;
    }
    
    .badge-primary {
        background-color: #007bff;
    }
    
    /* New badge styles for subject types */
    .badge-purple {
        background-color: #6f42c1;
        color: white;
    }
    
    .badge-orange {
        background-color: #fd7e14;
        color: white;
    }
    
    .badge-cyan {
        background-color: #0dcaf0;
        color: #000;
    }
    
    /* Make badges stand out more */
    .badge {
        font-size: 85%;
        padding: 0.35em 0.65em;
        display: inline-block;
        margin-top: 3px;
    }
    
    /* Add some space between subject name and badge */
    td div {
        margin-bottom: 4px;
    }
    
    .student-info-card {
        background-color: #f8f9fa;
        border-left: 4px solid #4B49AC;
    }
    
    .card-header {
        color: #495057;
    }
    
    .border-primary {
        border-color: #007bff !important;
    }
    
    .border-success {
        border-color: #28a745 !important;
    }
    
    @media print {
        .card {
            border: 1px solid #ddd !important;
            margin-bottom: 1.5rem !important;
            break-inside: avoid;
        }
        
        .card-header {
            background-color: #f1f1f1 !important;
            color: #333 !important;
        }
        
        .badge-success {
            background-color: #c3e6cb !important;
            color: #155724 !important;
        }
        
        .badge-danger {
            background-color: #f5c6cb !important;
            color: #721c24 !important;
        }
        
        .badge-info {
            background-color: #bee5eb !important;
            color: #0c5460 !important;
        }
        
        .badge-secondary {
            background-color: #d6d8db !important;
            color: #383d41 !important;
        }
        
        .badge-primary {
            background-color: #b8daff !important;
            color: #004085 !important;
        }
        
        /* Print styles for new badge classes */
        .badge-purple {
            background-color: #e2d9f3 !important;
            color: #5533a5 !important;
        }
        
        .badge-orange {
            background-color: #ffecd5 !important;
            color: #d56908 !important;
        }
        
        .badge-cyan {
            background-color: #d9f9ff !important;
            color: #0999b0 !important;
        }
    }
</style>
