<x-admin-layout :title="($row ? 'Ubah ' : 'Tambah ').$cfg['label']">
    <div class="card card-primary">
        <form method="POST"
              action="{{ $row ? route('admin.wilayah.update', [$level, $row->id]) : route('admin.wilayah.store', $level) }}">
            @csrf
            @if ($row) @method('PUT') @endif

            <div class="card-body">
                @if ($row)
                    @foreach ($ancestors as $crumb)
                        @continue($crumb['level'] === $level)
                        <div class="form-group">
                            <label>{{ $crumb['label'] }}</label>
                            <input type="text" class="form-control" value="{{ $crumb['id'] }} — {{ $crumb['name'] }}" disabled>
                        </div>
                    @endforeach
                    <div class="form-group">
                        <label>Kode</label>
                        <input type="text" class="form-control" value="{{ $row->id }}" disabled>
                        <small class="text-muted">Kode tidak dapat diubah karena menjadi acuan data turunannya.</small>
                    </div>
                @else
                    @if ($chain)
                        <div data-cascade data-api="{{ url('/api/wilayah') }}" data-initial="{{ old('parent_id', $parentId) }}">
                            @foreach ($chain as $i => $parentLevel)
                                <div class="form-group">
                                    <label>{{ \App\Http\Controllers\Admin\WilayahController::LEVELS[$parentLevel]['label'] }}</label>
                                    <select data-level="{{ $parentLevel }}" class="form-control @if ($loop->last && $errors->has('parent_id')) is-invalid @endif"
                                            @if ($loop->last) name="parent_id" required @endif>
                                        <option value="">Memuat...</option>
                                    </select>
                                    @if ($loop->last)
                                        @error('parent_id') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="form-group">
                        <label for="kode">Kode ({{ $cfg['suffix'] }} digit)</label>
                        <div class="input-group">
                            @if ($chain)
                                <div class="input-group-prepend"><span class="input-group-text" data-kode-prefix>{{ $parentId }}</span></div>
                            @endif
                            <input type="text" id="kode" name="kode" value="{{ old('kode') }}" maxlength="{{ $cfg['suffix'] }}"
                                   inputmode="numeric" class="form-control @error('kode') is-invalid @enderror" required>
                            @error('kode') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                        <small class="text-muted">Kode lengkap = kode induk + kode ini (mis. 11 → 1101).</small>
                    </div>
                @endif

                <div class="form-group">
                    <label for="nama">Nama {{ $cfg['label'] }}</label>
                    <input type="text" id="nama" name="nama" value="{{ old('nama', $row?->{$cfg['name']}) }}" maxlength="{{ $cfg['max'] }}"
                           class="form-control @error('nama') is-invalid @enderror" required>
                    @error('nama') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('admin.wilayah.index', array_filter(['level' => $level, 'parent' => $parentId])) }}" class="btn btn-default">Batal</a>
            </div>
        </form>
    </div>
</x-admin-layout>
