<!--
    resources/views/auth/login.blade.php

    Simple login form. Copy/paste into your project. 
    Adjust styling as needed (Bootstrap classes).
-->

@extends('layouts.app')

@section('title', 'Login')

@section('content')
<h2>Login</h2>

<!-- If there's a status message (e.g. from logout), display it -->
@if(session('status'))
  <div class="alert alert-info">
    {{ session('status') }}
  </div>
@endif

<!-- Validation errors (e.g. invalid credentials) -->
@if ($errors->any())
  <div class="alert alert-danger">
    <ul>
      @foreach ($errors->all() as $err)
        <li>{{ $err }}</li>
      @endforeach
    </ul>
  </div>
@endif

<div class="mt-3 mb-3">
    <strong>This login is for administrators only.</strong> If you are a parent looking to register or update your child's registration, please return to the <a href="{{ url('/') }}">home page</a>.
</div>

<form method="POST" action="{{ route('login.submit') }}">
  @csrf
  <div class="mb-3">
    <label for="email" class="form-label">Email</label>
    <input type="email" 
           class="form-control @error('email') is-invalid @enderror" 
           name="email" 
           id="email"
           value="{{ old('email') }}" 
           required
           autofocus>
  </div>

  <div class="mb-3">
    <label for="password" class="form-label">Password</label>
    <input type="password" 
           class="form-control @error('password') is-invalid @enderror" 
           name="password" 
           id="password"
           required>
  </div>

  <div class="mb-3 form-check">
    <input type="checkbox" 
           class="form-check-input" 
           id="remember" 
           name="remember" 
           {{ old('remember') ? 'checked' : '' }}>
    <label class="form-check-label" for="remember">Remember Me</label>
  </div>

  <button type="submit" class="btn btn-primary">Login</button>
</form>
@endsection
