@extends('layouts.app')

@section('title', 'Edit user')

@section('content')
<h1 class="h3 mb-1">Edit user</h1>
<p class="text-muted">
    Passwords aren't set here — the account holder resets their own from the login page.
</p>

@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<form method="POST" action="{{ route('admin.users.update', $user) }}" class="row g-3" style="max-width: 40rem;">
    @csrf
    @method('PUT')

    <div class="col-12">
        <label for="name" class="form-label">Name</label>
        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $user->name) }}" required autofocus>
    </div>

    <div class="col-12">
        <label for="email" class="form-label">Email</label>
        <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email', $user->email) }}" required>
    </div>

    <div class="col-12">
        <label for="role_id" class="form-label">Role</label>
        <select name="role_id" id="role_id" class="form-select @error('role_id') is-invalid @enderror" required>
            @foreach($roles as $role)
                <option value="{{ $role->id }}" @selected(old('role_id', $user->role_id) == $role->id)>
                    {{ $role->name }}@if($role->description) — {{ $role->description }}@endif
                </option>
            @endforeach
        </select>
        @if($user->id === auth()->id())
            <div class="form-text">This is your own account — moving yourself to a role without user management would lock you out of these screens.</div>
        @endif
    </div>

    <div class="col-12">
        <button type="submit" class="btn btn-primary">Save changes</button>
        <a href="{{ route('admin.users.index') }}" class="btn btn-link">Cancel</a>
    </div>
</form>
@endsection
