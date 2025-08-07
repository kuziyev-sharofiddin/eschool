@extends('layouts.master')

@section('title')
    {{ __('View Contact Inquiry') }}
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ __('View Contact Inquiry') }}
            </h3>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">
                            {{ __('Contact Inquiry Details') }}
                            <a href="{{ route('contact-inquiry.index') }}" class="btn btn-primary btn-sm float-right">
                                <i class="fa fa-list"></i> {{ __('Back to List') }}
                            </a>
                        </h4>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">{{ __('Name') }}</label>
                                    <div class="col-sm-9">
                                        <div class="form-control-plaintext">{{ $inquiry->name }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">{{ __('Email') }}</label>
                                    <div class="col-sm-9">
                                        <div class="form-control-plaintext">{{ $inquiry->email }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if(!Auth::user()->hasRole('Super Admin') || $inquiry->subject)
                            <div class="form-group row">
                                <label class="col-sm-3 col-lg-2 col-form-label">{{ __('Subject') }}</label>
                                <div class="col-sm-9 col-lg-10">
                                    <div class="form-control-plaintext">{{ $inquiry->subject ?? 'N/A' }}</div>
                                </div>
                            </div>
                        @endif

                        @if(Auth::user()->hasRole('Super Admin'))
                            <div class="form-group row">
                                <label class="col-sm-3 col-lg-2 col-form-label">{{ __('School') }}</label>
                                <div class="col-sm-9 col-lg-10">
                                    <div class="form-control-plaintext">{{ $inquiry->school ? $inquiry->school->name : 'N/A' }}</div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-lg-2 col-form-label">{{ __('Type') }}</label>
                                <div class="col-sm-9 col-lg-10">
                                    <div class="form-control-plaintext">
                                        <span class="badge {{ $inquiry->type == 'super_admin' ? 'badge-primary' : 'badge-info' }}">
                                            {{ $inquiry->type == 'super_admin' ? 'Super Admin' : 'School' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="form-group row">
                            <label class="col-sm-3 col-lg-2 col-form-label">{{ __('Description') }}</label>
                            <div class="col-sm-9 col-lg-10">
                                <div class="form-control-plaintext">{{ $inquiry->description }}</div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-lg-2 col-form-label">{{ __('Created At') }}</label>
                            <div class="col-sm-9 col-lg-10">
                                <div class="form-control-plaintext">{{ $inquiry->created_at->format('d-m-Y H:i') }}</div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <form action="{{ route('contact-inquiry.destroy', $inquiry->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this inquiry?')">
                                    <i class="fa fa-trash"></i> {{ __('Delete') }}
                                </button>
                            </form>
                            <a href="{{ route('contact-inquiry.index') }}" class="btn btn-light">{{ __('Back') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection 