<div class="card card-info">
    <div class="card-header"><h3 class="card-title">Ubah Kata Sandi</h3></div>

    <form method="post" action="{{ route('password.update') }}">
        @csrf
        @method('put')

        <div class="card-body">
            @if (session('status') === 'password-updated')
                <div class="alert alert-success">Kata sandi berhasil diubah.</div>
            @endif

            @foreach ([
                'current_password' => ['Kata sandi saat ini', 'current-password'],
                'password' => ['Kata sandi baru', 'new-password'],
                'password_confirmation' => ['Konfirmasi kata sandi baru', 'new-password'],
            ] as $field => [$label, $autocomplete])
                <div class="form-group">
                    <label for="{{ $field }}">{{ $label }}</label>
                    <input id="{{ $field }}" name="{{ $field }}" type="password" autocomplete="{{ $autocomplete }}"
                           class="form-control @if ($errors->updatePassword->has($field)) is-invalid @endif">
                    @if ($errors->updatePassword->has($field))
                        <span class="invalid-feedback">{{ $errors->updatePassword->first($field) }}</span>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="card-footer"><button type="submit" class="btn btn-info">Simpan</button></div>
    </form>
</div>
