@extends('layouts.auth')

@section('main-content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-xl-10 col-lg-12 col-md-9">
            <div class="card o-hidden border-0 shadow-lg my-5">
                <div class="card-body p-0">
                    <div class="row">
                        <div class="col-lg-6 d-none d-lg-block bg-login-image"></div>
                        <div class="col-lg-6">
                            <div class="p-5">
                                <div class="text-center">
                                    <h1 class="h4 text-gray-900 mb-4">{{ __('Verify Email') }}</h1>
                                    <p class="mb-4">Enter the 6-digit verification code sent to <strong>{{ $email }}</strong></p>
                                </div>

                                @if (session('success'))
                                    <div class="alert alert-success border-left-success" role="alert">
                                        {{ session('success') }}
                                    </div>
                                @endif

                                @if ($errors->any())
                                    <div class="alert alert-danger border-left-danger" role="alert">
                                        <ul class="pl-4 my-2">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('verify-code.verify') }}" class="user">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <input type="hidden" name="email" value="{{ $email }}">

                                    <div class="form-group text-center">
                                        <label for="code" class="text-sm font-weight-bold">{{ __('Verification Code') }}</label>
                                        <input type="text" class="form-control form-control-user text-center" id="code" name="code" placeholder="000000" maxlength="6" minlength="6" inputmode="numeric" pattern="[0-9]{6}" required autofocus style="font-size: 1.5rem; letter-spacing: 0.5rem;">
                                    </div>

                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary btn-user btn-block">
                                            {{ __('Verify Code') }}
                                        </button>
                                    </div>
                                </form>

                                <hr>

                                <div class="text-center">
                                    <form method="POST" action="{{ route('verify-code.resend') }}" class="d-inline">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="hidden" name="email" value="{{ $email }}">
                                        <button type="submit" class="btn btn-link small">{{ __('Resend Code') }}</button>
                                    </form>
                                </div>

                                <div class="text-center mt-3">
                                    <a class="small" href="{{ route('register') }}">{{ __('Register with a different email') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
