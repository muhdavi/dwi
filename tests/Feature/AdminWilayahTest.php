<?php

use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\Provinsi;
use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->user = User::factory()->create();
    DB::table('provinsis')->insert(['id' => 11, 'provinsi' => 'ACEH']);
    DB::table('kabupatens')->insert(['id' => 1101, 'provinsi_id' => 11, 'kabupaten' => 'KAB. ACEH SELATAN']);
    DB::table('kecamatans')->insert(['id' => 110101, 'kabupaten_id' => 1101, 'kecamatan' => 'Bakongan']);
});

test('guests are redirected to login', function () {
    $this->get('/admin/wilayah/provinsi')->assertRedirect('/login');
    $this->post('/admin/wilayah/provinsi', [])->assertRedirect('/login');
});

test('unknown level returns 404', function () {
    $this->actingAs($this->user)->get('/admin/wilayah/negara')->assertNotFound();
});

test('dashboard and list pages render with the AdminLTE layout', function () {
    $this->actingAs($this->user)->get('/dashboard')->assertOk()->assertSee('main-sidebar', false);

    foreach (['provinsi', 'kabupaten', 'kecamatan', 'kelurahan'] as $level) {
        $this->actingAs($this->user)->get("/admin/wilayah/{$level}")->assertOk();
    }

    $this->actingAs($this->user)->get('/admin/wilayah/kabupaten?parent=11&q=selatan')
        ->assertOk()->assertSee('KAB. ACEH SELATAN');
});

test('can create every level with an id built from the parent code', function () {
    $this->actingAs($this->user);

    $this->post('/admin/wilayah/provinsi', ['kode' => '12', 'nama' => 'SUMATERA UTARA'])->assertRedirect();
    $this->post('/admin/wilayah/kabupaten', ['parent_id' => 12, 'kode' => '01', 'nama' => 'KAB. NIAS'])->assertRedirect();
    $this->post('/admin/wilayah/kecamatan', ['parent_id' => 1201, 'kode' => '02', 'nama' => 'Idanogawo'])->assertRedirect();
    $this->post('/admin/wilayah/kelurahan', ['parent_id' => 120102, 'kode' => '2001', 'nama' => 'Desa Baru'])->assertRedirect();

    expect(Provinsi::find(12)->provinsi)->toBe('SUMATERA UTARA')
        ->and(Kabupaten::find(1201)->provinsi_id)->toBe(12)
        ->and(Kecamatan::find(120102)->kabupaten_id)->toBe(1201)
        ->and(Kelurahan::find(1201022001)->kecamatan_id)->toBe(120102);
});

test('create validates code length, parent and duplicates', function () {
    $this->actingAs($this->user);

    $this->post('/admin/wilayah/provinsi', ['kode' => '1', 'nama' => 'X'])->assertSessionHasErrors('kode');
    $this->post('/admin/wilayah/provinsi', ['kode' => '11', 'nama' => 'X'])->assertSessionHasErrors('kode');
    $this->post('/admin/wilayah/kabupaten', ['parent_id' => 99, 'kode' => '01', 'nama' => 'X'])->assertSessionHasErrors('parent_id');
    $this->post('/admin/wilayah/kabupaten', ['parent_id' => 11, 'kode' => '01', 'nama' => ''])->assertSessionHasErrors('nama');
    $this->post('/admin/wilayah/kabupaten', ['parent_id' => 11, 'kode' => '01', 'nama' => 'X'])->assertSessionHasErrors('kode');
});

test('can update the name but not the code', function () {
    $this->actingAs($this->user)->get('/admin/wilayah/kecamatan/110101/edit')->assertOk()->assertSee('Bakongan');

    $this->actingAs($this->user)
        ->put('/admin/wilayah/kecamatan/110101', ['nama' => 'Bakongan Baru', 'id' => 999999])
        ->assertRedirect();

    expect(Kecamatan::find(110101)->kecamatan)->toBe('Bakongan Baru')
        ->and(Kecamatan::find(999999))->toBeNull();
});

test('deleting a region that still has children is refused', function () {
    $this->actingAs($this->user)->delete('/admin/wilayah/provinsi/11')->assertSessionHas('error');

    expect(Provinsi::find(11))->not->toBeNull();
});

test('can delete a region without children', function () {
    $this->actingAs($this->user)->delete('/admin/wilayah/kecamatan/110101')->assertSessionHas('success');

    expect(Kecamatan::find(110101))->toBeNull();
});
