<?php

use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::table('provinsis')->insert([
        ['id' => 11, 'provinsi' => 'Aceh'],
        ['id' => 12, 'provinsi' => 'Sumatera Utara'],
    ]);
    DB::table('kabupatens')->insert([
        ['id' => 1101, 'provinsi_id' => 11, 'kabupaten' => 'Kabupaten Aceh Selatan'],
        ['id' => 1171, 'provinsi_id' => 11, 'kabupaten' => 'Kota Banda Aceh'],
        ['id' => 1201, 'provinsi_id' => 12, 'kabupaten' => 'Kabupaten Tapanuli Tengah'],
    ]);
    DB::table('kecamatans')->insert([
        ['id' => 110101, 'kabupaten_id' => 1101, 'kecamatan' => 'Bakongan'],
        ['id' => 110102, 'kabupaten_id' => 1101, 'kecamatan' => 'Kluet Utara'],
    ]);
    DB::table('kelurahans')->insert([
        ['id' => 1101012001, 'kecamatan_id' => 110101, 'desa' => 'Keude Bakongan'],
        ['id' => 1101012002, 'kecamatan_id' => 110101, 'desa' => 'Ujong Mangki'],
    ]);
});

test('lists provinces without authentication', function () {
    $this->getJson('/api/wilayah/provinsi')
        ->assertOk()
        ->assertJsonPath('meta.total', 2)
        ->assertJsonPath('data.0', ['id' => 11, 'kode' => '11', 'nama' => 'Aceh']);
});

test('walks the hierarchy down to villages', function () {
    $this->getJson('/api/wilayah/provinsi/11/kabupaten')
        ->assertOk()->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.kode', '11.01');

    $this->getJson('/api/wilayah/kabupaten/1101/kecamatan')
        ->assertOk()->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.kode', '11.01.01');

    $this->getJson('/api/wilayah/kecamatan/110101/kelurahan')
        ->assertOk()->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0', ['id' => 1101012001, 'kode' => '11.01.01.2001', 'nama' => 'Keude Bakongan', 'kecamatan_id' => 110101]);
});

test('detail endpoints include the parent id', function () {
    $this->getJson('/api/wilayah/kabupaten/1101')->assertOk()->assertJsonPath('data.provinsi_id', 11);
    $this->getJson('/api/wilayah/kecamatan/110101')->assertOk()->assertJsonPath('data.kabupaten_id', 1101);
    $this->getJson('/api/wilayah/kelurahan/1101012001')->assertOk()->assertJsonPath('data.kecamatan_id', 110101);
    $this->getJson('/api/wilayah/provinsi/12')->assertOk()->assertJsonPath('data.nama', 'Sumatera Utara');
});

test('supports search and pagination', function () {
    $this->getJson('/api/wilayah/provinsi?q=sumatera')->assertOk()->assertJsonCount(1, 'data');
    $this->getJson('/api/wilayah/provinsi?q=%25')->assertOk()->assertJsonCount(0, 'data');

    $this->getJson('/api/wilayah/provinsi?per_page=1&page=2')
        ->assertOk()
        ->assertJsonPath('data.0.id', 12)
        ->assertJsonPath('meta.last_page', 2);

    $this->getJson('/api/wilayah/provinsi?per_page=9999')->assertStatus(422);
});

test('returns json 404 for unknown or invalid ids', function () {
    $this->getJson('/api/wilayah/provinsi/99')->assertNotFound();
    $this->getJson('/api/wilayah/provinsi/99/kabupaten')->assertNotFound();
    $this->getJson('/api/wilayah/kabupaten/abc')->assertNotFound();
});

test('allows cross-origin reads', function () {
    $this->getJson('/api/wilayah/provinsi', ['Origin' => 'https://example.com'])
        ->assertOk()
        ->assertHeader('Access-Control-Allow-Origin');
});
