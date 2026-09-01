@extends('layouts.app')

@section('title', 'Add user')

@section('content')
<h1 class="h3 mb-1">Add user</h1>
<p class="text-muted">
    Set an initial password and hand it over; they can change it themselves with
    <em>Forgot your password?</em> on the login page.
</p>

@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<form method="POST" action="{{ route('admin.users.store') }}" class="row g-3" style="max-width: 40rem;">
    @csrf

    <div class="col-12">
        <label for="name" class="form-label">Name</label>
        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name') }}" required autofocus>
    </div>

    <div class="col-12">
        <label for="email" class="form-label">Email</label>
        <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email') }}" required>
    </div>

    <div class="col-12">
        <label for="password" class="form-label">Initial password</label>
        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required>
        <div class="form-text">At least 8 characters, with letters and numbers.</div>
    </div>

    <div class="col-12">
        <label for="role_id" class="form-label">Role</label>
        <select name="role_id" id="role_id" class="form-select @error('role_id') is-invalid @enderror" required>
            <option value="" disabled {{ old('role_id') ? '' : 'selected' }}>Choose a role…</option>
            @foreach($roles as $role)
                <option value="{{ $role->id }}" @selected(old('role_id') == $role->id)>
                    {{ $role->name }}@if($role->description) — {{ $role->description }}@endif
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-12">
        <button type="submit" class="btn btn-primary">Add user</button>
        <a href="{{ route('admin.users.index') }}" class="btn btn-link">Cancel</a>
    </div>
</form>
@endsection
