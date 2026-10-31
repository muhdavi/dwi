<?php

use App\Providers\AppServiceProvider;

test('landing page falls back to built assets when the vite hot file is stale', function () {
    $hotFile = public_path('hot');
    $original = is_file($hotFile) ? file_get_contents($hotFile) : null;

    file_put_contents($hotFile, 'http://127.0.0.1:59999');

    try {
        (new AppServiceProvider($this->app))->boot();

        $this->get('/')
            ->assertOk()
            ->assertDontSee('127.0.0.1:59999')
            ->assertSee('/build/assets/landing-', false);
    } finally {
        $original === null ? @unlink($hotFile) : file_put_contents($hotFile, $original);
    }
});
