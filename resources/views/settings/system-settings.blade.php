@extends('layouts.master')

@section('title')
    {{ __('general_settings') }}
@endsection


@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ __('general_settings') }}
            </h3>
        </div>
        <div class="row grid-margin">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form id="formdata" class="create-form-without-reset" action="{{ route('system-settings.store') }}" method="POST" novalidate="novalidate" enctype="multipart/form-data">
                            @csrf
                            @include('settings.forms.system-settings-form')
                            
                            <input class="btn btn-theme float-right ml-3" id="create-btn" type="submit" value={{ __('submit') }}>
                            <input class="btn btn-secondary float-right" type="reset" value={{ __('reset') }}>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
         $(document).ready(function () {
            // set web_maintenance setting from database
             $("#web_maintenance").prop( "checked", false );
             if ($('#web_maintenance').val() == 1) {
                 $("#web_maintenance").prop( "checked", true );
             } else { 
                 $("#web_maintenance").prop( "checked", false );
             }
             
             $(document).on('change', '#web_maintenance', function () {
                 if ($('#web_maintenance').val() == 1) {
                     $('#web_maintenance').val(0);
                     $('#txt_web_maintenance').val(0);
                 } else {
                     $('#web_maintenance').val(1);
                     $('#txt_web_maintenance').val(1);
                 }
             });

             // Initialize two_factor_verification state
            if ($('#two_factor_verification').is(':checked')) {
                $('#txt_two_factor_verification').val(1);
                $('#two_factor_verification').prop('checked', true);
            } else {
                $('#txt_two_factor_verification').val(0);
                $('#two_factor_verification').prop('checked', false);
            }

            $(document).on('change', '#two_factor_verification', function () {
                if ($('#two_factor_verification').is(':checked')) {
                    $('#txt_two_factor_verification').val(1);
                    $('#two_factor_verification').prop('checked', true);
                } else {
                    $('#txt_two_factor_verification').val(0);
                    $('#two_factor_verification').prop('checked', false);
                }
            });
             
             $(document).on('change', '#file_upload_size_limit', function () {                
                $('#txt_file_upload_size_limit').val($('#file_upload_size_limit').val());
             });

            // Image preview functionality
            $('#favicon-input').on('change', function() {
                previewImage(this, '#favicon-preview');
            });

            $('#horizontal-logo-input').on('change', function() {
                previewImage(this, '#horizontal-logo-preview');
            });

            $('#vertical-logo-input').on('change', function() {
                previewImage(this, '#vertical-logo-preview');
            });

            $('#login-page-logo-input').on('change', function() {
                previewImage(this, '#login-page-logo-preview');
            });

            function previewImage(input, previewSelector) {
                if (input.files && input.files[0]) {
                    var file = input.files[0];
                    
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $(previewSelector).attr('src', e.target.result);
                        
                        // Update the file name in the input field
                        $(input).closest('.form-group').find('.file-upload-info').val(file.name);
                    }
                    reader.readAsDataURL(file);
                }
            }
        });
        
        function formSuccessFunction(response) {
            setTimeout(() => {
                window.location.reload();
            }, 4000);
        }
    </script>
@endsection
