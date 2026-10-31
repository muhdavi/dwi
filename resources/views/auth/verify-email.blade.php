<x-guest-layout title="Verifikasi Email">
    <p class="login-box-msg">Verifikasi alamat email Anda dengan mengklik tautan yang kami kirim. Jika belum menerima email, kami dapat mengirim ulang.</p>

    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success">Tautan verifikasi baru telah dikirim ke alamat email Anda.</div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="btn btn-primary btn-block">Kirim ulang email verifikasi</button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-3">
        @csrf
        <button type="submit" class="btn btn-link p-0">Keluar</button>
    </form>
</x-guest-layout>
