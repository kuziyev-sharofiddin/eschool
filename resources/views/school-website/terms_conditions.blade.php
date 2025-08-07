@extends('layouts.school.master')
@section('title')
    {{ __('terms_conditions') }}
@endsection
@section('content')
    @php
        $dir = Session::get('language')->is_rtl ? 'rtl' : 'ltr';
    @endphp
    <div class="breadcrumb">
        <div class="container">
            <div class="contentWrapper">
                <span class="title"> {{ __('terms_conditions') }} </span>
                <span dir="{{ $dir }}">
                    <a dir="{{ $dir }}" href="{{ url('/') }}" class="home">{{ __('home') }}</a>
                    <span><i class="fa-solid fa-caret-right"></i></span>
                    <span class="page">{{ __('terms_conditions') }}</span>
                </span>
            </div>
        </div>
    </div>
    
    <section class="aboutUs commonMT commonWaveSect">
        <div class="container">
            <div class="row aboutWrapper">
                <div class="title text-center">
                    <h1>{{ __('terms_conditions') }}</h1>
                </div>

                <div class="col-sm-12 col-md-12">
                    <div class="aboutContentWrapper">
                        <span class="commonDesc">
                            {!! htmlspecialchars_decode($schoolSettings['terms_condition'] ?? '') !!}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
