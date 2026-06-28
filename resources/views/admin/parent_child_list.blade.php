<!--
  resources/views/admin/parent_child_list.blade.php

  This Blade view:
    1) Lists all parents & children in a DataTable.
    2) Includes an "Export CSV" button that calls route('admin.export_csv') 
       (you'll create that route/controller logic).
    3) Uses jQuery DataTables for sorting/searching/pagination.

  Copy/paste this file into your Laravel project, adjusting field names/logic as needed.
-->

@extends('layouts.app')

@section('title', 'Parent & Child List')

@section('content')
<h1>Parent &amp; Child List</h1>

<!--
    A button/link to export CSV.
    Make sure you define Route::get('/admin/export-csv', [AdminController::class, 'exportCsv'])->name('admin.export_csv');
    in your routes/web.php, and implement exportCsv() in AdminController.
-->
<a href="{{ route('admin.export_csv') }}" class="btn btn-secondary mb-3">
  Export CSV
</a>

<!--
    The DataTable listing parents & children.
    Each row shows one parent but can also flatten children data. 
    If you want 1 row per child, you can move children columns into a loop. 
    The example below shows 1 row per Parent with a combined list of children.
-->
<table class="table table-bordered" id="parent-child-table">
    <thead>
        <tr>
            <th>Student ID</th>
            <th>Child Name</th>
            <th>Allocated Dhamma Class</th>
            <th>Allocated Sinhala Class</th>
            <th>Day School Year</th>
            <th>Other Child Information</th>
            <th>Parent 1 Name</th>
            <th>Parent 1 Email</th>
            <th>Parent 1 Phone</th>
            <th>Parent 2 Name</th>
            <th>Parent 2 Email</th>
            <th>Parent 2 Phone</th>
            <th>Emergency Contact</th>
        </tr>
    </thead>
    <tbody>
        @foreach($parents as $parent)
            <!-- Loop through each child for this parent -->
            @foreach($parent->children as $child)
                <tr>
                    <td>{{ $child->student_number }}</td>
                    <td>{{ $child->first_name }} {{ $child->last_name }}</td>
                    <td>{{ $child->allocated_dhamma_class ?? '—' }}</td>
                    <td>{{ $child->allocated_sinhala_class ?? '—' }}</td>
                    <td>{{ $child->day_school_year }}</td>
                    <td>
                        DOB: {{ $child->date_of_birth }}<br>
                        Residency: {{ $child->residency_status }}<br>
                        School: {{ $child->day_school_name }}<br>
                        Allergies: {{ $child->allergies ?? 'N/A' }}<br>
                        Special Needs: {{ $child->special_needs ?? 'N/A' }}
                    </td>
                    <td>
                        {{ $parent->parent1_first_name }} {{ $parent->parent1_last_name }}
                    </td>
                    <td>{{ $parent->parent1_email }}</td>
                    <td>{{ $parent->parent1_phone }}</td>
                    <td>
                        {{ $parent->parent2_first_name }} {{ $parent->parent2_last_name }}
                    </td>
                    <td>{{ $parent->parent2_email }}</td>
                    <td>{{ $parent->parent2_phone }}</td>
                    <td>
                        Name: {{ $parent->emergency_contact_name }} <br>
                        Mobile: {{ $parent->emergency_contact_phone }} <br>
                        Relationship: {{ $parent->relationship_to_family }}
                    </td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>

<!-- jQuery / DataTables scripts -->
<!-- If not already loaded in your layout, ensure these are accessible. -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        $('#parent-child-table').DataTable({
            // Optional DataTable configs
            // E.g., pageLength: 25
        });
    });
</script>
@endsection
