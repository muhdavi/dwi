<div class="card card-primary">
    <div class="card-header"><h3 class="card-title">Informasi Profil</h3></div>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">@csrf</form>

    <form method="post" action="{{ route('profile.update') }}">
        @csrf
        @method('patch')

        <div class="card-body">
            @if (session('status') === 'profile-updated')
                <div class="alert alert-success">Profil berhasil disimpan.</div>
            @endif

            <div class="form-group">
                <label for="name">Nama</label>
                <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $user->name) }}" required autocomplete="name">
                @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email', $user->email) }}" required autocomplete="username">
                @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <small class="form-text text-muted">
                        Email belum terverifikasi.
                        <button form="send-verification" class="btn btn-link btn-sm p-0">Kirim ulang email verifikasi</button>
                    </small>
                    @if (session('status') === 'verification-link-sent')
                        <small class="text-success">Tautan verifikasi baru telah dikirim.</small>
                    @endif
                @endif
            </div>
        </div>

        <div class="card-footer"><button type="submit" class="btn btn-primary">Simpan</button></div>
    </form>
</div>
