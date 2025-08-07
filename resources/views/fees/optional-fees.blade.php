{{-- @dd($feesClassTypes) --}}
@extends('layouts.master')

@section('title')
    {{ __('optional') . ' ' . __('fees') }}
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ __('optional') . ' ' . __('fees') }}
            </h3>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card search-container">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title"></h4>
                        <div id="toolbar">
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label class="filter-menu" for="session_year_id"> {{ __('Session Year') }} </label>
                                    <select name="session_year_id" id="session_year_id" class="form-control">
                                        @foreach ($session_year_all as $session_year)
                                            <option value="{{ $session_year->id }}"
                                                {{ $session_year->default ? 'selected' : '' }}> {{ $session_year->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-4">
                                    <label for="filter-class-section-id"
                                        class="filter-menu">{{ __('Class Section') }} <span class="text-danger">*</span></label>
                                    <select name="filter-class-section-id" id="filter-class-section-id"
                                        class="form-control">
                                        <option value="">{{ __('select') }}</option>
                                        @foreach ($class_section as $class)
                                            <option value="{{ $class->id }}" data-class-section-id="{{ $class->class_id }}">
                                                {{ $class->full_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-4">
                                    <label class="filter-menu" for="filter_optional_fees">{{ __('Optional') . ' ' . __('Fees') }} <span class="text-danger">*</span></label>
                                    <select name="filter_optional_fees" id="filter_optional_fees" class="form-control">
                                        <option value="">{{ __('select') }}</option>
                                        @foreach ($feesClassTypes as $item)
                                            <option value="{{ $item->id }}" data-class-section-id="{{ $item->class_id }}">{{ $item->fees_type->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <table aria-describedby="mydesc" class='table' id='table_list' data-toggle="table"
                            data-url="{{ route('fees.optional.list', 1) }}" data-click-to-select="true"
                            data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]"
                            data-search="true" data-toolbar="#toolbar" data-show-columns="true" data-show-refresh="true"
                            data-fixed-columns="false" data-trim-on-search="false" data-mobile-responsive="true"
                            data-sort-name="id" data-sort-order="desc" data-maintain-selected="true"
                            data-export-data-type='all'
                            data-export-options='{ "fileName": "{{ __('fees') }}-{{ __('paid') }}-{{ __('list') }}-<?= date('d-m-y')
                            ?>" ,"ignoreColumn":["operate"]}'
                            data-show-export="true" data-query-params="optionalFeesPaidListQueryParams" data-escape="true">
                            <thead>
                                <tr>
                                    <th scope="col" data-field="id" data-sortable="true" data-visible="false" data-align="center">{{ __('id') }}</th>
                                    <th scope="col" data-field="no" data-formatter="totalFeesFormatter" data-sortable="false" data-align="center">{{ __('no.') }}</th>
                                    <th scope="col" data-field="full_name" data-sortable="false" data-align="center"> {{ __('Student Name') }}</th>
                                    <th scope="col" data-field="optional_fees_amount" data-sortable="false" data-align="center">{{ __('Optional Fees') }}</th>
                                    <th scope="col" data-field="payment_method" data-sortable="false" data-align="center"> {{ __('Payment Method') }}</th>
                                    <th scope="col" data-field="fees_paid.date"  data-sortable="false" data-align="center">{{ __('Date') }}</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

