<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\Provinsi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WilayahController extends Controller
{
    // Kode setiap tingkat = kode induk + sufiks, sehingga panjang sufiks tetap.
    public const LEVELS = [
        'provinsi' => [
            'model' => Provinsi::class, 'label' => 'Provinsi', 'name' => 'provinsi', 'max' => 30,
            'parent' => null, 'parent_column' => null, 'child' => 'kabupaten', 'suffix' => 2,
        ],
        'kabupaten' => [
            'model' => Kabupaten::class, 'label' => 'Kabupaten/Kota', 'name' => 'kabupaten', 'max' => 50,
            'parent' => 'provinsi', 'parent_column' => 'provinsi_id', 'child' => 'kecamatan', 'suffix' => 2,
        ],
        'kecamatan' => [
            'model' => Kecamatan::class, 'label' => 'Kecamatan', 'name' => 'kecamatan', 'max' => 100,
            'parent' => 'kabupaten', 'parent_column' => 'kabupaten_id', 'child' => 'kelurahan', 'suffix' => 2,
        ],
        'kelurahan' => [
            'model' => Kelurahan::class, 'label' => 'Desa/Kelurahan', 'name' => 'desa', 'max' => 100,
            'parent' => 'kecamatan', 'parent_column' => 'kecamatan_id', 'child' => null, 'suffix' => 4,
        ],
    ];

    public function index(Request $request, string $level): View
    {
        $cfg = $this->config($level);
        $model = $cfg['model'];
        $query = $model::query();

        $parentId = $request->query('parent');
        $parent = null;

        if ($cfg['parent'] && is_numeric($parentId)) {
            $parent = $this->config($cfg['parent'])['model']::find($parentId);
            if ($parent) {
                $query->where($cfg['parent_column'], $parent->id);
            }
        }

        if ($search = trim((string) $request->query('q'))) {
            $term = '%'.addcslashes($search, '%_\\').'%';
            $query->where(fn ($q) => $q->where($cfg['name'], 'like', $term)->orWhere('id', 'like', $term));
        }

        $rows = $query->orderBy('id')->paginate(20)->withQueryString();

        $parentNames = [];
        if ($cfg['parent'] && ! $parent) {
            $parentCfg = $this->config($cfg['parent']);
            $parentNames = $parentCfg['model']::whereIn('id', $rows->pluck($cfg['parent_column']))
                ->pluck($parentCfg['name'], 'id');
        }

        $childCounts = [];
        if ($cfg['child']) {
            $childCfg = $this->config($cfg['child']);
            $childCounts = $childCfg['model']::selectRaw($childCfg['parent_column'].' as pid, count(*) as total')
                ->whereIn($childCfg['parent_column'], $rows->pluck('id'))
                ->groupBy($childCfg['parent_column'])
                ->pluck('total', 'pid');
        }

        return view('admin.wilayah.index', [
            'level' => $level,
            'cfg' => $cfg,
            'rows' => $rows,
            'parent' => $parent,
            'parentNames' => $parentNames,
            'childCounts' => $childCounts,
            'search' => $search,
            'ancestors' => $parent ? $this->ancestors($cfg['parent'], $parent) : [],
        ]);
    }

    public function create(Request $request, string $level): View
    {
        $cfg = $this->config($level);
        $parentId = $request->query('parent');

        return view('admin.wilayah.form', [
            'level' => $level,
            'cfg' => $cfg,
            'row' => null,
            'parentId' => is_numeric($parentId) ? $parentId : null,
            'chain' => $this->chain($level),
            'ancestors' => [],
        ]);
    }

    public function store(Request $request, string $level): RedirectResponse
    {
        $cfg = $this->config($level);
        $model = $cfg['model'];

        $rules = [
            'kode' => ['required', 'digits:'.$cfg['suffix']],
            'nama' => ['required', 'string', 'max:'.$cfg['max']],
        ];
        $attributes = ['kode' => 'kode', 'nama' => 'nama'];

        if ($cfg['parent']) {
            $parentCfg = $this->config($cfg['parent']);
            $rules['parent_id'] = ['required', 'integer', Rule::exists((new $parentCfg['model'])->getTable(), 'id')];
            $attributes['parent_id'] = strtolower($parentCfg['label']);
        }

        $data = $request->validate($rules, [], $attributes);

        $id = ($data['parent_id'] ?? '').$data['kode'];

        if ($model::whereKey($id)->exists()) {
            return back()->withInput()->withErrors(['kode' => "Kode {$id} sudah digunakan."]);
        }

        $row = new $model;
        $row->id = (int) $id;
        $row->{$cfg['name']} = $data['nama'];
        if ($cfg['parent']) {
            $row->{$cfg['parent_column']} = $data['parent_id'];
        }
        $row->save();

        $this->flush();

        return redirect()->route('admin.wilayah.index', $this->listParams($level, $row))
            ->with('success', "{$cfg['label']} {$row->getAttribute($cfg['name'])} berhasil ditambahkan.");
    }

    public function edit(string $level, int $id): View
    {
        $cfg = $this->config($level);
        $row = $cfg['model']::findOrFail($id);
        $ancestors = [];

        if ($cfg['parent']) {
            $ancestors = $this->ancestors($cfg['parent'], $this->config($cfg['parent'])['model']::find($row->{$cfg['parent_column']}));
        }

        return view('admin.wilayah.form', [
            'level' => $level,
            'cfg' => $cfg,
            'row' => $row,
            'parentId' => $cfg['parent'] ? $row->{$cfg['parent_column']} : null,
            'chain' => [],
            'ancestors' => $ancestors,
        ]);
    }

    public function update(Request $request, string $level, int $id): RedirectResponse
    {
        $cfg = $this->config($level);
        $row = $cfg['model']::findOrFail($id);

        $data = $request->validate(['nama' => ['required', 'string', 'max:'.$cfg['max']]], [], ['nama' => 'nama']);

        $row->{$cfg['name']} = $data['nama'];
        $row->save();

        $this->flush();

        return redirect()->route('admin.wilayah.index', $this->listParams($level, $row))
            ->with('success', "{$cfg['label']} {$row->getAttribute($cfg['name'])} berhasil diperbarui.");
    }

    public function destroy(string $level, int $id): RedirectResponse
    {
        $cfg = $this->config($level);
        $row = $cfg['model']::findOrFail($id);
        $params = $this->listParams($level, $row);
        $name = $row->{$cfg['name']};

        if ($cfg['child']) {
            $childCfg = $this->config($cfg['child']);

            if ($childCfg['model']::where($childCfg['parent_column'], $row->id)->exists()) {
                return redirect()->route('admin.wilayah.index', $params)
                    ->with('error', "{$cfg['label']} {$name} tidak dapat dihapus karena masih memiliki data {$childCfg['label']}.");
            }
        }

        $row->delete();
        $this->flush();

        return redirect()->route('admin.wilayah.index', $params)
            ->with('success', "{$cfg['label']} {$name} berhasil dihapus.");
    }

    private function config(string $level): array
    {
        return self::LEVELS[$level] ?? abort(404);
    }

    private function listParams(string $level, Model $row): array
    {
        $params = ['level' => $level];
        $parentColumn = self::LEVELS[$level]['parent_column'];

        if ($parentColumn) {
            $params['parent'] = $row->{$parentColumn};
        }

        return $params;
    }

    // Tingkat induk dari yang teratas hingga induk langsung.
    private function chain(string $level): array
    {
        $chain = [];

        for ($parent = self::LEVELS[$level]['parent']; $parent; $parent = self::LEVELS[$parent]['parent']) {
            array_unshift($chain, $parent);
        }

        return $chain;
    }

    // Rantai induk (dari teratas) hingga baris itu sendiri, berdasarkan awalan kode.
    private function ancestors(string $level, ?Model $row): array
    {
        if (! $row) {
            return [];
        }

        $result = [];
        $id = (string) $row->id;

        foreach ($this->chain($level) as $i => $parentLevel) {
            $parentCfg = self::LEVELS[$parentLevel];
            $found = $parentCfg['model']::find(substr($id, 0, [2, 4, 6][$i]));
            $result[] = ['level' => $parentLevel, 'label' => $parentCfg['label'], 'id' => $found?->id, 'name' => $found?->{$parentCfg['name']}];
        }

        $cfg = self::LEVELS[$level];
        $result[] = ['level' => $level, 'label' => $cfg['label'], 'id' => $row->id, 'name' => $row->{$cfg['name']}];

        return $result;
    }

    private function flush(): void
    {
        Cache::forget('landing.stats');
    }
}
