@extends('layouts.default')

@section('title', 'Login')


@section('content')
<div class="row>">
    &nbsp;
</div>

<div class="card mx-auto w-50">
    <div class="card-header">
        <h3 class="center">Login Form</h3>
    </div>
    <div class="row>">
        &nbsp;
    </div>
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="form-group row">
            <label for="email" class="col-md-4 col-form-label text-md-right">Username</label>

            <div class="col-md-6">
                <input id="useranem" type="username" class="form-control @error('username') is-invalid @enderror" name="username"
                       value="{{ old('username') }}" required autocomplete="username" autofocus>

                @error('username')
                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                @enderror
            </div>
        </div>

        <div class="form-group row">
            <label for="password" class="col-md-4 col-form-label text-md-right">Password</label>

            <div class="col-md-6">
                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                @error('password')
                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                @enderror
            </div>
        </div>


        <div class="form-group row mb-0">
            <div class="col-md-8 offset-md-4">
                <button type="submit" class="btn btn-primary">
                    Login
                </button>
            </div>
        </div>
    </form>
</div>
@stop
