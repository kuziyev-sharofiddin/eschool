@extends('layouts.master')

@section('title')
    {{ __('student_profile') }} - {{ $student->user->first_name }} {{ $student->user->last_name }}
@endsection

@section('content')
<div class="content-wrapper">
    <div class="page-header">
        <h3 class="page-title">
           {{ __('student_profile') }}
        </h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('reports.student.student-reports') }}">{{ __('student_reports') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('profile') }}</li>
            </ol>
        </nav>
    </div>
    <div class="row">
        <!-- Left Profile Card -->
        <div class="col-md-4 grid-margin">
            <div class="card">
                <div class="card-body text-center">
                    <img src="{{ $student->user->image ?? asset('images/default-user.png') }}" 
                         class="rounded-circle mb-3 shadow" 
                         style="width: 120px; height: 120px; object-fit: cover; border: 3px solid #eaeaea;"
                         alt="{{ $student->user->first_name }}'s Photo">
                    <h4 class="mb-1">{{ $student->user->first_name }} {{ $student->user->last_name }}</h4>
                    <p class="text-muted mb-2">{{ __('student') }}</p>
                    <div class="badge text-primary mb-3 font-weight-bold">
                        <span class="text-dark">{{ __('admission_no') }} : </span><span class="text-dark">{{ $student->admission_no }}</span>
                    </div>
                    <hr>
                    <ul class="list-group list-group-flush text-left">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><b>{{ __('medium') }}:</b></span>
                            <span class="text-capitalize font-weight-medium">{{ $student->class_section->medium->name ?? '-' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><b>{{ __('class_section') }}:</b></span>
                            <span class="font-weight-medium">{{ $student->class_section->class->name ?? '-' }} ({{ $student->class_section->section->name ?? '-' }})</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span></i> <b>{{ __('Stream') }}:</b></span>
                            <span class="text-capitalize font-weight-medium">{{ $student->class_section->class->stream->name ?? '-' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><b>{{ __('Shift') }}:</b></span>
                            <span class="text-capitalize font-weight-medium">{{ $student->class_section->class->shift->name ?? '-' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><b>{{ __('session_year') }}:</b></span>
                            <span class="text-capitalize font-weight-medium">{{ $student->session_year->name ?? '-' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><b>{{ __('gender') }}:</b></span>
                            <span class="text-capitalize font-weight-medium">{{ ucfirst($student->user->gender) }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- Right Details Tabs -->
        <div class="col-md-8 grid-margin">
            <div class="card">
                <div class="card-body">
                    <!-- Tabs -->
                    <ul class="nav nav-tabs nav-tabs-line" id="studentTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="profile-tab" data-toggle="tab" href="#profile" role="tab">
                                {{ __('profile') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="attendance-tab" data-toggle="tab" href="#attendance" role="tab">
                                {{ __('attendance') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="exam-tab" data-toggle="tab" href="#exam" role="tab">
                                {{ __('exam') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="fees-tab" data-toggle="tab" href="#fees" role="tab">
                                {{ __('Fees') }}
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content border-0 px-0" id="studentTabContent">
                        <div class="tab-pane fade show active py-3" id="profile" role="tabpanel">
                            <div class="card">
                                <div class="card-header bg-gradient-light p-2">
                                    <h5 class="mb-0 text-theme">{{ __('basic_information') }}</h5>
                                </div>
                                <div class="card-body p-2">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>{{ __('admission_date') }}:</strong> {{ $student->admission_date ?? '-' }}</p>
                                            <p><strong>{{ __('dob') }}:</strong> {{ $student->user->dob ?? '-' }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>{{ __('mobile_number') }}:</strong> {{ $student->user->mobile ?? '-' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                                <div class="card">
                                <div class="card-header bg-gradient-light p-2">
                                    <h5 class="mb-0 text-theme">{{ __('address_information') }}</h5>
                                </div>
                                <div class="card-body p-2">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="text-muted">{{ __('current_address') }}</h6>
                                            <p>{{ $student->user->current_address ?: '-' }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="text-muted">{{ __('permanent_address') }}</h6>
                                            <p>{{ $student->user->permanent_address ?: '-' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card">
                                <div class="card-header bg-gradient-light p-2">
                                    <h5 class="mb-0 text-theme">{{ __('parent_guardian_information') }}</h5>
                                </div>
                                <div class="card-body p-2">
                                    @if(isset($student->guardian))
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>{{ __('name') }}:</strong> {{ $student->guardian->first_name }} {{ $student->guardian->last_name }}</p>
                                            <p><strong>{{ __('gender') }}:</strong> {{ ucfirst($student->guardian->gender) }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>{{ __('email') }}:</strong> {{ $student->guardian->email }}</p>
                                            <p><strong>{{ __('mobile_number') }}:</strong> {{ $student->guardian->mobile }}</p>
                                        </div>
                                    </div>
                                    @else
                                    <div class="alert alert-info">
                                        {{ __('no_guardian_information_available') }}
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <!-- Other tab panes can be filled as needed -->
                        <div class="tab-pane fade py-3" id="attendance" role="tabpanel">
                            @include('reports.student.attendance-report-tab', ['sessionYears' => $sessionYears])
                        </div>
                        <div class="tab-pane fade py-3" id="exam" role="tabpanel">
                            @include('reports.student.exam-report-tab')
                        </div>
                        <div class="tab-pane fade py-3" id="fees" role="tabpanel">
                            @include('reports.student.fees-report-tab', ['studentFees' => $studentFees])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        // Handle tab navigation
        $('#studentTab a').on('click', function (e) {
            e.preventDefault();
            $(this).tab('show');
        });
        
        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
@endpush

