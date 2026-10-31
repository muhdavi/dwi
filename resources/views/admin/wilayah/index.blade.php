<x-admin-layout :title="$cfg['label']">
    @if ($parent)
        <ol class="breadcrumb bg-white shadow-sm">
            @foreach ($ancestors as $crumb)
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.wilayah.index', $crumb['level']) }}">{{ $crumb['label'] }}</a>:
                    {{ $crumb['name'] }}
                </li>
            @endforeach
            <li class="breadcrumb-item active">{{ $cfg['label'] }}</li>
        </ol>
    @endif

    <div class="card">
        <div class="card-header">
            <form method="GET" class="form-inline">
                @if ($parent)
                    <input type="hidden" name="parent" value="{{ $parent->id }}">
                @endif
                <div class="input-group">
                    <input type="search" name="q" value="{{ $search }}" class="form-control" placeholder="Cari nama atau kode...">
                    <div class="input-group-append">
                        <button class="btn btn-default" type="submit"><i class="fas fa-search"></i></button>
                    </div>
                </div>
                @if ($search)
                    <a href="{{ route('admin.wilayah.index', array_filter(['level' => $level, 'parent' => $parent?->id])) }}" class="btn btn-link">Reset</a>
                @endif
            </form>
            <div class="card-tools">
                <a href="{{ route('admin.wilayah.create', array_filter(['level' => $level, 'parent' => $parent?->id])) }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Tambah {{ $cfg['label'] }}
                </a>
            </div>
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th style="width: 140px">Kode</th>
                        <th>Nama</th>
                        @if ($cfg['parent'] && ! $parent)
                            <th>{{ \App\Http\Controllers\Admin\WilayahController::LEVELS[$cfg['parent']]['label'] }}</th>
                        @endif
                        @if ($cfg['child'])
                            <th>{{ \App\Http\Controllers\Admin\WilayahController::LEVELS[$cfg['child']]['label'] }}</th>
                        @endif
                        <th style="width: 160px" class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            <td><code>{{ $row->id }}</code></td>
                            <td>{{ $row->{$cfg['name']} }}</td>
                            @if ($cfg['parent'] && ! $parent)
                                <td>{{ $parentNames[$row->{$cfg['parent_column']}] ?? '-' }}</td>
                            @endif
                            @if ($cfg['child'])
                                <td>
                                    <a href="{{ route('admin.wilayah.index', ['level' => $cfg['child'], 'parent' => $row->id]) }}">
                                        {{ number_format($childCounts[$row->id] ?? 0, 0, ',', '.') }} data
                                    </a>
                                </td>
                            @endif
                            <td class="text-right">
                                <a href="{{ route('admin.wilayah.edit', [$level, $row->id]) }}" class="btn btn-warning btn-xs"><i class="fas fa-edit"></i> Ubah</a>
                                <form method="POST" action="{{ route('admin.wilayah.destroy', [$level, $row->id]) }}" class="d-inline"
                                      data-confirm="Hapus {{ $cfg['label'] }} &quot;{{ $row->{$cfg['name']} }}&quot;?">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-xs"><i class="fas fa-trash"></i> Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Tidak ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer d-flex justify-content-between align-items-center">
            <small class="text-muted">Total {{ number_format($rows->total(), 0, ',', '.') }} data</small>
            {{ $rows->onEachSide(1)->links('pagination::bootstrap-4') }}
        </div>
    </div>
</x-admin-layout>
