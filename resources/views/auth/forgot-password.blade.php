<x-guest-layout title="Lupa Kata Sandi">
    <p class="login-box-msg">Masukkan email Anda dan kami akan mengirim tautan untuk mengatur ulang kata sandi.</p>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="input-group mb-3">
            <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="Email"
                   class="form-control @error('email') is-invalid @enderror" required autofocus>
            <div class="input-group-append"><div class="input-group-text"><span class="fas fa-envelope"></span></div></div>
            @error('email') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="btn btn-primary btn-block">Kirim tautan reset</button>
    </form>

    <p class="mb-0 mt-3"><a href="{{ route('login') }}">Kembali ke halaman masuk</a></p>
</x-guest-layout>
