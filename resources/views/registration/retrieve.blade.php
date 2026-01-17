<!--
    Form to retrieve existing registration via email, extending the layout.
-->

@extends('layouts.app')

@section('title', 'Retrieve Registration')

@section('content')
    <h1>Retrieve Registration Details</h1>
    <p>You can enter either Parent 1 or Parent 2's email address to retrieve the registration details.</p>

    <!-- If there's a status message in the session, display it -->
    @if(session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    <!-- Overall errors display -->
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Error:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                   <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('registration.retrieve.send') }}">
        @csrf
        <div class="mb-3">
            <label for="email" class="form-label">Parent Email</label>
            <input type="email"
                   class="form-control @error('email') is-invalid @enderror"
                   name="email"
                   id="email"
                   placeholder="Enter Parent 1 or Parent 2 email"
                   required>
            @error('email')
              <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary">Retrieve</button>
    </form>
@endsection
