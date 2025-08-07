<!-- Yearly Results Report Tab -->
<div class="container-fluid">
    <!-- Statistics Cards Row -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card h-60 border-primary">
                <div class="card-body text-center py-2">
                    <div class="card-icon mb-1">
                        <i class="fa fa-users fa-2x text-primary"></i>
                    </div>
                    <h3 class="card-title text-primary mb-2" id="total_students">0</h3>
                    <p class="card-text text-muted mb-0">{{ __('Total Students') }}</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card h-60 border-success">
                <div class="card-body text-center py-2">
                    <div class="card-icon mb-1">
                        <i class="fa fa-check-circle fa-2x text-success"></i>
                    </div>
                    <h3 class="card-title text-success mb-2" id="total_pass">0</h3>
                    <p class="card-text text-muted mb-0">{{ __('Students Passed') }}</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card h-60 border-danger">
                <div class="card-body text-center py-2">
                    <div class="card-icon mb-1">
                        <i class="fa fa-times-circle fa-2x text-danger"></i>
                    </div>
                    <h3 class="card-title text-danger mb-2" id="total_fail">0</h3>
                    <p class="card-text text-muted mb-0">{{ __('Students Failed') }}</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card h-60 border-info">
                <div class="card-body text-center py-2">
                    <div class="card-icon mb-1">
                        <i class="fa fa-percentage fa-2x text-info"></i>
                    </div>
                    <h3 class="card-title text-info mb-2" id="total_percentage">0%</h3>
                    <p class="card-text text-muted mb-0">{{ __('Pass Percentage') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Table Section -->
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body px-0">
                    <div class="row">
                        <div class="form-group col-sm-12 col-md-4">
                            <label class="filter-menu">{{ __('Class Section') }} <span class="text-danger">*</span></label>
                            <select name="filter_class_section_id" id="filter_class_section_id" class="form-control">
                                <option value="">{{ __('select_class_section') }}</option>
                                @foreach ($classSections as $classSection)
                                    <option value={{ $classSection->id }}>{{$classSection->full_name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-sm-12 col-md-4">
                            <label class="filter-menu">{{ __('Session Year') }} <span class="text-danger">*</span></label>
                            <select name="filter_session_year_id" id="filter_session_year_id" class="form-control">
                                @foreach ($sessionYears as $sessionYear)
                                    <option value={{ $sessionYear->id }} {{$sessionYear->default==1?"selected":""}}>{{$sessionYear->name}}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- <div class="form-group col-sm-12 col-md-4">
                            <label class="filter-menu">{{ __('Subject') }} <span class="text-danger">*</span></label>
                            <select name="filter_subject_id" id="filter_subject_id" class="form-control">
                                <option value="">{{ __('select_subject') }}</option>
                                @foreach ($subjects as $subject)
                                    <option value={{ $subject->id }}>{{ $subject->name }}  ({{ $subject->type }})</option>
                                @endforeach
                            </select>
                        </div> --}}
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <table aria-describedby="mydesc" class='table' id='table_list'
                                   data-toggle="table" data-url="{{ route('reports.exam.yearly-result-show',[1]) }}"
                                   data-click-to-select="true" data-side-pagination="server"
                                   data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]"
                                   data-search="true" data-toolbar="#toolbar" data-show-columns="true"
                                   data-show-refresh="true" data-trim-on-search="false"
                                   data-mobile-responsive="true" data-sort-name="id"
                                   data-sort-order="desc" data-maintain-selected="true"
                                   data-export-data-type='all' data-export-options='{ "fileName": "exam-list-<?= date(' d-m-y') ?>" ,"ignoreColumn": ["operate"]}'
                                   data-show-export="true" data-detail-formatter="examListFormatter" data-query-params="getYearlyExamResult" data-escape="true">
                                <thead>
                                <tr>
                                    <th scope="col" data-field="id" data-sortable="true" data-visible="false">{{ __('id') }}</th>
                                    <th scope="col" data-field="no">{{ __('no.') }}</th>
                                    <th scope="col" data-field="user.full_name">{{ __('students').' '.__('name') }}</th>
                                    <th scope="col" data-field="total_marks" data-sortable="true">{{ __('total_marks') }}</th>
                                    <th scope="col" data-field="obtained_marks" data-sortable="true">{{ __('obtained_marks') }}</th>
                                    <th scope="col" data-field="percentage" data-sortable="true">{{ __('percentage') }}</th>
                                    <th scope="col" data-field="grade" data-sortable="true">{{ __('grade') }}</th>
                                    <th scope="col" data-field="created_at"  data-sortable="true" data-visible="false">{{ __('created_at') }}</th>
                                    <th scope="col" data-field="updated_at"  data-sortable="true" data-visible="false">{{ __('updated_at') }}</th>
                                    <th scope="col" data-field="operate" data-escape="false" data-events="examResultEvents" data-escape="false">{{ __('action') }}</th>
                                </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    
    .card-icon {
        margin-bottom: 1rem;
    }
    
    .card-title {
        font-size: 2.5rem;
        font-weight: bold;
        margin-bottom: 0.5rem;
    }
    
    .card-text {
        font-size: 0.9rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .border-primary {
        border-left: 4px solid #007bff !important;
    }
    
    .border-success {
        border-left: 4px solid #28a745 !important;
    }
    
    .border-danger {
        border-left: 4px solid #dc3545 !important;
    }
    
    .border-info {
        border-left: 4px solid #17a2b8 !important;
    }
    
    .h-100 {
        height: 100% !important;
    }

    .rank-badge {
        font-size: 0.9rem;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
    }

    .rank-overall {
        background-color: #007bff;
        color: white;
    }

    .rank-subject {
        background-color: #28a745;
        color: white;
    }
    
    @media (max-width: 576px) {
        .card-title {
            font-size: 2rem;
        }
        
        .card-icon i {
            font-size: 2rem !important;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Function to update statistics cards
    function yearlyResultStatistics() {
        const sessionYearId = document.getElementById('filter_session_year_id').value;
        const classSectionId = document.getElementById('filter_class_section_id').value;
        
        // Make AJAX call to get statistics
        fetch(`{{ route('reports.exam.yearly-result-statistics') }}?session_year_id=${sessionYearId}&class_section_id=${classSectionId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the cards with animation
                    animateCounter('total_students', data.total_students);
                    animateCounter('total_pass', data.total_pass);
                    animateCounter('total_fail', data.total_fail);
                    animateCounter('total_percentage', data.pass_percentage, '%');
                }
            })
            .catch(error => {
                console.error('Error fetching statistics:', error);
            });
    }

                                // Function to animate counter
    function animateCounter(elementId, targetValue, suffix = '') {
        const element = document.getElementById(elementId);
        const startValue = 0;
        const duration = 1000; // 1 second
        const increment = targetValue / (duration / 50);
        let currentValue = startValue;
        
        const timer = setInterval(() => {
            currentValue += increment;
            if (currentValue >= targetValue) {
                currentValue = targetValue;
                clearInterval(timer);
            }
            element.textContent = Math.floor(currentValue) + suffix;
        }, 50);
    }
    
    // Event listeners for filter changes
    document.getElementById('filter_session_year_id').addEventListener('change', function() {
        yearlyResultStatistics();
        $('#table_list').bootstrapTable('refresh');
    });
    
    document.getElementById('filter_class_section_id').addEventListener('change', function() {
        yearlyResultStatistics();
        $('#table_list').bootstrapTable('refresh');
    });

    document.getElementById('filter_subject_id').addEventListener('change', function() {
        yearlyResultStatistics();
        $('#table_list').bootstrapTable('refresh');
    });
    
    // Initial load
    yearlyResultStatistics();
    if (document.getElementById('filter_session_year_id').value) {
        yearlyResultStatistics();
    }
    yearlyResultStatistics();
});

// Custom formatter for rank column
function rankFormatter(value, row, index) {
    const subjectId = document.getElementById('filter_subject_id').value;
    
    if (subjectId) {
        return `<span class="badge rank-subject" title="{{ __('Subject-wise rank') }}">${value}</span>`;
    } else {
        return `<span class="badge rank-overall" title="{{ __('Overall rank') }}">${value}</span>`;
    }
}
</script>
