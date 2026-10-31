@props(['title' => 'Dashboard'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} | {{ config('app.name', 'Data Wilayah') }}</title>
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a></li>
            <li class="nav-item d-none d-sm-inline-block"><a href="{{ route('landing') }}" class="nav-link">Beranda</a></li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#"><i class="far fa-user mr-1"></i> {{ Auth::user()->name }}</a>
                <div class="dropdown-menu dropdown-menu-right">
                    <a href="{{ route('profile.edit') }}" class="dropdown-item"><i class="fas fa-user-cog mr-2"></i> Profil</a>
                    <div class="dropdown-divider"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item"><i class="fas fa-sign-out-alt mr-2"></i> Keluar</button>
                    </form>
                </div>
            </li>
        </ul>
    </nav>

    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="{{ route('dashboard') }}" class="brand-link">
            <i class="fas fa-map-marked-alt brand-image ml-3 mr-2" style="line-height: 1.8"></i>
            <span class="brand-text font-weight-light">Data Wilayah</span>
        </a>

        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-header">DATA WILAYAH</li>
                    @foreach ([
                        'provinsi' => ['Provinsi', 'fa-flag'],
                        'kabupaten' => ['Kabupaten/Kota', 'fa-city'],
                        'kecamatan' => ['Kecamatan', 'fa-map'],
                        'kelurahan' => ['Desa/Kelurahan', 'fa-home'],
                    ] as $key => [$label, $icon])
                        <li class="nav-item">
                            <a href="{{ route('admin.wilayah.index', $key) }}"
                               class="nav-link {{ request()->routeIs('admin.wilayah.*') && request()->route('level') === $key ? 'active' : '' }}">
                                <i class="nav-icon fas {{ $icon }}"></i><p>{{ $label }}</p>
                            </a>
                        </li>
                    @endforeach
                    <li class="nav-header">AKUN</li>
                    <li class="nav-item">
                        <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user-cog"></i><p>Profil</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <h1>{{ $title }}</h1>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        {{ session('error') }}
                    </div>
                @endif

                {{ $slot }}
            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>Data Wilayah Indonesia</strong> &mdash; admin dengan tema AdminLTE.
    </footer>
</div>
</body>
</html>
