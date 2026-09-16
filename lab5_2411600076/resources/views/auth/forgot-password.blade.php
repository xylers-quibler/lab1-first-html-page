<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Restaurant Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body class="login-page d-flex justify-content-center align-items-center min-vh-100">
    <div class="card login-card shadow-lg" style="max-width: 450px; width: 100%;">
        <div class="card-header text-center py-4">
            <h2 class="mb-0 text-white">🍽️ Restaurant Manager</h2>
            <p class="mb-0 small text-white-50">Reset your password</p>
        </div>
        <div class="card-body p-4">
            <p class="text-muted small mb-3">
                Forgot your password? Enter your email and we'll send you a reset link.
            </p>

            @if (session('status'))
                <div class="alert alert-success mb-3">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input id="email" class="form-control" type="email" name="email" value="{{ old('email') }}" required autofocus />
                    @error('email') <small class="text-danger d-block">{{ $message }}</small> @enderror
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">Send Reset Link</button>
                </div>

                <div class="text-center mt-3">
                    <a href="{{ route('login') }}" class="small">← Back to Login</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>