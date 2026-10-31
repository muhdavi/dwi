<?php

use Illuminate\Support\Facades\DB;

test('landing page shows region statistics', function () {
    DB::table('provinsis')->insert(['id' => 11, 'provinsi' => 'Aceh']);
    DB::table('kabupatens')->insert([
        ['id' => 1101, 'provinsi_id' => 11, 'kabupaten' => 'Kabupaten Aceh Selatan'],
        ['id' => 1171, 'provinsi_id' => 11, 'kabupaten' => 'Kota Banda Aceh'],
    ]);
    DB::table('kecamatans')->insert(['id' => 110101, 'kabupaten_id' => 1101, 'kecamatan' => 'Bakongan']);
    DB::table('kelurahans')->insert([
        ['id' => 1101012001, 'kecamatan_id' => 110101, 'desa' => 'Keude Bakongan'],
        ['id' => 1101011001, 'kecamatan_id' => 110101, 'desa' => 'Kelurahan Contoh'],
    ]);

    $response = $this->get('/');

    $response->assertOk()
        ->assertSee('Data Wilayah Indonesia')
        ->assertViewHas('stats', fn ($s) => $s['totals'] === [
            'provinsi' => 1, 'kabupaten' => 1, 'kota' => 1, 'kecamatan' => 1, 'desa' => 2,
        ] && $s['types']['desa'] === 1 && $s['types']['kelurahan'] === 1);
});
