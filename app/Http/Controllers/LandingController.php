<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LandingController extends Controller
{
    private const REGIONS = [
        1 => 'Sumatera',
        2 => 'Sumatera',
        3 => 'Jawa',
        5 => 'Bali & Nusa Tenggara',
        6 => 'Kalimantan',
        7 => 'Sulawesi',
        8 => 'Maluku',
        9 => 'Papua',
    ];

    public function __invoke(): View
    {
        $stats = Cache::get('landing.stats');

        if ($stats === null) {
            $stats = $this->buildStats();

            // Data kosong (seeder belum dijalankan) tidak boleh ikut di-cache.
            if ($stats['totals']['desa'] > 0) {
                Cache::put('landing.stats', $stats, now()->addDay());
            }
        }

        return view('landing', ['stats' => $stats]);
    }

    private function buildStats(): array
    {
        $kabupaten = DB::table('kabupatens')
            ->selectRaw('provinsi_id, SUM(CASE WHEN id % 100 >= 71 THEN 1 ELSE 0 END) AS kota, SUM(CASE WHEN id % 100 < 71 THEN 1 ELSE 0 END) AS kab')
            ->groupBy('provinsi_id')
            ->get()
            ->keyBy('provinsi_id');

        $kecamatan = DB::table('kecamatans')
            ->join('kabupatens', 'kabupatens.id', '=', 'kecamatans.kabupaten_id')
            ->selectRaw('kabupatens.provinsi_id AS provinsi_id, COUNT(*) AS total')
            ->groupBy('kabupatens.provinsi_id')
            ->pluck('total', 'provinsi_id');

        $desa = DB::table('kelurahans')
            ->join('kecamatans', 'kecamatans.id', '=', 'kelurahans.kecamatan_id')
            ->join('kabupatens', 'kabupatens.id', '=', 'kecamatans.kabupaten_id')
            ->selectRaw('kabupatens.provinsi_id AS provinsi_id, COUNT(*) AS total')
            ->groupBy('kabupatens.provinsi_id')
            ->pluck('total', 'provinsi_id');

        $provinces = DB::table('provinsis')->orderBy('id')->get()->map(fn ($p) => [
            'id' => $p->id,
            'name' => $p->provinsi,
            'region' => self::REGIONS[intdiv($p->id, 10)] ?? 'Lainnya',
            'kabupaten' => (int) ($kabupaten[$p->id]->kab ?? 0),
            'kota' => (int) ($kabupaten[$p->id]->kota ?? 0),
            'kecamatan' => (int) ($kecamatan[$p->id] ?? 0),
            'desa' => (int) ($desa[$p->id] ?? 0),
        ]);

        $regions = $provinces->groupBy('region')->map(fn ($items, $name) => [
            'name' => $name,
            'provinsi' => $items->count(),
            'desa' => $items->sum('desa'),
            'kecamatan' => $items->sum('kecamatan'),
        ])->sortByDesc('desa')->values();

        $topKabupaten = DB::table('kelurahans')
            ->join('kecamatans', 'kecamatans.id', '=', 'kelurahans.kecamatan_id')
            ->join('kabupatens', 'kabupatens.id', '=', 'kecamatans.kabupaten_id')
            ->selectRaw('kabupatens.kabupaten AS name, COUNT(*) AS total')
            ->groupBy('kabupatens.id', 'kabupatens.kabupaten')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // Kode desa: digit pertama bagian akhir 1 = kelurahan, 2 = desa.
        $types = DB::table('kelurahans')
            ->selectRaw('SUM(CASE WHEN id % 10000 >= 1000 AND id % 10000 < 2000 THEN 1 ELSE 0 END) AS kelurahan, SUM(CASE WHEN id % 10000 >= 2000 AND id % 10000 < 3000 THEN 1 ELSE 0 END) AS desa, COUNT(*) AS total')
            ->first();

        $kabTotal = $provinces->sum('kabupaten');
        $kotaTotal = $provinces->sum('kota');

        return [
            'totals' => [
                'provinsi' => $provinces->count(),
                'kabupaten' => $kabTotal,
                'kota' => $kotaTotal,
                'kecamatan' => $provinces->sum('kecamatan'),
                'desa' => $provinces->sum('desa'),
            ],
            'provinces' => $provinces->all(),
            'regions' => $regions->all(),
            'topKabupaten' => $topKabupaten->map(fn ($k) => ['name' => $k->name, 'total' => (int) $k->total])->all(),
            'types' => [
                'kelurahan' => (int) ($types->kelurahan ?? 0),
                'desa' => (int) ($types->desa ?? 0),
                'lainnya' => (int) (($types->total ?? 0) - ($types->kelurahan ?? 0) - ($types->desa ?? 0)),
            ],
        ];
    }
}
