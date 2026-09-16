<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Restaurant Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body class="login-page d-flex justify-content-center align-items-center min-vh-100">
    <div class="card login-card shadow-lg" style="max-width: 450px; width: 100%;">
        <div class="card-header text-center py-4">
            <h2 class="mb-0 text-white">🍽️ Restaurant Manager</h2>
            <p class="mb-0 small text-white-50">Create your account</p>
        </div>
        <div class="card-body p-4">
            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input id="name" class="form-control" type="text" name="name" value="{{ old('name') }}" required autofocus />
                    @error('name') <small class="text-danger d-block">{{ $message }}</small> @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input id="email" class="form-control" type="email" name="email" value="{{ old('email') }}" required />
                    @error('email') <small class="text-danger d-block">{{ $message }}</small> @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input id="password" class="form-control" type="password" name="password" required />
                    @error('password') <small class="text-danger d-block">{{ $message }}</small> @enderror
                </div>

                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <input id="password_confirmation" class="form-control" type="password" name="password_confirmation" required />
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">Register</button>
                </div>

                <div class="text-center mt-3">
                    <a href="{{ route('login') }}" class="small">Already have an account? Login</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>