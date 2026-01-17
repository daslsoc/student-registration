<!--
    CSV Import form for admin tasks.
    Includes inline error hints for file upload and default registration year.
-->

@extends('layouts.app')

@section('title', 'CSV Import')

@section('content')
    <h1>Import CSV</h1>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>CSV Import Errors:</strong>
            <ul>
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.import_csv') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label for="csv_file" class="form-label">CSV File</label>
            <input type="file"
                   class="form-control @error('csv_file') is-invalid @enderror"
                   name="csv_file"
                   id="csv_file"
                   required>
            @error('csv_file')
              <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <div class="mb-3">
            <label for="default_registration_year" class="form-label">Default Registration Year</label>
            <input type="number"
                   class="form-control @error('default_registration_year') is-invalid @enderror"
                   name="default_registration_year"
                   id="default_registration_year"
                   min="1900"
                   max="{{ date('Y') }}"
                   value="{{ date('Y') }}"
                   required>
            @error('default_registration_year')
              <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">Import</button>
    </form>
@endsection
