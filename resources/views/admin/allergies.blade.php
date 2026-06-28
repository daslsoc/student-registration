@extends('layouts.app')

@section('title', 'Allergies & Medical')

@section('content')
<h1>Allergies &amp; Medical</h1>
<p class="text-muted">Enrolled students with a recorded allergy or special need (anything other than &ldquo;None&rdquo;). Use this as a quick medical reference — it shows each child's class and who to contact.</p>

@php
    // "None"/blank/null all mean "nothing to report" for these columns.
    $isReal = fn ($v) => filled($v) && strtolower(trim($v)) !== 'none';
@endphp

@if ($children->isEmpty())
    <p>No students currently have an allergy or special need recorded.</p>
@else
    <p class="text-muted">{{ $children->count() }} {{ $children->count() === 1 ? 'student' : 'students' }} with a recorded allergy or special need.</p>
    <table class="table table-bordered" id="allergies-table">
        <thead>
            <tr>
                <th>Student #</th>
                <th>Name</th>
                <th>Allergy</th>
                <th>Special Needs</th>
                <th>Allocated Class (Dhamma / Sinhala)</th>
                <th>Day School Year</th>
                <th>Parent</th>
                <th>Parent Contact</th>
                <th>Emergency Contact</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($children as $child)
                <tr>
                    <td>{{ $child->student_number }}</td>
                    <td>{{ $child->first_name }} {{ $child->last_name }}</td>
                    <td>
                        @if ($isReal($child->allergies))
                            <span class="fw-semibold text-danger">{{ $child->allergies }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if ($isReal($child->special_needs))
                            <span class="fw-semibold">{{ $child->special_needs }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>{{ $child->allocated_dhamma_class ?? '—' }} / {{ $child->allocated_sinhala_class ?? '—' }}</td>
                    <td>{{ $child->day_school_year }}</td>
                    <td>{{ optional($child->parent)->parent1_first_name }} {{ optional($child->parent)->parent1_last_name }}</td>
                    <td>
                        {{ optional($child->parent)->parent1_phone }}<br>
                        <small>{{ optional($child->parent)->parent1_email }}</small>
                    </td>
                    <td>
                        {{ optional($child->parent)->emergency_contact_name }}<br>
                        <small>{{ optional($child->parent)->emergency_contact_phone }}</small>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- jQuery / DataTables for search, sort and print -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#allergies-table').DataTable({ pageLength: 25 });
        });
    </script>
@endif
@endsection
