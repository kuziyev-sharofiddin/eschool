<!-- Subject Wise Results Report Tab -->
<div class="container-fluid">
    <!-- Statistics Cards Row -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card h-60 border-primary">
                <div class="card-body text-center py-2">
                    <div class="card-icon mb-1">
                        <i class="fa fa-book fa-3x text-primary"></i>
                    </div>
                    <h3 class="card-title text-primary mb-2" id="subject_wise_total_subjects">0</h3>
                    <p class="card-text text-muted mb-0">{{ __('Total Subjects') }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card h-60 border-success">
                <div class="card-body text-center py-2">
                    <div class="card-icon mb-3">
                        <i class="fa fa-check-circle fa-3x text-success"></i>
                    </div>
                    <h3 class="card-title text-success mb-2" id="subject_wise_subjects_passed">0</h3>
                    <p class="card-text text-muted mb-0">{{ __('Subjects Passed') }}</p>
                    <small class="text-muted">{{ __('Subjects with ≥33% pass rate') }}</small>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card h-60 border-danger">
                <div class="card-body text-center py-2">
                    <div class="card-icon mb-3">
                        <i class="fa fa-times-circle fa-3x text-danger"></i>
                    </div>
                    <h3 class="card-title text-danger mb-2" id="subject_wise_subjects_failed">0</h3>
                    <p class="card-text text-muted mb-0">{{ __('Subjects Failed') }}</p>
                    <small class="text-muted">{{ __('Subjects with <33% pass rate') }}</small>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card h-60 border-info">
                <div class="card-body text-center py-2">
                    <div class="card-icon mb-3">
                        <i class="fa fa-percentage fa-3x text-info"></i>
                    </div>
                    <h3 class="card-title text-info mb-2" id="subject_wise_pass_percentage">0%</h3>
                    <p class="card-text text-muted mb-0">{{ __('Overall Pass Rate') }}</p>
                    <small class="text-muted">{{ __('Percentage of passed subjects') }}</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body px-0">
                    <div class="row mb-4">
                        <div class="form-group col-12 col-sm-12 col-md-4 col-lg-4">
                            <label for="filter_session_year_id" class="filter-menu">{{__("session_year")}}</label>
                            <select name="filter_session_year_id" id="filter_subject_wise_session_year_id" class="form-control">
                                @foreach ($sessionYears as $sessionYear)
                                    <option value="{{ $sessionYear->id }}" {{$sessionYear->default==1 ? "selected" : ""}}>{{ $sessionYear->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- exam list --}}
                        <div class="col-12 col-sm-12 col-md-4 col-lg-4">
                            <label for="filter_exam_id" class="filter-menu">{{__("Exam")}}</label>
                            <select name="filter_exam_id" id="filter_subject_wise_exam_id" class="form-control">
                                <option value="">{{ __('select_exam') }}</option>
                                @foreach ($exams as $exam)
                                    <option value="{{ $exam->id }}">{{ $exam->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- class section list --}}
                        <div class="col-12 col-sm-12 col-md-4 col-lg-4">
                            <label for="filter_class_section_id" class="filter-menu">{{__("Class Section")}}</label>
                            <select name="filter_class_section_id" id="filter_subject_wise_class_section_id" class="form-control">
                                <option value="">{{ __('select_class_section') }}</option>
                                <option value="data-not-found" style="display: none;">-- {{ __('no_data_found') }} --</option>
                                @foreach ($classSections as $classSection)
                                    <option value="{{ $classSection->id }}" data-class-id="{{ $classSection->class_id }}">{{ $classSection->full_name }}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col-12">
                            <table aria-describedby="mydesc" class='table' id='subject_wise_table_list' data-toggle="table"
                                data-url="{{ route('reports.exam.subject-wise-result-show', [1]) }}"
                                data-click-to-select="true" data-side-pagination="server" data-pagination="true"
                                data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-toolbar="#toolbar"
                                data-show-columns="true" data-show-refresh="true" data-trim-on-search="false"
                                data-mobile-responsive="true" data-sort-name="id" data-sort-order="desc"
                                data-maintain-selected="true" data-export-data-type='all'
                                data-export-options='{ "fileName": "exam-list-<?= date(' d-m-y') ?>" ,"ignoreColumn":
                                ["operate"]}'
                                data-show-export="true" data-detail-formatter="examListFormatter"
                                data-query-params="getSubjectWiseExamResult" data-escape="true">
                                <thead>
                                    <tr>
                                        <th scope="col" data-field="id" data-sortable="true" data-visible="false">
                                            {{ __('id') }}</th>
                                        <th scope="col" data-field="no">{{ __('no.') }}</th>
                                        <th scope="col" data-field="user.full_name">
                                            {{ __('students') . ' ' . __('name') }}</th>
                                        <th scope="col" data-field="total_marks" data-sortable="true">
                                            {{ __('total_marks') }}</th>
                                        <th scope="col" data-field="obtained_marks" data-sortable="true">
                                            {{ __('obtained_marks') }}</th>
                                        <th scope="col" data-field="percentage" data-sortable="true">
                                            {{ __('percentage') }}</th>
                                        <th scope="col" data-field="grade" data-sortable="true">
                                            {{ __('grade') }}</th>
                                        <th scope="col" data-field="created_at" 
                                            data-sortable="true" data-visible="false">{{ __('created_at') }}</th>
                                        <th scope="col" data-field="updated_at" 
                                            data-sortable="true" data-visible="false">{{ __('updated_at') }}</th>
                                        @can('exam-result-edit')
                                            <th scope="col" data-field="operate" data-escape="false"
                                                data-events="examResultEvents" data-escape="false">{{ __('action') }}
                                            </th>
                                        @endcan
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Function to reset statistics
    function resetStatistics() {
        document.getElementById('subject_wise_total_subjects').textContent = '0';
        document.getElementById('subject_wise_subjects_passed').textContent = '0';
        document.getElementById('subject_wise_subjects_failed').textContent = '0';
        document.getElementById('subject_wise_pass_percentage').textContent = '0%';
    }

    // Function to update statistics
    function updateStatistics() {
        const sessionYearId = document.getElementById('filter_subject_wise_session_year_id').value;
        const classSectionId = document.getElementById('filter_subject_wise_class_section_id').value;
        
        if (!sessionYearId) {
            resetStatistics();
            return;
        }

        // Make AJAX call to get statistics
        fetch(`{{ route('reports.exam.subject-wise-result-statistics') }}?session_year_id=${sessionYearId}&class_section_id=${classSectionId || ''}&subject_id=${''}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the cards with animation
                    animateCounter('subject_wise_total_subjects', data.total_subjects);
                    animateCounter('subject_wise_subjects_passed', data.subjects_passed);
                    animateCounter('subject_wise_subjects_failed', data.subjects_failed);
                    animateCounter('subject_wise_pass_percentage', data.pass_percentage, '%');
                }
            })
            .catch(error => {
                console.error('Error fetching statistics:', error);
                resetStatistics();
            });
    }

    // Function to refresh table data
    function refreshTableData() {
        if ($.fn.bootstrapTable) {
            $('#subject_wise_table_list').bootstrapTable('refresh');
        }
    }

    $(document).on('change', '#filter_subject_wise_session_year_id, #filter_subject_wise_class_section_id, #filter_subject_wise_exam_id', function() {
        updateStatistics();
        refreshTableData();
    });


    // Function to handle tab change
    function handleTabChange() {
        // Reset filters
        resetFilters();
        
        // Reset statistics
        resetStatistics();
        
        // Refresh table data
        refreshTableData();
        
        // Update statistics with default values
        updateStatistics();
    }

    // Add tab change event listeners
    const tabLinks = document.querySelectorAll('a[data-toggle="tab"]');
    tabLinks.forEach(tab => {
        tab.addEventListener('shown.bs.tab', function(event) {
            if (event.target.getAttribute('href') === '#subject_wise_results') {
                handleTabChange();
            }
        });
    });

    // Event listeners for filter changes
    document.getElementById('filter_subject_wise_session_year_id').addEventListener('change', function() {
        updateStatistics();
        refreshTableData();
    });
    
    document.getElementById('filter_subject_wise_class_section_id').addEventListener('change', function() {
        updateStatistics();
        refreshTableData();
    });

    
    // Function to animate counter with proper number formatting
    function animateCounter(elementId, targetValue, suffix = '') {
        const element = document.getElementById(elementId);
        const startValue = parseInt(element.textContent) || 0;
        const duration = 1000; // 1 second
        const increment = (targetValue - startValue) / (duration / 50);
        let currentValue = startValue;
        
        const timer = setInterval(() => {
            currentValue += increment;
            if ((increment >= 0 && currentValue >= targetValue) || 
                (increment < 0 && currentValue <= targetValue)) {
                currentValue = targetValue;
                clearInterval(timer);
            }
            // Format the number as integer
            const formattedValue = Math.round(currentValue);
            element.textContent = formattedValue + suffix;
        }, 50);
    }

    // Function to reset filters
    function resetFilters() {
        // Reset session year to default
        const sessionYearSelect = document.getElementById('filter_subject_wise_session_year_id');
        const defaultSessionYear = Array.from(sessionYearSelect.options).find(option => option.getAttribute('selected'));
        if (defaultSessionYear) {
            sessionYearSelect.value = defaultSessionYear.value;
        }

        // Reset class section
        document.getElementById('filter_subject_wise_class_section_id').value = '';
    }

    // Event listeners for filter changes
    document.getElementById('filter_subject_wise_session_year_id').addEventListener('change', function() {
        updateStatistics();
        $('#subject_wise_table_list').bootstrapTable('refresh');
    });
    
    document.getElementById('filter_subject_wise_class_section_id').addEventListener('change', function() {
        updateStatistics();
        $('#subject_wise_table_list').bootstrapTable('refresh');
    });
    
    // Initial load
    updateStatistics();
    if (document.getElementById('filter_subject_wise_session_year_id').value) {
        updateStatistics();
    }
    updateStatistics();
});
</script>