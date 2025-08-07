@extends('layouts.master')

@section('title')
    {{ __('Contact Inquiries') }}
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ __('manage') . ' ' . __('Contact Inquiries') }}
            </h3>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            {{ __('list') . ' ' . __('Contact Inquiries') }}
                        </h4>
                        <div class="d-block">
                            <div class="">
                                <div class="col-12 text-right d-flex justify-content-end text-right align-items-end">
                                    <b><a href="#" class="table-list-type active mr-2" data-id="0">{{ __('all') }}</a></b> |
                                    <a href="#" class="ml-2 table-list-type" data-id="1">{{ __('Trashed') }}</a>
                                </div>
                            </div>
                        </div>

                        <table aria-describedby="mydesc" class='table' id='table_list' data-toggle="table"
                            data-url="{{ route('contact-inquiry.show') }}" data-click-to-select="true"
                            data-side-pagination="server" data-pagination="true" data-page-size="5" data-page-list="[5, 10, 20, 50, 100, 200]"
                            data-search="true" data-show-columns="true" data-show-refresh="true" data-fixed-columns="false"
                            data-fixed-number="2" data-fixed-right-number="1" data-trim-on-search="false"
                            data-mobile-responsive="true" data-sort-name="id" data-sort-order="desc"
                            data-maintain-selected="true" data-export-data-type='all' data-export-options='{ "fileName": "contact-inquiry-list-<?= date('d-m-y') ?>"
                                ,"ignoreColumn":["operate"]}' data-show-export="true"
                            data-query-params="contactInquiryQueryParams" data-escape="true">
                            <thead>
                                <tr>
                                    <th scope="col" data-field="id" data-visible="false">
                                        {{ __('id') }}
                                    </th>
                                    <th scope="col" data-field="no">{{ __('no.') }}</th>
                                    <th scope="col" data-field="name">{{ __('name') }}</th>
                                    <th scope="col" data-field="email">{{ __('email') }}</th>
                                    @if (!Auth::user()->hasRole('Super Admin'))
                                        <th scope="col" data-field="subject">{{ __('Subject') }}</th>
                                    @endif
                                    <th scope="col" data-field="message">{{ __('Message') }}</th>
                                    <th scope="col" data-field="created_at" >
                                        {{ __('Date') }}</th>
                                    <th scope="col" data-field="operate" data-escape="false">{{ __('action') }}
                                    </th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    {{--
    <script>
        function dateTimeFormatter(value, row) {
            if (value) {
                return moment(value).format('DD-MM-YYYY HH:mm');
            }
            return '-';
        }
    </script> --}}

    <script>
        function contactInquiryQueryParams(params) {
            let selected = $('.table-list-type.active').data('id') || 0;
            return {
                offset: params.offset,
                limit: params.limit,
                search: params.search,
                sort: params.sort,
                order: params.order,
                show_deleted: selected,
                status: $('#status_filter').val() // if any additional filters
            };
        }
    </script>
@endsection