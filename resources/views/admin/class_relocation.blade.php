@extends('layouts.app')

@section('title', 'Class Relocation')

@section('content')
<h1>Class Relocation</h1>
<p class="text-muted">Search for any student by name or student number to relocate them to a different class. Parents are emailed automatically when a class actually changes.</p>

@if (session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="GET" action="{{ route('admin.class_relocation') }}" class="row g-2 mb-4">
    <div class="col-12 col-sm-auto">
        <input type="text" name="q" value="{{ $q }}" class="form-control"
               placeholder="Name or student number" aria-label="Search students" autofocus>
    </div>
    <div class="col-12 col-sm-auto">
        <button type="submit" class="btn btn-primary">Search</button>
    </div>
</form>

@if ($q !== '')
    @if ($children->isEmpty())
        <p>No students found matching &ldquo;{{ $q }}&rdquo;.</p>
    @else
        <p class="text-muted">{{ $children->count() }} {{ $children->count() === 1 ? 'student' : 'students' }} found.</p>
        @include('admin.partials.allocation_form', [
            'children' => $children,
            'classes' => $classes,
            'redirectTo' => request()->getRequestUri(),
        ])
    @endif
@endif
@endsection
