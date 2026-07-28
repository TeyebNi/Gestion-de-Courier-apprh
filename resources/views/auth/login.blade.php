@extends('layouts.app')

@section('content')
<div class="d-flex flex-column align-items-center justify-content-center" style="min-height: 85vh;">

    <div class="d-flex align-items-center justify-content-center mb-4" style="width: 100%; max-width: 700px;">
        <img src="{{ asset('images/logo-tvz.png') }}" alt="Logo" style="width: 90px; height: 90px; object-fit: contain; flex-shrink: 0;">
    </div>

    <div class="card shadow-sm" style="width: 100%; max-width: 380px;">
        <div class="card-body p-4">

            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

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
                    <label for="email" class="form-label">Adresse Email</label>
                    <div class="input-group">
                        <span class="input-group-text">&#9993;</span>
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                               name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
                    </div>
                    @error('email')
                        <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Mot de passe</label>
                    <div class="input-group">
                        <span class="input-group-text">&#128274;</span>
                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                               name="password" required autocomplete="current-password">
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword" style="min-width: 42px;">
                            <span id="toggleIcon">&#128065;</span>
                        </button>
                    </div>
                    @error('password')
                        <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        Se connecter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="mt-3 text-center">
        @if (Route::has('register'))
            <a href="{{ route('register') }}">Créer un compte</a>
        @endif
        @if (Route::has('password.request'))
            <span class="mx-1">|</span>
            <a href="{{ route('password.request') }}">Mot de passe oublié ?</a>
        @endif
    </div>

</div>

<script>
    document.getElementById('togglePassword').addEventListener('click', function () {
        const passwordInput = document.getElementById('password');
        const icon = document.getElementById('toggleIcon');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.textContent = '\u{1F648}';
        } else {
            passwordInput.type = 'password';
            icon.textContent = '\u{1F441}';
        }
    });
</script>
@endsection
