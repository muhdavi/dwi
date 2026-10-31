<x-guest-layout title="Konfirmasi Kata Sandi">
    <p class="login-box-msg">Ini area aman. Konfirmasi kata sandi Anda sebelum melanjutkan.</p>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div class="input-group mb-3">
            <input id="password" type="password" name="password" placeholder="Kata sandi"
                   class="form-control @error('password') is-invalid @enderror" required autocomplete="current-password">
            <div class="input-group-append"><div class="input-group-text"><span class="fas fa-lock"></span></div></div>
            @error('password') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="btn btn-primary btn-block">Konfirmasi</button>
    </form>
</x-guest-layout>
