{{--
    Shared allocation edit form: a table of students with a Dhamma/Sinhala
    class dropdown each, posting to admin.allocations.update.

    Expects:
      $children   — collection of Child (each with ->parent loaded)
      $classes    — array of selectable class names
      $redirectTo — optional path to return to after saving (defaults handled
                    server-side to the unallocated worklist)
--}}
<form method="POST" action="{{ route('admin.allocations.update') }}">
    @csrf
    @isset($redirectTo)
        <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
    @endisset
    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Day school year</th>
                    <th>Buddhism class</th>
                    <th>Sinhala class</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($children as $child)
                    <tr>
                        <td class="text-nowrap">{{ $child->student_number }}</td>
                        <td class="text-nowrap">{{ $child->first_name }} {{ $child->last_name }}</td>
                        <td class="text-nowrap">{{ $child->day_school_year }}</td>
                        <td>
                            <select name="allocations[{{ $child->student_number }}][dhamma]" class="form-select form-select-sm">
                                <option value="">— None —</option>
                                @foreach ($classes as $class)
                                    <option value="{{ $class }}" @selected($child->allocated_dhamma_class === $class)>{{ $class }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <select name="allocations[{{ $child->student_number }}][sinhala]" class="form-select form-select-sm">
                                <option value="">— None —</option>
                                @foreach ($classes as $class)
                                    <option value="{{ $class }}" @selected($child->allocated_sinhala_class === $class)>{{ $class }}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <button type="submit" class="btn btn-primary">Save allocations</button>
</form>
