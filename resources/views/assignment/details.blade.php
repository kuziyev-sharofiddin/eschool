@extends('layouts.master')

@section('title')
    {{ __('manage') . ' ' . __('assignment') }}
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ __('list') . ' ' . __('assignment_submission') }}
            </h3>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <form action="{{ route('assignment.bulkAssignmentSubmissionUpdate') }}" class="create-form assignment-submission-table" id="formdata">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="assignment_name" value="{{ $assignment->name }}">
                        <input type="hidden" name="subject_name" value="{{ $assignment->class_subject->subject->name_with_type }}">
                        <input type="hidden" name="user_ids" id="user_ids" value="">
                        {{-- <input type="text" name="class_section_id" value="{{ $assignment->class_section->id }}">
                        <input type="text" name="class_subject_id" value="{{ $assignment->class_subject->subject->id }}"> --}}
                        <div class="card-body pb-0">
                            <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
                                <h4 class="card-title">
                                    {{ __('assignment') }}: {{ $assignment->name }}
                                </h4>
                                <h4 class="card-title">
                                    {{ __('class_section') }}: {{ $assignment->class_section->full_name }}
                                </h4>
                                <h4 class="card-title">
                                    {{ __('subject') }}: {{ $assignment->class_subject->subject->name_with_type }}
                                </h4>
                            </div>
                            <div class="row" id="toolbar">

                                {{-- <div class="form-group col-12 col-sm-12 col-md-3 col-lg-6">
                                    <label for="filter-class-section-id" class="filter-menu">{{__("class_section")}}</label>
                                    <select name="class_section_id" id="filter-class-section-id" class="form-control" style="width:100%;" tabindex="-1" aria-hidden="true">
                                        <option value="">{{ __('all') }}</option>
                                        @foreach ($classSections as $data)
                                            <option value="{{ $data->id }}">
                                                {{ $data->full_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div> --}}

                                {{-- <div class="form-group col-12 col-sm-12 col-md-3 col-lg-6">
                                    <label for="filter-subject-id" class="filter-menu">{{__("subject")}}</label>
                                    <select name="class_subject_id" id="filter-subject-id" class="form-control select2" style="width:100%;" tabindex="-1" aria-hidden="true">
                                        <option value="">-- {{ __('Select Subject') }} --</option>
                                        @foreach ($subjectTeachers as $item)
                                            <option value="{{ $item->class_subject_id }}" data-class-section="{{ $item->class_section_id }}">{{ $item->subject_with_name}}</option>
                                        @endforeach
                                    </select>
                                </div> --}}

                                {{-- @if($semesters->count() > 0)
                                    <div class="form-group col-sm-12 col-md-3">
                                        <label for="filter-semester-id" class="filter-menu">{{ __('Semester') }}</label>
                                        <select name="filter-semester-id" id="filter-semester-id" class="form-control">
                                            <option value="">{{ __('all') }}</option>
                                        </select>
                                    </div>
                                @endif --}}

                            </div>
                            <table aria-describedby="mydesc" class='table' id='table_list' data-toggle="table"
                                data-url="{{ route('assignment.showSubmissionDetails', [$assignment->id, $assignment->class_section->id, $assignment->class_subject->subject->id]) }}" data-click-to-select="false"
                                data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]"
                                data-search="true" data-toolbar="#toolbar" data-show-columns="true" data-show-refresh="true"
                                data-fixed-columns="false" data-fixed-number="2" data-fixed-right-number="1"
                                data-trim-on-search="false" data-mobile-responsive="true" data-sort-name="id"
                                data-query-params="AssignmentSubmissionQueryParams" data-sort-order="desc"
                                data-maintain-selected="true" data-export-data-type='all'
                                data-check-on-init="true" data-response-handler="responseHandler"
                                data-export-options='{ "fileName": "assignment-submission-student-list-<?= date('d-m-y') ?>","ignoreColumn": ["operate"]}'
                                data-show-export="true" data-escape="true">
                                <thead>
                                    <tr>
                                        <th data-field="state" data-checkbox="true"></th>
                                        <th scope="col" data-field="id" data-sortable="false" data-visible="false">{{ __('id') }}</th>
                                        <th scope="col" data-field="no">{{ __('no.') }}</th>
                                        <th scope="col" data-field="student.full_name" data-sortable="false">{{ __('student_name') }}</th>
                                        <th scope="col" data-field="file" data-sortable="false" data-formatter="fileFormatter">{{ __('files') }}</th>
                                        <th scope="col" data-field="status" data-sortable="false" data-formatter="assignmentSubmissionStatusUpdateFormatter">{{ __('status') }}</th>
                                        <th scope="col" data-field="points" data-sortable="false" data-formatter="assignmentSubmissionPointsFormatter">{{ __('points') }}</th>
                                        <th scope="col" data-field="feedback" data-sortable="false" data-formatter="assignmentSubmissionFeedbackUpdateFormatter">{{ __('feedback') }}</th>
                                        <th scope="col" data-field="session_year.name" data-sortable="false" data-visible="false">{{ __('Session Year') }}</th>
                                        <th scope="col" data-field="created_at"  data-sortable="false" data-visible="false">{{ __('created_at') }}</th>
                                        <th scope="col" data-field="updated_at"  data-sortable="false" data-visible="false">{{ __('updated_at') }}</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                        <input class="btn btn-theme mx-4 mb-4 float-right" id="create-btn" type="submit" value={{ __('submit') }}>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function () {
            $('.type').trigger('change');
            $('#formdata').submit(function (e) { 
                e.preventDefault();
               setTimeout(() => {
                    // Reset selections
                    selections = [];
                    userIds = [];
                    $('.type').trigger('change');
                    $('#table_list').bootstrapTable('refresh');
                    $('#user_ids').val('');
               }, 1500) 
            });
        });
        
        $('.type').change(function (e) {
            console.log($(this).val());
            
            var selectedType = $('input[name="type"]:checked').val();
            e.preventDefault();
            $('#user_ids').val('').trigger('change');
        });

        var $tableList = $('#table_list')
        var selections = []
        var userIds = [];

        function responseHandler(res) {
            $.each(res.rows, function (i, row) {
                row.state = $.inArray(row.id, selections) !== -1
            })
            return res
        }

        $(function () {
            $tableList.on('check.bs.table check-all.bs.table uncheck.bs.table uncheck-all.bs.table',
                function (e, rowsAfter, rowsBefore) {
                    userIds = [];
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
                        userIds.push(element);
                    });

                    $('#user_ids').val(userIds);
                })
        })
    </script>
@endsection