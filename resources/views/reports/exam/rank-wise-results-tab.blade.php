<!-- Rank Wise Results Report Tab -->
<div class="container-fluid">
    <!-- Statistics Cards Row -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card h-100 border-primary">
                <div class="card-body text-center">
                    <div class="card-icon mb-3">
                        <i class="fa fa-users fa-3x text-primary"></i>
                    </div>
                    <h3 class="card-title text-primary mb-2" id="rank_wise_total_students">0</h3>
                    <p class="card-text text-muted mb-0">{{ __('Total Students') }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card h-100 border-success">
                <div class="card-body text-center">
                    <div class="card-icon mb-3">
                        <i class="fa fa-check-circle fa-3x text-success"></i>
                    </div>
                    <h3 class="card-title text-success mb-2" id="rank_wise_total_pass">0</h3>
                    <p class="card-text text-muted mb-0">{{ __('Students Passed') }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card h-100 border-danger">
                <div class="card-body text-center">
                    <div class="card-icon mb-3">
                        <i class="fa fa-times-circle fa-3x text-danger"></i>
                    </div>
                    <h3 class="card-title text-danger mb-2" id="rank_wise_total_fail">0</h3>
                    <p class="card-text text-muted mb-0">{{ __('Students Failed') }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card h-100 border-info">
                <div class="card-body text-center">
                    <div class="card-icon mb-3">
                        <i class="fa fa-percentage fa-3x text-info"></i>
                    </div>
                    <h3 class="card-title text-info mb-2" id="rank_wise_total_percentage">0%</h3>
                    <p class="card-text text-muted mb-0">{{ __('Pass Percentage') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body px-0">
                    <div class="row" id="">
                        <div class="form-group col-12 col-sm-12 col-md-4 col-lg-4">
                            <label for="filter_session_year_id" class="filter-menu">{{__("session_year")}}</label>
                            <select name="filter_session_year_id" id="filter_rank_wise_session_year_id" class="form-control">
                                @foreach ($sessionYears as $sessionYear)
                                    <option value="{{ $sessionYear->id }}" {{$sessionYear->default==1 ? "selected" : ""}}>{{ $sessionYear->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-12 col-sm-12 col-md-4 col-lg-4">
                            <label for="filter_class_section_id" class="filter-menu">{{__("Class Section")}}</label>
                            <select name="filter_class_section_id" id="filter_rank_wise_class_section_id" class="form-control">
                                <option value="">{{ __('select_class_section') }}</option>
                                <option value="data-not-found" style="display: none;">-- {{ __('no_data_found') }} --</option>
                                @foreach ($classSections as $classSection)
                                    <option value="{{ $classSection->id }}" data-class-id="{{ $classSection->class_id }}">{{ $classSection->full_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-sm-12 col-md-4 col-lg-4">
                            <label for="filter_subject_id" class="filter-menu">{{__("Subject")}}</label>
                            <select name="filter_subject_id" id="filter_rank_wise_subject_id" class="form-control">
                                <option value="">{{ __('select_subject') }}</option>
                                <option value="data-not-found" style="display: none;">-- {{ __('no_data_found') }} --</option>
                                @foreach ($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->name }} ({{ $subject->type }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <table aria-describedby="mydesc" class='table' id='reank_wise_table_list'
                                   data-toggle="table" data-url="{{ route('reports.exam.rank-wise-result-show',[1]) }}"
                                   data-click-to-select="true" data-side-pagination="server"
                                   data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]"
                                   data-search="true" data-toolbar="#toolbar" data-show-columns="true"
                                   data-show-refresh="true" data-trim-on-search="false"
                                   data-mobile-responsive="true" data-sort-name="percentage"
                                   data-sort-order="desc" data-maintain-selected="true"
                                   data-export-data-type='all' data-export-options='{ "fileName": "exam-list-<?= date(' d-m-y') ?>" ,"ignoreColumn": ["operate"]}'
                                   data-show-export="true" data-detail-formatter="examListFormatter" data-query-params="getRankWiseExamResult" data-escape="true">
                                <thead>
                                <tr>
                                    <th scope="col" data-field="id" data-sortable="true" data-visible="false">{{ __('id') }}</th>
                                    <th scope="col" data-field="no">{{ __('no.') }}</th>
                                    <th scope="col" data-field="user.full_name">{{ __('students').' '.__('name') }}</th>
                                    <th scope="col" data-field="total_marks" data-sortable="true">{{ __('total_marks') }}</th>
                                    <th scope="col" data-field="obtained_marks" data-sortable="true">{{ __('obtained_marks') }}</th>
                                    <th scope="col" data-field="percentage" data-sortable="true">{{ __('percentage') }}</th>
                                    <th scope="col" data-field="grade" data-sortable="true">{{ __('grade') }}</th>
                                    <th scope="col" data-field="rank" data-sortable="true">{{ __('rank') }}</th>
                                    <th scope="col" data-field="created_at"  data-sortable="true" data-visible="false">{{ __('created_at') }}</th>
                                    <th scope="col" data-field="updated_at"  data-sortable="true" data-visible="false">{{ __('updated_at') }}</th>
                                    @can('exam-result-edit')
                                        <th scope="col" data-field="operate" data-escape="false" data-events="examResultEvents" data-escape="false">{{ __('action') }}</th>
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
    // Function to reset filters
    function resetFilters() {
        // Reset session year to default
        const sessionYearSelect = document.getElementById('filter_rank_wise_session_year_id');
        const defaultSessionYear = Array.from(sessionYearSelect.options).find(option => option.getAttribute('selected'));
        if (defaultSessionYear) {
            sessionYearSelect.value = defaultSessionYear.value;
        }

        // Reset class section
        document.getElementById('filter_rank_wise_class_section_id').value = '';

        // Reset subject
        document.getElementById('filter_rank_wise_subject_id').value = '';
    }

    // Function to reset statistics
    function resetStatistics() {
        document.getElementById('rank_wise_total_students').textContent = '0';
        document.getElementById('rank_wise_total_pass').textContent = '0';
        document.getElementById('rank_wise_total_fail').textContent = '0';
        document.getElementById('rank_wise_total_percentage').textContent = '0%';
    }

    // Function to update statistics
    function updateStatistics() {
        const sessionYearId = document.getElementById('filter_rank_wise_session_year_id').value;
        const classSectionId = document.getElementById('filter_rank_wise_class_section_id').value;
        const subjectId = document.getElementById('filter_rank_wise_subject_id').value;
        
        if (!sessionYearId) {
            resetStatistics();
            return;
        }

        // Make AJAX call to get statistics
        fetch(`{{ route('reports.exam.rank-wise-result-statistics') }}?session_year_id=${sessionYearId}&class_section_id=${classSectionId || ''}&subject_id=${subjectId || ''}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Ensure percentage doesn't exceed 100%
                    const passPercentage = Math.min(100, data.pass_percentage);
                    
                    // Update the cards with animation
                    animateCounter('rank_wise_total_students', data.total_students);
                    animateCounter('rank_wise_total_pass', data.total_pass);
                    animateCounter('rank_wise_total_fail', data.total_fail);
                    animateCounter('rank_wise_total_percentage', passPercentage, '%');
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
            $('#reank_wise_table_list').bootstrapTable('refresh');
        }
    }

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
            if (event.target.getAttribute('href') === '#rank_wise_results') {
                handleTabChange();
            }
        });
    });

    // Event listeners for filter changes
    document.getElementById('filter_rank_wise_session_year_id').addEventListener('change', function() {
        updateStatistics();
        refreshTableData();
    });
    
    document.getElementById('filter_rank_wise_class_section_id').addEventListener('change', function() {
        updateStatistics();
        refreshTableData();
    });

    document.getElementById('filter_rank_wise_subject_id').addEventListener('change', function() {
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
            // Format the number to have at most 2 decimal places
            const formattedValue = Math.round(currentValue * 100) / 100;
            element.textContent = formattedValue + suffix;
        }, 50);
    }
});
</script>