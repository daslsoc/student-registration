@extends('layouts.app')

@section('title', 'Forgot password')

@section('content')
<h2>Forgot your password?</h2>

@if(session('status'))
    <div class="alert alert-info">{{ session('status') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
    </div>
@endif

<p class="text-muted" style="max-width: 40rem;">
    Enter the email address for your administrator account and we'll send you a link to set a new password.
    The link works once and expires after 60 minutes.
</p>

<form method="POST" action="{{ route('password.email') }}" style="max-width: 30rem;">
    @csrf
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control @error('email') is-invalid @enderror"
               name="email" id="email" value="{{ old('email') }}" required autofocus>
    </div>

    <button type="submit" class="btn btn-primary">Email me a reset link</button>
    <a href="{{ route('login') }}" class="btn btn-link">Back to login</a>
</form>
@endsection
