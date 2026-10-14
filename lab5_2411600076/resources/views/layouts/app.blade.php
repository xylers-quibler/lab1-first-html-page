<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Restaurant Manager')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>

    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-gradient-primary">
            <div class="container-fluid">
                <a class="navbar-brand fw-bold" href="{{ route('dashboard') }}">Restaurant Manager</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto align-items-lg-center">

                        <li class="nav-item dropdown me-lg-3">
                            <a class="nav-link text-white position-relative" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-bell fs-5"></i>
                                @if(auth()->user()->unreadNotifications->count() > 0)
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        {{ auth()->user()->unreadNotifications->count() }}
                                    </span>
                                @endif
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end notification-menu">
                                <li class="d-flex justify-content-between align-items-center px-3 py-2">
                                    <strong>Low Stock Alerts</strong>
                                    @if(auth()->user()->unreadNotifications->count() > 0)
                                        <form method="POST" action="{{ route('notifications.readAll') }}">
                                            @csrf
                                            <button class="btn btn-link btn-sm p-0">Mark all as read</button>
                                        </form>
                                    @endif
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                @forelse(auth()->user()->unreadNotifications->take(5) as $notification)
                                    <li>
                                        <a class="dropdown-item small text-wrap" href="{{ route('notifications.read', $notification->id) }}">
                                            {{ $notification->data['message'] }}
                                            <div class="text-muted">{{ $notification->created_at->diffForHumans() }}</div>
                                        </a>
                                    </li>
                                @empty
                                    <li><span class="dropdown-item small text-muted">No new alerts.</span></li>
                                @endforelse
                            </ul>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> {{ auth()->user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><span class="dropdown-item-text small text-muted">Role: {{ ucfirst(auth()->user()->role) }}</span></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </li>

                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <div class="container-fluid">
        <div class="row">

            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar vh-100 p-3">
                <div class="position-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">
                                Menu Inventory
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('transactions.*') ? 'active' : '' }}" href="{{ route('transactions.index') }}">
                                Transactions
                            </a>
                        </li>
                        @can('admin')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('adjustments.*') ? 'active' : '' }}" href="{{ route('adjustments.create') }}">
                                    Stock Adjustment
                                </a>
                            </li>
                        @endcan
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                                Reports
                            </a>
                        </li>
                        <li class="nav-item">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="nav-link text-danger border-0 bg-transparent w-100 text-start">
                                    Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </main>

        </div>
    </div>

    <footer class="text-center py-3 bg-light mt-4 border-top">
        <p class="mb-0">&copy; 2026 Restaurant Manager. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
