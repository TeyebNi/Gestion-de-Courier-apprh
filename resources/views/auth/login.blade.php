@extends('layouts.app')

@section('content')
<div class="d-flex flex-column align-items-center justify-content-center" style="min-height: 85vh;">

    <div class="mb-4 text-center">
        <img src="{{ asset('images/logo-tvz.png') }}" alt="Logo" style="width: 100px; height: 100px; object-fit: contain;">
    </div>

    <div class="card shadow-sm" style="width: 100%; max-width: 380px;">
        <div class="card-body p-4">

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">{{ __('Email Address') }}</label>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                           name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
                    @error('email')
                        <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">{{ __('Password') }}</label>
                    <div class="input-group">
                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                               name="password" required autocomplete="current-password">
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword" style="min-width: 42px;">
                            <span id="toggleIcon">👁</span>
                        </button>
                        @error('password')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label" for="remember">
                            {{ __('Remember Me') }}
                        </label>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        {{ __('Log In') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="mt-3 text-center">
        @if (Route::has('register'))
            <a href="{{ route('register') }}">{{ __('Register') }}</a>
        @endif
        @if (Route::has('password.request'))
            <span class="mx-1">|</span>
            <a href="{{ route('password.request') }}">{{ __('Lost your password?') }}</a>
        @endif
    </div>

</div>

<script>
    document.getElementById('togglePassword').addEventListener('click', function () {
        const passwordInput = document.getElementById('password');
        const icon = document.getElementById('toggleIcon');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.textContent = '🙈';
        } else {
            passwordInput.type = 'password';
            icon.textContent = '👁';
        }
    });
</script>
@endsection