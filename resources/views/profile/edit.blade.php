<x-admin-layout title="Profil">
    <div class="row">
        <div class="col-md-6">
            @include('profile.partials.update-profile-information-form')
        </div>
        <div class="col-md-6">
            @include('profile.partials.update-password-form')
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</x-admin-layout>
