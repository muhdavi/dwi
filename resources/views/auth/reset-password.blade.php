<x-guest-layout title="Atur Ulang Kata Sandi">
    <p class="login-box-msg">Buat kata sandi baru.</p>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="input-group mb-3">
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" placeholder="Email"
                   class="form-control @error('email') is-invalid @enderror" required autofocus autocomplete="username">
            <div class="input-group-append"><div class="input-group-text"><span class="fas fa-envelope"></span></div></div>
            @error('email') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
        </div>

        <div class="input-group mb-3">
            <input id="password" type="password" name="password" placeholder="Kata sandi baru"
                   class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password">
            <div class="input-group-append"><div class="input-group-text"><span class="fas fa-lock"></span></div></div>
            @error('password') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
        </div>

        <div class="input-group mb-3">
            <input id="password_confirmation" type="password" name="password_confirmation" placeholder="Konfirmasi kata sandi"
                   class="form-control @error('password_confirmation') is-invalid @enderror" required autocomplete="new-password">
            <div class="input-group-append"><div class="input-group-text"><span class="fas fa-lock"></span></div></div>
            @error('password_confirmation') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="btn btn-primary btn-block">Atur ulang kata sandi</button>
    </form>
</x-guest-layout>
