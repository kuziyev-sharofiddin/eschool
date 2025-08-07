@extends('layouts.master')

@section('title')
    {{ __('students') }}
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ __('manage_students') }}
            </h3>
        </div>

        <div class="row">
            <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            {{ __('list_students') }}
                        </h4>
                        <div class="row" id="toolbar">
                            <div class="form-group col-sm-12 col-md-4">
                                <label class="filter-menu">{{ __('Class Section') }} <span class="text-danger">*</span></label>
                                <select name="filter_class_section_id" id="filter_class_section_id" class="form-control">
                                    <option value="">{{ __('select_class_section') }}</option>
                                    @foreach ($class_sections as $class_section)
                                        <option value={{ $class_section->id }}>{{$class_section->full_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-sm-12 col-md-4">
                                <label class="filter-menu">{{ __('session_year') }} <span class="text-danger">*</span></label>
                                <select name="filter_session_year_id" id="filter_session_year_id" class="form-control">
                                    @foreach ($sessionYears as $sessionYear)
                                        <option value={{ $sessionYear->id }} {{$sessionYear->default==1?"selected":""}}>{{$sessionYear->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <table aria-describedby="mydesc" class='table' id='table_list'
                                       data-toggle="table" data-url="{{ route('reports.student.student-reports.show',[1]) }}" data-click-to-select="true"
                                       data-side-pagination="server" data-pagination="true"
                                       data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                                       data-toolbar="#toolbar" data-show-columns="true" data-show-refresh="true" data-fixed-columns="false"
                                       data-trim-on-search="false" data-mobile-responsive="true" data-sort-name="id"
                                       data-sort-order="desc" data-maintain-selected="true" data-export-types="['pdf','json', 'xml', 'csv', 'txt', 'sql', 'doc', 'excel']" data-show-export="true"
                                       data-export-options='{ "fileName": "students-list-<?= date('d-m-y') ?>" ,"ignoreColumn": ["operate"]}' data-query-params="studentReportsQueryParams"
                                       data-check-on-init="true" data-escape="true">
                                    <thead>
                                    <tr>
                                        <th scope="col" data-field="id" data-sortable="true" data-visible="false">{{ __('id') }}</th>
                                        <th scope="col" data-field="no">{{ __('no.') }}</th>
                                        <th scope="col" data-field="user.id" data-visible="false">{{ __('User Id') }}</th>
                                        <th scope="col" data-field="user.image" data-formatter="imageFormatter">{{ __('image') }}</th>
                                        <th scope="col" data-field="user.full_name" >{{ __('name') }}</th>
                                        <th scope="col" data-field="user.dob" >{{ __('dob') }}</th>
                                        <th scope="col" data-field="class_section.full_name">{{ __('class_section') }}</th>
                                        <th scope="col" data-field="admission_no"> {{ __('Gr Number') }}</th>
                                        <th scope="col" data-field="user.gender">{{ __('gender') }}</th>
                                        <th scope="col" data-field="admission_date">{{ __('admission_date') }}</th>
                                        <th scope="col" data-field="guardian.email" data-visible="false">{{ __('guardian') . ' ' . __('email') }}</th>
                                        <th scope="col" data-field="guardian.full_name" data-visible="false">{{ __('guardian') . ' ' . __('name') }}</th>
                                        <th scope="col" data-field="guardian.mobile" data-visible="false">{{ __('guardian') . ' ' . __('mobile') }}</th>
                                        <th scope="col" data-field="guardian.gender" data-visible="false">{{ __('guardian') . ' ' . __('gender') }}</th>

                                        {{-- Admission form fields --}}
                                        @foreach ($extraFields as $field)
                                            <th scope="col" data-visible="false" data-escape="false" data-field="{{ $field->name }}">{{ $field->name }}</th>
                                        @endforeach
                                        {{-- End admission form fields --}}
                                        <th data-events="studentEvents" class="align-button text-center" scope="col" data-field="operate" data-escape="false">{{ __('action') }}</th>
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
@endsection

