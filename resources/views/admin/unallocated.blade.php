@extends('layouts.app')

@section('title', 'Unallocated Students')

@section('content')
<h1>Unallocated Students</h1>
<p class="text-muted">Paid students who still need a class for at least one subject — for example anyone whose day-school year wasn't covered by the auto-allocation rule. To move a student who already has a class, use <a href="{{ route('admin.class_relocation') }}">Class Relocation</a>.</p>

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

@if ($children->isEmpty())
    <p>Every enrolled student has a class for both subjects.</p>
@else
    @include('admin.partials.allocation_form', ['children' => $children, 'classes' => $classes])
@endif
@endsection
