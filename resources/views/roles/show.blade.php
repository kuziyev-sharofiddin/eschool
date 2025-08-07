@extends('layouts.master')

@section('title')
    {{__('show_role')}}
@endsection

@section('content')

    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{__('show_role')}}
            </h3>
            <a class="btn btn-sm btn-theme" href="{{ route('roles.index') }}">{{__('back')}}</a>
        </div>
        <div class="row grid-margin">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-12">
                                <div class="mb-4">
                                    <strong>{{__('name')}}:</strong>
                                    {{ $role->name }}
                                </div>
                            </div>

                            @php
                                $groupedPermissions = $rolePermissions->groupBy(function ($item) {
                                    return explode('-', $item->name)[0];
                                });
                            @endphp
                    
                            @foreach ($groupedPermissions as $group => $permissions)
                                <div class="col-sm-12 col-md-12 mb-4">
                                    <div class="mb-3">
                                        <label class="">
                                            <i class="fa fa-arrow-right"></i>
                                            <strong>{{ ucfirst($group) }}</strong>
                                        </label>
                                    </div>

                                    <div class="row mt-2">
                                        @foreach ($permissions as $value)
                                            <div class="col-lg-3 col-sm-12 col-xs-12 col-md-3">
                                                <div class="">                                                    
                                                    <p class="m-0"><i class="fa fa-check-circle me-2"></i> {{ $value->name }}</p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <hr>
                                </div>
                            @endforeach

                            {{-- <div class="col-xs-12 col-sm-12 col-md-12">
                                <div class="row">
                                    @if(!empty($rolePermissions))
                                        @foreach($rolePermissions as $v)
                                            <div class="col-lg-3 col-sm-12 col-xs-12 col-md-3">
                                                <label class="label label-success">{{ $v->name }}</label>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
