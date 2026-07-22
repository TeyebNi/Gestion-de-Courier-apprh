@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Register') }}</div>

                <div class="card-body">
                    <div id="ajax-errors"></div>

                    <form id="registerForm" method="POST" action="{{ route('register') }}">
                        @csrf

                        <div class="row mb-3">
                            <label for="name" class="col-md-4 col-form-label text-md-end">{{ __('Name') }}</label>
                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
                                <span class="invalid-feedback d-block" data-error-for="name"></span>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Email Address') }}</label>
                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required autocomplete="email">
                                <span class="invalid-feedback d-block" data-error-for="email"></span>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Password') }}</label>
                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control" name="password" required autocomplete="new-password">
                                <span class="invalid-feedback d-block" data-error-for="password"></span>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-end">{{ __('Confirm Password') }}</label>
                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-4 col-form-label text-md-end">Compte :</label>
                            <div class="col-md-6">
                                <input type="hidden" name="role" id="role" value="user">
                                <button type="button" class="btn btn-outline-primary role-btn" data-role="admin">Admin</button>
                                <button type="button" class="btn btn-primary role-btn" data-role="user">User</button>
                                <span class="invalid-feedback d-block" data-error-for="role"></span>
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" id="registerBtn" class="btn btn-primary">
                                    {{ __('Register') }}
                                </button>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6 offset-md-4">
                                {{ __('Already have an account?') }}
                                <a href="{{ route('login') }}">{{ __('Signin') }}</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modale de succès -->
<div id="successOverlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:2000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; padding:32px; width:90%; max-width:360px; text-align:center; box-shadow:0 10px 30px rgba(0,0,0,0.2);">
        <div style="margin:0 auto 16px; width:64px; height:64px; border-radius:50%; border:3px solid #28a745; display:flex; align-items:center; justify-content:center;">
            <span style="color:#28a745; font-size:32px;">&#10003;</span>
        </div>
        <h5 style="margin-bottom:8px;">Inscription réussie !</h5>
        <p id="successDetails" style="color:#666; margin-bottom:20px;"></p>
        <button id="closeSuccess" class="btn btn-primary" style="width:100%;">OK</button>
    </div>
</div>

<script>
// Sélection Admin / User
document.querySelectorAll('.role-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.role-btn').forEach(function (b) {
            b.classList.remove('btn-primary');
            b.classList.add('btn-outline-primary');
        });
        this.classList.remove('btn-outline-primary');
        this.classList.add('btn-primary');
        document.getElementById('role').value = this.dataset.role;
    });
});

document.getElementById('registerForm').addEventListener('submit', function (e) {
    e.preventDefault();

    var form = e.target;
    var btn = document.getElementById('registerBtn');
    var errorsBox = document.getElementById('ajax-errors');

    errorsBox.innerHTML = '';
    document.querySelectorAll('[data-error-for]').forEach(function (el) { el.textContent = ''; });
    document.querySelectorAll('.form-control').forEach(function (el) { el.classList.remove('is-invalid'); });

    btn.disabled = true;

    fetch(form.action, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: new FormData(form),
    })
    .then(function (response) {
        return response.json().then(function (data) {
            return { status: response.status, data: data };
        });
    })
    .then(function (result) {
        btn.disabled = false;

        if (result.status === 200 && result.data.success) {
            document.getElementById('successDetails').textContent = result.data.name + ' (' + result.data.email + ') - ' + result.data.role;
            document.getElementById('successOverlay').style.display = 'flex';
            form.reset();
        } else if (result.status === 422) {
            var errors = result.data.errors || {};
            Object.keys(errors).forEach(function (field) {
                var span = document.querySelector('[data-error-for="' + field + '"]');
                var input = document.getElementById(field);
                if (span) span.textContent = errors[field][0];
                if (input) input.classList.add('is-invalid');
            });
        } else {
            errorsBox.innerHTML = '<div class="alert alert-danger">Une erreur est survenue. Réessayez.</div>';
        }
    })
    .catch(function () {
        btn.disabled = false;
        errorsBox.innerHTML = '<div class="alert alert-danger">Impossible de contacter le serveur.</div>';
    });
});

document.getElementById('closeSuccess').addEventListener('click', function () {
    window.location.href = "{{ route('login') }}";
});
</script>
@endsection
