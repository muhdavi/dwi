<?php

use App\Http\Controllers\Api\WilayahController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('wilayah')->name('wilayah.')->controller(WilayahController::class)->group(function () {
    Route::get('provinsi', 'provinsi')->name('provinsi.index');
    Route::get('provinsi/{id}', 'showProvinsi')->whereNumber('id')->name('provinsi.show');
    Route::get('provinsi/{provinsi}/kabupaten', 'kabupaten')->whereNumber('provinsi')->name('kabupaten.index');

    Route::get('kabupaten/{id}', 'showKabupaten')->whereNumber('id')->name('kabupaten.show');
    Route::get('kabupaten/{kabupaten}/kecamatan', 'kecamatan')->whereNumber('kabupaten')->name('kecamatan.index');

    Route::get('kecamatan/{id}', 'showKecamatan')->whereNumber('id')->name('kecamatan.show');
    Route::get('kecamatan/{kecamatan}/kelurahan', 'kelurahan')->whereNumber('kecamatan')->name('kelurahan.index');

    Route::get('kelurahan/{id}', 'showKelurahan')->whereNumber('id')->name('kelurahan.show');
});
