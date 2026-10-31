<x-guest-layout title="Masuk">
    <p class="login-box-msg">Masuk untuk mengelola data wilayah</p>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="input-group mb-3">
            <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="Email"
                   class="form-control @error('email') is-invalid @enderror" required autofocus autocomplete="username">
            <div class="input-group-append"><div class="input-group-text"><span class="fas fa-envelope"></span></div></div>
            @error('email') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
        </div>

        <div class="input-group mb-3">
            <input id="password" type="password" name="password" placeholder="Kata sandi"
                   class="form-control @error('password') is-invalid @enderror" required autocomplete="current-password">
            <div class="input-group-append"><div class="input-group-text"><span class="fas fa-lock"></span></div></div>
            @error('password') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
        </div>

        <div class="row">
            <div class="col-7">
                <div class="icheck-primary custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="remember_me" name="remember">
                    <label class="custom-control-label" for="remember_me">Ingat saya</label>
                </div>
            </div>
            <div class="col-5">
                <button type="submit" class="btn btn-primary btn-block">Masuk</button>
            </div>
        </div>
    </form>

    @if (Route::has('password.request'))
        <p class="mb-0 mt-3"><a href="{{ route('password.request') }}">Lupa kata sandi?</a></p>
    @endif
</x-guest-layout>
