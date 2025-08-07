@extends('layouts.master')

@section('title')
    {{ __('assign') . ' ' . __('elective') . ' ' . __('subject') }}
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ __('manage') . ' ' . __('elective') . ' ' . __('subject') }}
            </h3>
        </div>

        <div class="row">
            <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            {{ __('list') . ' ' . __('elective') . ' ' . __('subject') }}
                        </h4>

                        <form id="create-form" class="pt-3" action="{{ route('assign.elective.subject.store') }}" method="POST"
                        novalidate="novalidate" data-success-function="formSuccessFunction">
                            @csrf
                            <div class="row" id="toolbar">
                                <div class="form-group col-sm-12 col-md-4">
                                    <label for="session_year_id"
                                        class="filter-menu">{{ __('Session Year') }}</label>
                                    <select name="session_year_id" id="filter-session-year-id"
                                        class="form-control select2">
                                        <option value="">{{ __('select') . ' ' . __('session_year') }}</option>
                                        @foreach ($session_years as $session_year)
                                            <option value={{ $session_year->id }} {{$session_year->default==1 ? "selected" : ""}}>{{ $session_year->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-sm-12 col-md-4">
                                    <label for="class_section_id"
                                        class="filter-menu">{{ __('Class Section') }}</label>
                                    <select name="class_section_id" id="filter-class-section-id"
                                        class="form-control select2">
                                        <option value="">{{ __('select') . ' ' . __('class_section') }}</option>
                                        @foreach ($class_sections as $class_section)
                                            <option value={{ $class_section->id }}
                                                data-class-id="{{ $class_section->class_id }}">
                                                {{ $class_section->full_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-sm-12 col-md-4">
                                    <label class="filter-menu">{{ __('Elective') . ' ' . __('Subject') }} <span
                                            class="text-danger">*</span></label>
                                    <select name="class_subject_id" id="elective-subject-id" class="form-control select2">
                                        <option value="">{{ __('select') . ' ' . __('subject') }}</option>
                                        <option value="data-not-found">-- {{ __('no_data_found') }} --</option>
                                        @foreach ($electiveSubjectGroups as $class)
                                            @foreach ($class['elective_subject_groups'] as $electiveSubjectGroup)
                                                @foreach ($electiveSubjectGroup['subjects'] as $subject)
                                                    <option value="{{ $subject['class_subject_id'] }}" data-class-id="{{ $class['id'] }}">{{ $subject['name_with_type'] }}</option>
                                                @endforeach
                                            @endforeach 
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <table aria-describedby="mydesc" class='table' id='table_list' data-toggle="table"
                                        data-url="{{ route('assign.elective.subject.show') }}" data-click-to-select="true"
                                        data-side-pagination="server" data-pagination="true"
                                        data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                                        data-toolbar="#toolbar" data-show-columns="true" data-show-refresh="true"
                                        data-fixed-columns="true" data-fixed-number="2" data-fixed-right-number="1"
                                        data-trim-on-search="false" data-mobile-responsive="true" data-sort-name="id"
                                        data-sort-order="desc" data-maintain-selected="true"
                                        data-export-types='["txt","excel"]'
                                        data-export-options='{ "fileName": "elective-subject-list-<?= date('d-m-y') ?>"
                                        ,"ignoreColumn": ["operate"]}'
                                        data-query-params="assignElectiveSubjectQueryParams">
                                        <thead>
                                            <tr>
                                                <th scope="col" data-field="state" data-checkbox="true"></th>
                                                <th scope="col" data-field="no" data-sortable="false">
                                                    {{ __('no.') }}</th>
                                                <th scope="col" data-field="id" data-sortable="true"
                                                    data-visible="false">{{ __('id') }}</th>
                                                <th scope="col" data-field="full_name" data-sortable="true">
                                                    {{ __('name') }}</th>
                                                <th scope="col" data-field="class_section">{{ __('class_section') }}</th>
                                                <th scope="col" data-field="elective_subjects"
                                                    data-formatter="assignElectiveSubjectsFormatter">
                                                    {{ __('Selected Subjects') }}</th>
                                                {{-- <th scope="col" data-field="total_subjects">{{ __('Total Elective Subjects') }}</th>
                                                <th scope="col" data-field="total_selected">{{ __('Total Selected Subjects') }}</th> --}}
                                                {{-- <th scope="col" data-field="status"
                                                    data-formatter="assignElectiveSubjectStatusFormatter">
                                                    {{ __('Status') }}</th> --}}
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                                <div class="form-group row col-sm-12 mt-4 d-flex align-items-end justify-content-end">
                                    <textarea id="student_ids" name="student_ids" style="display: none"></textarea>
                                    <button type="submit" class="btn btn-theme" id="assignSubjectBtn">
                                        {{ __('assign') . ' ' . __('subject') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            var $tableList = $('#table_list')
            var selections = []
            var student_ids = [];

            // Check if student_ids is empty
            checkStudentIds();  

        function responseHandler(res) {
            $.each(res.rows, function (i, row) {
                row.state = $.inArray(row.id, selections) !== -1
            })
            return res
        }

        // Check if student_ids is empty
        function checkStudentIds(){
            if (student_ids.length > 0) {
                $('#assignSubjectBtn').prop('disabled', false);
            }else{
                $('#assignSubjectBtn').prop('disabled', true);
            }
        }

        $(function () {
            $tableList.on('check.bs.table check-all.bs.table uncheck.bs.table uncheck-all.bs.table',
                function (e, rowsAfter, rowsBefore) {
                    student_ids = [];
                    var rows = rowsAfter
                    if (e.type === 'uncheck-all') {
                        rows = rowsBefore
                    }
                    var ids = $.map(!$.isArray(rows) ? [rows] : rows, function (row) {
                        return row.id
                    })

                    var func = $.inArray(e.type, ['check', 'check-all']) > -1 ? 'union' : 'difference'
                    selections = window._[func](selections, ids)
                    selections.forEach(element => {
                        student_ids.push(element);
                    });

                    // Check if student_ids is empty
                    checkStudentIds();
                    $('textarea#student_ids').val(student_ids);
                })
        })
        });

        function formSuccessFunction(response) {
            setTimeout(() => {
                // Reset selections
                selections = [];
                student_ids = [];
                $('#table_list').bootstrapTable('refresh');
                $('#create-form').trigger('reset');
                $('#student_ids').val();
            }, 500);
        }
    </script>
@endsection
