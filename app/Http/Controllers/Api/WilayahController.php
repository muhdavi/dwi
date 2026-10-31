<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\Provinsi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WilayahController extends Controller
{
    private const MAX_PER_PAGE = 500;

    public function provinsi(Request $request): JsonResponse
    {
        return $this->collection($request, Provinsi::query(), 'provinsi');
    }

    public function showProvinsi(int $id): JsonResponse
    {
        return $this->item(Provinsi::findOrFail($id), 'provinsi');
    }

    public function kabupaten(Request $request, int $provinsi): JsonResponse
    {
        Provinsi::findOrFail($provinsi);

        return $this->collection($request, Kabupaten::where('provinsi_id', $provinsi), 'kabupaten', 'provinsi_id');
    }

    public function showKabupaten(int $id): JsonResponse
    {
        return $this->item(Kabupaten::findOrFail($id), 'kabupaten', 'provinsi_id');
    }

    public function kecamatan(Request $request, int $kabupaten): JsonResponse
    {
        Kabupaten::findOrFail($kabupaten);

        return $this->collection($request, Kecamatan::where('kabupaten_id', $kabupaten), 'kecamatan', 'kabupaten_id');
    }

    public function showKecamatan(int $id): JsonResponse
    {
        return $this->item(Kecamatan::findOrFail($id), 'kecamatan', 'kabupaten_id');
    }

    public function kelurahan(Request $request, int $kecamatan): JsonResponse
    {
        Kecamatan::findOrFail($kecamatan);

        return $this->collection($request, Kelurahan::where('kecamatan_id', $kecamatan), 'desa', 'kecamatan_id');
    }

    public function showKelurahan(int $id): JsonResponse
    {
        return $this->item(Kelurahan::findOrFail($id), 'desa', 'kecamatan_id');
    }

    private function collection(Request $request, Builder $query, string $nameColumn, ?string $parentColumn = null): JsonResponse
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:'.self::MAX_PER_PAGE],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        if ($request->filled('q')) {
            $term = addcslashes($request->string('q')->trim()->toString(), '%_\\');
            $query->where($nameColumn, 'like', "%{$term}%");
        }

        $query->orderBy('id');

        if ($request->has('per_page') || $request->has('page')) {
            $page = $query->paginate($request->integer('per_page', 50));

            return response()->json([
                'data' => $page->getCollection()->map(fn ($row) => $this->format($row, $nameColumn, $parentColumn))->values(),
                'meta' => [
                    'current_page' => $page->currentPage(),
                    'per_page' => $page->perPage(),
                    'total' => $page->total(),
                    'last_page' => $page->lastPage(),
                ],
            ]);
        }

        $rows = $query->get();

        return response()->json([
            'data' => $rows->map(fn ($row) => $this->format($row, $nameColumn, $parentColumn))->values(),
            'meta' => ['total' => $rows->count()],
        ]);
    }

    private function item(Model $row, string $nameColumn, ?string $parentColumn = null): JsonResponse
    {
        return response()->json(['data' => $this->format($row, $nameColumn, $parentColumn)]);
    }

    private function format(Model $row, string $nameColumn, ?string $parentColumn = null): array
    {
        $data = [
            'id' => $row->id,
            'kode' => $this->kode((string) $row->id),
            'nama' => $row->{$nameColumn},
        ];

        if ($parentColumn !== null) {
            $data[$parentColumn] = $row->{$parentColumn};
        }

        return $data;
    }

    // Kode resmi: 11 / 11.01 / 11.01.01 / 11.01.01.2001.
    private function kode(string $id): string
    {
        return implode('.', array_filter([
            substr($id, 0, 2),
            substr($id, 2, 2),
            substr($id, 4, 2),
            substr($id, 6),
        ], fn ($part) => $part !== ''));
    }
}
