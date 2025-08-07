@extends('layouts.master')

@section('title')
    {{ __('add_bulk_questions') }}
@endsection


@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ __('add_bulk_questions') }}
                {{-- {{ storage_path('images/online_exam.png') }} --}}
            </h3>
        </div>
        <div class="row">
            <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <form class="pt-3" id="create-form" enctype="multipart/form-data"
                            action="{{ route('online-exam-question.store-bulk-questions') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label>{{ __('Class Section') }} <span class="text-danger">*</span></label>
                                    <select name="class_section_id[]" required id="class-section-id" class="form-control select2 online-exam-class-section-id select2-dropdown select2-hidden-accessible" style="width:100%;" tabindex="-1" aria-hidden="true" multiple>
                                        {{-- <option value="">--- {{ __('select') . ' ' . __('Class Section') }} ---</option> --}}
                                        @foreach ($classSections as $data)
                                            <option value="{{ $data->id }}" data-class-id="{{ $data->class_id }}" data-section-id="{{ $data->section_id }}">
                                                {{ $data->full_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-check w-fit-content">
                                        <label class="form-check-label user-select-none">
                                            <input type="checkbox" class="form-check-input" id="select-all" value="1">{{__("Select All")}}
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    
                                    <label>{{ __('subject') }} <span class="text-danger">*</span></label>
                                    @if (Auth::user()->hasRole('School Admin'))
                                        <select required name="subject_id" id="subject-id" class="form-control">
                                            <option value="">-- {{ __('Select Subject') }} --</option>
                                            <option value="data-not-found">-- {{ __('no_data_found') }} --</option>
                                            @foreach ($classSubjects as $item)
                                                <option value="{{ $item->subject_id }}" data-class-section="{{ $item->class_id }}">{{ $item->subject_with_name}}</option>
                                            @endforeach
                                        </select>
                                    @else
                                    {!! Form::hidden('user_id', Auth::user()->id, ['id' => 'user_id']) !!}
                                        <select required name="subject_id" id="subject-id" class="form-control">
                                            <option value="">-- {{ __('Select Subject') }} --</option>
                                            <option value="data-not-found">-- {{ __('no_data_found') }} --</option>
                                            @foreach ($subjectTeachers as $item)
                                                <option value="{{ $item->subject_id }}" data-class-section="{{ $item->class_section_id }}" data-user="{{ Auth::user()->id }}">{{ $item->subject_with_name}}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                </div>
                                <div class="form-group col-sm-12 col-md-6">
                                    <label for="file-upload-default">{{ __('file_upload') }} <span
                                            class="text-danger">*</span></label>
                                    <input type="file" name="file" class="file-upload-default" />
                                    <div class="input-group col-xs-12">
                                        <input type="text" class="form-control file-upload-info" id="file-upload-default"
                                            disabled="" placeholder="{{ __('file_upload') }}" required="required" />
                                        <span class="input-group-append">
                                            <button class="file-upload-browse btn btn-theme"
                                                type="button">{{ __('upload') }}</button>
                                        </span>
                                    </div>
                                </div>
                                <div class="form-group col-sm-12 col-xs-12">
                                    <input class="btn btn-theme submit_bulk_file float-right" type="submit"
                                        value="{{ __('submit') }}" name="submit" id="submit_bulk_file">
                                </div>
                            </div>
                        </form>
                        <hr>
                        <div class="row form-group col-sm-12 col-md-4 mt-5">
                            <a class="btn btn-theme form-control"
                                href="{{ route('online-exam-question.download-smaple-data-file') }}" download>
                                <strong>{{ __('download_dummy_file') }}</strong>
                            </a>
                        </div>
                        <div class="row col-sm-12 col-xs-12">
                            <span style="font-size: 14px">
                                <b>{{ __('note') }} :-</b><br>
                                1. {{ __('First download dummy file and convert to .csv file then upload it') }}. <br>
                                2. {{ __('If want more question options, then add after option_d column into csv file. e.g.- option_e, option_f') }}.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
