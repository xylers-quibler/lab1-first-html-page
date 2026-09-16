<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Restaurant Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body class="login-page d-flex justify-content-center align-items-center min-vh-100">
    <div class="card login-card shadow-lg" style="max-width: 450px; width: 100%;">
        <div class="card-header text-center py-4">
            <h2 class="mb-0 text-white">🍽️ Restaurant Manager</h2>
            <p class="mb-0 small text-white-50">Set a new password</p>
        </div>
        <div class="card-body p-4">
            <form method="POST" action="{{ route('password.store') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input id="email" class="form-control" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus />
                    @error('email') <small class="text-danger d-block">{{ $message }}</small> @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">New Password</label>
                    <input id="password" class="form-control" type="password" name="password" required />
                    @error('password') <small class="text-danger d-block">{{ $message }}</small> @enderror
                </div>

                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Confirm New Password</label>
                    <input id="password_confirmation" class="form-control" type="password" name="password_confirmation" required />
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>