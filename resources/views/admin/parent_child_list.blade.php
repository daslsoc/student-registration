@extends('layouts.app')

@section('title', 'Parent & Child List')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h3 mb-0">Parent &amp; Child List</h1>
    <a href="{{ route('admin.export_csv') }}" class="btn btn-outline-secondary btn-sm">Export CSV</a>
</div>

{{-- Filter by registration status: completed, pending, or both. --}}
<form method="GET" action="{{ route('admin.parent_student_list') }}" class="row g-2 align-items-end mb-3">
    <div class="col-auto">
        <label for="status" class="form-label mb-0">Registration status</label>
        <select name="status" id="status" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="all" @selected($status === 'all')>Both</option>
            <option value="completed" @selected($status === 'completed')>Completed only</option>
            <option value="pending" @selected($status === 'pending')>Pending only</option>
        </select>
    </div>
    <div class="col-auto">
        <noscript><button type="submit" class="btn btn-primary btn-sm">Apply</button></noscript>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-bordered table-sm align-middle" id="parent-child-table">
        <thead class="table-light">
            <tr>
                <th>Status</th>
                <th>Student</th>
                <th>Allocated class</th>
                <th>Child details</th>
                <th>Parent(s)</th>
                <th>Emergency contact</th>
            </tr>
        </thead>
        <tbody>
            @forelse($parents as $parent)
                @foreach($parent->children as $child)
                    <tr>
                        <td>
                            @if($parent->registration_status === \App\Models\ParentModel::STATUS_COMPLETED)
                                <span class="badge text-bg-success">Completed</span>
                            @else
                                <span class="badge text-bg-warning">Pending</span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $child->first_name }} {{ $child->last_name }}</div>
                            <div class="text-muted small">#{{ $child->student_number ?? '—' }} &middot; {{ $child->day_school_year }}</div>
                        </td>
                        <td class="small">
                            Dhamma: {{ $child->allocated_dhamma_class ?? '—' }}<br>
                            Sinhala: {{ $child->allocated_sinhala_class ?? '—' }}
                        </td>
                        <td class="small">
                            DOB: {{ $child->date_of_birth }}<br>
                            Residency: {{ $child->residency_status }}<br>
                            School: {{ $child->day_school_name }}<br>
                            Allergies: {{ $child->allergies ?? 'None' }}<br>
                            Special needs: {{ $child->special_needs ?? 'None' }}
                        </td>
                        <td class="small">
                            <div class="fw-semibold">{{ $parent->parent1_first_name }} {{ $parent->parent1_last_name }}</div>
                            <div>{{ $parent->parent1_email }}</div>
                            <div>{{ $parent->parent1_phone }}</div>
                            @if($parent->parent2_first_name || $parent->parent2_email)
                                <hr class="my-1">
                                <div class="fw-semibold">{{ $parent->parent2_first_name }} {{ $parent->parent2_last_name }}</div>
                                <div>{{ $parent->parent2_email }}</div>
                                <div>{{ $parent->parent2_phone }}</div>
                            @endif
                        </td>
                        <td class="small">
                            {{ $parent->emergency_contact_name }}<br>
                            {{ $parent->emergency_contact_phone }}<br>
                            <span class="text-muted">{{ $parent->relationship_to_family }}</span>
                        </td>
                    </tr>
                @endforeach
            @empty
                <tr><td colspan="6" class="text-center text-muted">No families match this filter.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function () {
        $('#parent-child-table').DataTable({ pageLength: 25, order: [] });
    });
</script>
@endsection
