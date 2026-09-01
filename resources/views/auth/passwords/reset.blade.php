@extends('layouts.app')

@section('title', 'Reset password')

@section('content')
<h2>Choose a new password</h2>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
    </div>
@endif

<form method="POST" action="{{ route('password.update') }}" style="max-width: 30rem;">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">

    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control @error('email') is-invalid @enderror"
               name="email" id="email" value="{{ old('email', $email) }}" required>
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">New password</label>
        <input type="password" class="form-control @error('password') is-invalid @enderror"
               name="password" id="password" required autofocus>
        <div class="form-text">At least 8 characters, with letters and numbers.</div>
    </div>

    <div class="mb-3">
        <label for="password_confirmation" class="form-label">Confirm new password</label>
        <input type="password" class="form-control" name="password_confirmation" id="password_confirmation" required>
    </div>

    <button type="submit" class="btn btn-primary">Set new password</button>
</form>
@endsection
