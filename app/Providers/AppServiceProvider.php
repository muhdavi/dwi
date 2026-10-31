<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(60)->by($request->user()?->id ?: $request->ip()));

        $this->ignoreStaleViteHotFile();
    }

    /**
     * Jika public/hot tertinggal tetapi dev server Vite tidak berjalan,
     * pakai aset hasil build agar halaman (dan grafik) tetap berfungsi.
     */
    private function ignoreStaleViteHotFile(): void
    {
        $hotFile = public_path('hot');

        if (! is_file($hotFile)) {
            return;
        }

        $parts = parse_url(trim((string) file_get_contents($hotFile)));
        $host = trim($parts['host'] ?? '', '[]');
        $port = $parts['port'] ?? 5173;

        $socket = $host !== '' ? @fsockopen($host, $port, $errno, $errstr, 0.3) : false;

        if ($socket === false) {
            Vite::useHotFile(storage_path('framework/vite.hot'));

            return;
        }

        fclose($socket);
    }
}
