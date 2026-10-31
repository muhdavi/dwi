<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Data Wilayah Indonesia') }} - Data Wilayah Indonesia</title>
        <meta name="description" content="Data lengkap wilayah administrasi Indonesia: provinsi, kabupaten/kota, kecamatan, dan desa/kelurahan.">
        <link rel="stylesheet" href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700;800&display=swap">
        @vite(['resources/css/app.css', 'resources/js/landing.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gray-50">
        <header class="bg-white border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <a href="{{ route('landing') }}" class="font-extrabold text-lg text-red-600">Data Wilayah Indonesia</a>
                <nav class="flex items-center gap-4 text-sm font-semibold">
                    @auth
                        <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-red-600">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-red-600">Masuk</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="px-3 py-1.5 rounded-md bg-red-600 text-white hover:bg-red-700">Daftar</a>
                        @endif
                    @endauth
                </nav>
            </div>
        </header>

        <section class="bg-gradient-to-br from-red-600 to-red-800 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24 text-center">
                <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight">Data Wilayah Indonesia</h1>
                <p class="mt-4 max-w-2xl mx-auto text-lg text-red-100">
                    Seluruh wilayah administrasi Indonesia dalam satu basis data bertingkat:
                    provinsi, kabupaten/kota, kecamatan, hingga desa/kelurahan.
                </p>
                <a href="#statistik" class="inline-block mt-8 px-6 py-3 rounded-md bg-white text-red-700 font-bold hover:bg-red-50">Lihat statistik</a>
            </div>
        </section>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12" id="statistik">
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                @foreach ([
                    'Provinsi' => $stats['totals']['provinsi'],
                    'Kabupaten' => $stats['totals']['kabupaten'],
                    'Kota' => $stats['totals']['kota'],
                    'Kecamatan' => $stats['totals']['kecamatan'],
                    'Desa/Kelurahan' => $stats['totals']['desa'],
                ] as $label => $value)
                    <div class="bg-white rounded-lg shadow-sm p-5 text-center">
                        <div class="text-3xl font-extrabold text-red-600">{{ number_format($value, 0, ',', '.') }}</div>
                        <div class="mt-1 text-sm font-semibold text-gray-500">{{ $label }}</div>
                    </div>
                @endforeach
            </div>

            @if ($stats['totals']['desa'] === 0)
                <div class="mt-10 rounded-lg bg-yellow-50 border border-yellow-200 p-5 text-center text-yellow-800">
                    Data wilayah belum tersedia. Jalankan <code>php artisan migrate --seed</code> terlebih dahulu.
                </div>
            @endif

            <div class="mt-10 grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <h2 class="font-bold mb-4">Desa/Kelurahan per Wilayah Pulau</h2>
                    <div class="h-72"><canvas id="chart-region"></canvas></div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <h2 class="font-bold mb-4">Desa vs Kelurahan</h2>
                    <div class="h-72"><canvas id="chart-tipe"></canvas></div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <h2 class="font-bold mb-4">Kabupaten vs Kota</h2>
                    <div class="h-72"><canvas id="chart-kab-kota"></canvas></div>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <h2 class="font-bold mb-4">Desa/Kelurahan per Provinsi</h2>
                    <div class="h-[56rem]"><canvas id="chart-desa-provinsi"></canvas></div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <h2 class="font-bold mb-4">Kabupaten dan Kota per Provinsi</h2>
                    <div class="h-[56rem]"><canvas id="chart-kab-provinsi"></canvas></div>
                </div>
            </div>

            <div class="mt-6 bg-white rounded-lg shadow-sm p-5">
                <h2 class="font-bold mb-4">10 Kabupaten/Kota dengan Desa/Kelurahan Terbanyak</h2>
                <div class="h-96"><canvas id="chart-top-kab"></canvas></div>
            </div>
        </main>

        <footer class="border-t border-gray-200 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 text-sm text-gray-500 text-center">
                Sumber data: <a class="underline" href="https://github.com/cahyadsn/wilayah" target="_blank" rel="noopener">cahyadsn/wilayah</a>
                (Kepmendagri No 300.2.2-2138 Tahun 2025, lisensi MIT).
            </div>
        </footer>

        <script id="landing-stats" type="application/json">@json($stats)</script>
    </body>
</html>
