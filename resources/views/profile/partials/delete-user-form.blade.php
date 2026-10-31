<div class="card card-danger">
    <div class="card-header"><h3 class="card-title">Hapus Akun</h3></div>

    <div class="card-body">
        Setelah akun dihapus, seluruh data akun akan dihapus permanen.
    </div>

    <div class="card-footer">
        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#confirm-user-deletion">Hapus Akun</button>
    </div>
</div>

<div class="modal fade" id="confirm-user-deletion" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form method="post" action="{{ route('profile.destroy') }}" class="modal-content">
            @csrf
            @method('delete')

            <div class="modal-header">
                <h5 class="modal-title">Yakin ingin menghapus akun?</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Masukkan kata sandi untuk mengonfirmasi penghapusan akun secara permanen.</p>
                <input type="password" name="password" placeholder="Kata sandi"
                       class="form-control @if ($errors->userDeletion->isNotEmpty()) is-invalid @endif">
                @if ($errors->userDeletion->isNotEmpty())
                    <span class="invalid-feedback">{{ $errors->userDeletion->first('password') }}</span>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger">Hapus Akun</button>
            </div>
        </form>
    </div>
</div>

@if ($errors->userDeletion->isNotEmpty())
    <script>
        document.addEventListener('DOMContentLoaded', () => window.jQuery('#confirm-user-deletion').modal('show'));
    </script>
@endif
