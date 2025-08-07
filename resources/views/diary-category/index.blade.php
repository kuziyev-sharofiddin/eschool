@extends('layouts.master')

@section('title')
    {{ __('diary_category') }}
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ __('manage_diary_categories') }}
            </h3>
        </div>

        <div class="row">
            <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            {{ __('create_diary_category') }}
                        </h4>
                        <form class="create-form pt-3" id="formdata" action="{{ route('diary-categories.store') }}"
                            method="POST" novalidate="novalidate">
                            @csrf
                            <div class="row">
                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ __('name') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('name', null, ['required', 'placeholder' => __('name'), 'class' => 'form-control']) !!}
                                </div>
                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ __('type') }} <span class="text-danger">*</span></label><br>
                                    <div class="d-flex">
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                {!! Form::radio('type', 'positive', true, ['id' => 'positive']) !!}
                                                {{ __('positive') }}
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                {!! Form::radio('type', 'negative', false, ['id' => 'negative']) !!}
                                                {{ __('negative') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- <input class="btn btn-theme" type="submit" value={{ __('submit') }}> --}}
                            <input class="btn btn-theme float-right ml-3" id="create-btn" type="submit"
                                value={{ __('submit') }}>
                            <input class="btn btn-secondary float-right" type="reset" value={{ __('reset') }}>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            {{ __('list_diary_categories') }}
                        </h4>

                        <div class="col-12 text-right">
                            <b><a href="#" class="table-list-type mr-2 active" data-id="0">All</a></b> | <a href="#" class="ml-2 table-list-type" data-id="1">Trashed</a>
                        </div>
                        
                        {{-- <div class="row" id="toolbar">
                            <div class="form-group col-12">
                                <button id="update-status" class="btn btn-secondary" disabled><span
                                        class="update-status-btn-name">{{ __('Inactive') }}</span></button>
                            </div>
                        </div> --}}

                        {{-- <div class="col-12 mt-4 text-right">
                            <b><a href="#" class="table-list-type active mr-2"
                                    data-id="0">{{ __('active') }}</a></b> | <a href="#"
                                class="ml-2 table-list-type" data-id="1">{{ __('Inactive') }}</a>
                        </div> --}}

                        <div class="row">
                            <div class="col-12">
                                <table aria-describedby="mydesc" class='table' id='table_list' data-toggle="table"
                                    data-url="{{ route('diary-categories.show', [1]) }}" data-click-to-select="true"
                                    data-side-pagination="server" data-pagination="true"
                                    data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-toolbar="#toolbar"
                                    data-show-columns="true" data-show-refresh="true" data-trim-on-search="false"
                                    data-mobile-responsive="true" data-sort-name="id" data-sort-order="desc"
                                    data-maintain-selected="true" data-export-data-type='all' data-show-export="true"
                                    data-export-options='{ "fileName": "diary-category-list-<?= date('d-m-y') ?>"
                                    ,"ignoreColumn":["operate"]}'
                                    data-query-params="queryParams" data-escape="true">
                                    <thead>
                                        <tr>
                                            {{-- <th data-field="state" data-checkbox="true"></th> --}}
                                            <th scope="col" data-field="id" data-sortable="true" data-visible="false">
                                                {{ __('id') }}</th>
                                            <th scope="col" data-field="no">{{ __('no.') }}</th>
                                            <th scope="col" data-field="name">{{ __('name') }}</th>
                                            <th scope="col" data-field="type">{{ __('type') }}</th>
                                            <th data-events="diaryCategoryEvents" scope="col"
                                                data-formatter="actionColumnFormatter" data-field="operate"
                                                data-escape="false">{{ __('action') }}</th>
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


    <div class="modal fade" id="editModal" data-backdrop="static" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">{{ __('edit_diary_category') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"><i class="fa fa-close"></i></span>
                    </button>
                </div>
                <form id="editdata" class="edit-form" action="{{ url('diary-categories') }}" novalidate="novalidate">
                {{-- <form id="editdata" class="edit-form" action="{{ url('diary-categories') }}" novalidate="novalidate"> --}}
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group col-sm-12 col-md-12 col-lg-4">
                                <label>{{ __('name') }} <span class="text-danger">*</span></label>
                                {!! Form::text('name', null, [
                                    'required',
                                    'placeholder' => __('name'),
                                    'class' => 'form-control',
                                    'id' => 'name',
                                ]) !!}
                            </div>
                            <div class="form-group col-sm-12 col-md-4">
                                <label>{{ __('type') }} <span class="text-danger">*</span></label><br>
                                <div class="d-flex">
                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label">
                                            {!! Form::radio('type', 'positive', false, ['id' => 'positive']) !!}
                                            {{ __('Positive') }}
                                        </label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label">
                                            {!! Form::radio('type', 'negative', false, ['id' => 'negative']) !!}
                                            {{ __('Negative') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">

                        </div>

                        {{-- <div class="row">
                            <div class="form-group col-sm-12 col-md-4">
                                <div class="d-flex">
                                    <div class="form-check w-fit-content">
                                        <label class="form-check-label ml-4">
                                            <input type="checkbox" class="form-check-input" name="reset_password"
                                                value="1">{{ __('reset_password') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div> --}}

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-dismiss="modal">{{ __('Cancel') }}</button>
                        <input class="btn btn-theme" type="submit" value={{ __('submit') }}>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
