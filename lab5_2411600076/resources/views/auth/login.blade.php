<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Restaurant Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body class="login-page d-flex justify-content-center align-items-center min-vh-100">
    <div class="card login-card shadow-lg" style="max-width: 400px; width: 100%;">
        <div class="card-header text-center py-4">
            <h2 class="mb-0 text-white">🍽️ Restaurant Manager</h2>
            <p class="mb-0 small text-white-50">Login to your dashboard</p>
        </div>
        <div class="card-body p-4">
            @if (session('status'))
                <div class="alert alert-success mb-3">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                {{-- Email --}}
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input id="email" class="form-control" type="email" name="email" value="{{ old('email') }}" required autofocus />
                    @error('email') <small class="text-danger d-block">{{ $message }}</small> @enderror
                </div>

                {{-- Password --}}
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input id="password" class="form-control" type="password" name="password" required />
                    @error('password') <small class="text-danger d-block">{{ $message }}</small> @enderror
                </div>

                {{-- Remember Me --}}
                <div class="mb-3 form-check">
                    <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
                    <label class="form-check-label" for="remember_me">Remember me</label>
                </div>

                {{-- Forgot Password Link --}}
                <div class="text-end mb-3">
                    <a href="{{ route('password.request') }}" class="small">Forgot Password?</a>
                </div>

                {{-- Login Button --}}
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">Login</button>
                </div>

                {{-- Register Link + Demo Credentials --}}
                <div class="text-center mt-3">
                    <small class="text-muted d-block mb-2">Demo: admin@test.com / password123</small>
                    <a href="{{ route('register') }}" class="small">Don't have an account? Register</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>