<x-admin-layout title="Dashboard">
    <div class="row">
        @foreach ($totals as $level => $item)
            <div class="col-md-3 col-sm-6">
                <div class="small-box {{ ['provinsi' => 'bg-info', 'kabupaten' => 'bg-success', 'kecamatan' => 'bg-warning', 'kelurahan' => 'bg-danger'][$level] }}">
                    <div class="inner">
                        <h3>{{ number_format($item['total'], 0, ',', '.') }}</h3>
                        <p>{{ $item['label'] }}</p>
                    </div>
                    <div class="icon"><i class="fas fa-map-marker-alt"></i></div>
                    <a href="{{ route('admin.wilayah.index', $level) }}" class="small-box-footer">
                        Kelola data <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card">
        <div class="card-body">
            Selamat datang, <strong>{{ Auth::user()->name }}</strong>. Gunakan menu di sebelah kiri untuk menambah, mengubah, atau menghapus data wilayah.
            Statistik dan grafik lengkap tersedia di <a href="{{ route('landing') }}">halaman beranda</a>.
        </div>
    </div>
</x-admin-layout>
