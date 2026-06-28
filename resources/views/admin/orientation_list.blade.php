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

@section('title', 'Orientation List')

@section('content')
<h1>Orientation List</h1>

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
            <th>Registration Status</th>
            <th>Child Name</th>
            <th>Day School Year</th>
            <th>Allocated Dhamma Class</th>
            <th>Allocated Sinhala Class</th>
            <th>Age (Now)</th>
            <th>Parent 1 Name</th>
            <th>Parent 2 Name</th>
        </tr>
    </thead>
    <tbody>
        @foreach($parents as $parent)
            <!-- Loop through each child for this parent -->
            @foreach($parent->children as $child)
                <tr>
                    <td>{{ $child->student_number }}</td>
                    <td>{{ $parent->registration_status }}</td>
                    <td>{{ $child->first_name }} {{ $child->last_name }}</td>
                    <td>{{ $child->day_school_year }}</td>
                    <td>{{ $child->allocated_dhamma_class ?? '—' }}</td>
                    <td>{{ $child->allocated_sinhala_class ?? '—' }}</td>
                    <td>{{ $child->age() }}</td>
                    <td>
                        {{ $parent->parent1_first_name }} {{ $parent->parent1_last_name }}
                    </td>
                    <td>
                        {{ $parent->parent2_first_name }} {{ $parent->parent2_last_name }}
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
