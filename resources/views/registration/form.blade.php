<!--
    - Uses a shared layout: layouts.app
    - Displays inline error messages with error
    - Dynamically adds new child blocks when you click "+ Add Another Child"
    - Dynamically removes the last child block when you click "- Remove Last Child"
-->

@extends('layouts.app')

@section('title', 'School Registration')

@section('content')
<h1>School Registration</h1>

<p>Welcome! Please fill out the form below to register your child(ren) for the Dhamma and Sinhala Language School of Canberra. Ensure all information is accurate for a smooth registration process.</p>

@if(session('status'))
<div class="alert alert-success">{{ session('status') }}</div>
@endif

@if ($errors->any())
<div class="alert alert-danger">
    <strong>There were some issues with your submission:</strong>
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('registration.submit') }}">
    @csrf

    <!-- ================= PARENT / GUARDIAN SECTION ================= -->
    <h3>Parent / Guardian Information</h3>

    <!-- Parent 1 First Name -->
    <div class="mb-3 row">
        <label for="parent1_first_name" class="col-sm-3 col-form-label">Parent 1 First Name</label>
        <div class="col-sm-7">
            <input type="text"
                class="form-control @error('parent1_first_name') is-invalid @enderror"
                name="parent1_first_name"
                id="parent1_first_name"
                value="{{ old('parent1_first_name') }}"
                required>
            @error('parent1_first_name')
            <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>
    </div>

    <!-- Parent 1 Last Name -->
    <div class="mb-3 row">
        <label for="parent1_last_name" class="col-sm-3 col-form-label">Parent 1 Last Name</label>
        <div class="col-sm-7">
            <input type="text"
                class="form-control @error('parent1_last_name') is-invalid @enderror"
                name="parent1_last_name"
                id="parent1_last_name"
                value="{{ old('parent1_last_name') }}"
                required>
            @error('parent1_last_name')
            <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>
    </div>

    <!-- Parent 1 Email -->
    <div class="mb-3 row">
        <label for="parent1_email" class="col-sm-3 col-form-label">Parent 1 Email</label>
        <div class="col-sm-7">
            <input type="email"
                class="form-control @error('parent1_email') is-invalid @enderror"
                name="parent1_email"
                id="parent1_email"
                value="{{ old('parent1_email') }}"
                required>
            @error('parent1_email')
            <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>
    </div>

    <!-- Parent 1 Phone -->
    <div class="mb-3 row">
        <label for="parent1_phone" class="col-sm-3 col-form-label">Parent 1 Mobile</label>
        <div class="col-sm-2">
            <input type="text"
                class="form-control @error('parent1_phone') is-invalid @enderror"
                name="parent1_phone"
                id="parent1_phone"
                value="{{ old('parent1_phone') }}"
                required>
            @error('parent1_phone')
            <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>
    </div>

    <hr />

    <!-- Parent 2 First Name (optional) -->
    <div class="mb-3 row">
        <label for="parent2_first_name" class="col-sm-3 col-form-label">Parent 2 First Name (optional)</label>
        <div class="col-sm-7">
            <input type="text"
                class="form-control @error('parent2_first_name') is-invalid @enderror"
                name="parent2_first_name"
                id="parent2_first_name"
                value="{{ old('parent2_first_name') }}">
            @error('parent2_first_name')
            <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>
    </div>

    <!-- Parent 2 Last Name (optional) -->
    <div class="mb-3 row">
        <label for="parent2_last_name" class="col-sm-3 col-form-label">Parent 2 Last Name (optional)</label>
        <div class="col-sm-7">
            <input type="text"
                class="form-control @error('parent2_last_name') is-invalid @enderror"
                name="parent2_last_name"
                id="parent2_last_name"
                value="{{ old('parent2_last_name') }}">
            @error('parent2_last_name')
            <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>
    </div>

    <!-- Parent 2 Email (optional) -->
    <div class="mb-3 row">
        <label for="parent2_email" class="col-sm-3 col-form-label">Parent 2 Email (optional)</label>
        <div class="col-sm-7">
            <input type="email"
                class="form-control @error('parent2_email') is-invalid @enderror"
                name="parent2_email"
                id="parent2_email"
                value="{{ old('parent2_email') }}">
            @error('parent2_email')
            <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>
    </div>

    <!-- Parent 2 Phone (optional) -->
    <div class="mb-3 row">
        <label for="parent2_phone" class="col-sm-3 col-form-label">Parent 2 Mobile (optional)</label>
        <div class="col-sm-2">
            <input type="text"
                class="form-control @error('parent2_phone') is-invalid @enderror"
                name="parent2_phone"
                id="parent2_phone"
                value="{{ old('parent2_phone') }}">
            @error('parent2_phone')
            <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>
    </div>

    <hr />

    <!-- ================= EMERGENCY CONTACT SECTION ================= -->
    <h3>Emergency Contact</h3>

    <!-- Emergency Contact Name -->
    <div class="mb-3 row">
        <label for="emergency_contact_name" class="col-sm-3 col-form-label">Emergency Contact Name</label>
        <div class="col-sm-7">
            <input type="text"
                class="form-control @error('emergency_contact_name') is-invalid @enderror"
                name="emergency_contact_name"
                id="emergency_contact_name"
                value="{{ old('emergency_contact_name') }}"
                required>
            @error('emergency_contact_name')
            <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>
    </div>

    <!-- Emergency Contact Phone -->
    <div class="mb-3 row">
        <label for="emergency_contact_phone" class="col-sm-3 col-form-label">Emergency Contact Phone</label>
        <div class="col-sm-2">
            <input type="text"
                class="form-control @error('emergency_contact_phone') is-invalid @enderror"
                name="emergency_contact_phone"
                id="emergency_contact_phone"
                value="{{ old('emergency_contact_phone') }}"
                required>
            @error('emergency_contact_phone')
            <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>
    </div>

    <!-- Relationship to Family -->
    <div class="mb-3 row">
        <label for="relationship_to_family" class="col-sm-3 col-form-label">Relationship to Family</label>
        <div class="col-sm-7">
            <input type="text"
                class="form-control @error('relationship_to_family') is-invalid @enderror"
                name="relationship_to_family"
                id="relationship_to_family"
                value="{{ old('relationship_to_family') }}"
                required>
            @error('relationship_to_family')
            <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>
    </div>

    <hr />

    <!-- ================= CHILDREN SECTION ================= -->
    <h3>Children</h3>
    <div id="children-container">
        <!-- The first child-block (index 0) -->
        <div class="child-block mb-4">
            <div class="mb-3 row">
                <label class="col-sm-3 col-form-label">Child First Name</label>
                <div class="col-sm-7">
                    <input type="text"
                        class="form-control @error('children.0.first_name') is-invalid @enderror"
                        name="children[0][first_name]"
                        value="{{ old('children.0.first_name') }}"
                        required>
                    @error('children.0.first_name')
                    <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-sm-3 col-form-label">Child Last Name</label>
                <div class="col-sm-7">
                    <input type="text"
                        class="form-control @error('children.0.last_name') is-invalid @enderror"
                        name="children[0][last_name]"
                        value="{{ old('children.0.last_name') }}"
                        required>
                    @error('children.0.last_name')
                    <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-sm-3 col-form-label">Gender</label>
                <div class="col-sm-2">
                    <select class="form-select @error('children.0.gender') is-invalid @enderror"
                        name="children[0][gender]"
                        required>
                        <option value="Male" {{ old('children.0.gender') == 'Male'   ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ old('children.0.gender') == 'Female' ? 'selected' : '' }}>Female</option>
                    </select>
                    @error('children.0.gender')
                    <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-sm-3 col-form-label">Date of Birth</label>
                <div class="col-sm-2">
                    <input type="date"
                        class="form-control @error('children.0.date_of_birth') is-invalid @enderror"
                        name="children[0][date_of_birth]"
                        value="{{ old('children.0.date_of_birth') }}"
                        required>
                    @error('children.0.date_of_birth')
                    <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-sm-3 col-form-label">Residency Status</label>
                <div class="col-sm-3">
                    <select class="form-select @error('children.0.residency_status') is-invalid @enderror"
                        name="children[0][residency_status]"
                        required>
                        <option value="Citizen"
                            {{ old('children.0.residency_status') == 'Citizen' ? 'selected' : '' }}>
                            Citizen
                        </option>
                        <option value="Permanent Resident"
                            {{ old('children.0.residency_status') == 'Permanent Resident' ? 'selected' : '' }}>
                            Permanent Resident
                        </option>
                        <option value="Temporary Resident"
                            {{ old('children.0.residency_status') == 'Temporary Resident' ? 'selected' : '' }}>
                            Temporary Resident
                        </option>
                    </select>
                    @error('children.0.residency_status')
                    <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-sm-3 col-form-label">Day School Name</label>
                <div class="col-sm-7">
                    <input type="text"
                        class="form-control @error('children.0.day_school_name') is-invalid @enderror"
                        name="children[0][day_school_name]"
                        value="{{ old('children.0.day_school_name') }}"
                        required>
                    @error('children.0.day_school_name')
                    <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-sm-3 col-form-label">Current School Grade in {{ date('Y') }}</label>
                <div class="col-sm-2">
                    <select class="form-select @error('children.0.day_school_year') is-invalid @enderror"
                        name="children[0][day_school_year]"
                        required>
                        <option value="Pre School"
                            {{ old('children.0.day_school_year') == 'Pre School' ? 'selected' : '' }}>
                            Pre School
                        </option>
                        <option value="Kindergarten"
                            {{ old('children.0.day_school_year') == 'Kindergarten' ? 'selected' : '' }}>
                            Kindergarten
                        </option>
                        <option value="Grade 1"
                            {{ old('children.0.day_school_year') == 'Grade 1' ? 'selected' : '' }}>
                            Grade 1
                        </option>
                        <option value="Grade 2"
                            {{ old('children.0.day_school_year') == 'Grade 2' ? 'selected' : '' }}>
                            Grade 2
                        </option>
                        <option value="Grade 3"
                            {{ old('children.0.day_school_year') == 'Grade 3' ? 'selected' : '' }}>
                            Grade 3
                        </option>
                        <option value="Grade 4"
                            {{ old('children.0.day_school_year') == 'Grade 4' ? 'selected' : '' }}>
                            Grade 4
                        </option>
                        <option value="Grade 5"
                            {{ old('children.0.day_school_year') == 'Grade 5' ? 'selected' : '' }}>
                            Grade 5
                        </option>
                        <option value="Grade 6"
                            {{ old('children.0.day_school_year') == 'Grade 6' ? 'selected' : '' }}>
                            Grade 6
                        </option>
                        <option value="Grade 7"
                            {{ old('children.0.day_school_year') == 'Grade 7' ? 'selected' : '' }}>
                            Grade 7
                        </option>
                        <option value="Grade 8"
                            {{ old('children.0.day_school_year') == 'Grade 8' ? 'selected' : '' }}>
                            Grade 8
                        </option>
                        <option value="Grade 9"
                            {{ old('children.0.day_school_year') == 'Grade 9' ? 'selected' : '' }}>
                            Grade 9
                        </option>
                        <option value="Grade 10"
                            {{ old('children.0.day_school_year') == 'Grade 10' ? 'selected' : '' }}>
                            Grade 10
                        </option>
                        <option value="Grade 11"
                            {{ old('children.0.day_school_year') == 'Grade 11' ? 'selected' : '' }}>
                            Grade 11
                        </option>
                        <option value="Grade 12"
                            {{ old('children.0.day_school_year') == 'Grade 12' ? 'selected' : '' }}>
                            Grade 12
                        </option>
                    </select>
                    @error('children.0.day_school_year')
                    <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-sm-3 col-form-label">Allergies</label>
                <div class="col-sm-7">
                    <input type="text"
                        class="form-control @error('children.0.allergies') is-invalid @enderror"
                        name="children[0][allergies]"
                        value="{{ old('children.0.allergies') }}">
                    @error('children.0.allergies')
                    <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-sm-3 col-form-label">Special Needs</label>
                <div class="col-sm-7">
                    <input type="text"
                        class="form-control @error('children.0.special_needs') is-invalid @enderror"
                        name="children[0][special_needs]"
                        value="{{ old('children.0.special_needs') }}">
                    @error('children.0.special_needs')
                    <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-sm-3 col-form-label">Dhamma Class Last Year (in {{ date('Y') - 1 }})</label>
                <div class="col-sm-3">
                    <select class="form-select @error('children.0.dhamma_class') is-invalid @enderror"
                        name="children[0][dhamma_class]"
                        required>
                        <option value="Did not attend last year"
                            {{ old('children.0.dhamma_class') == 'Did not attend last year' ? 'selected' : '' }}>
                            Did not attend last year
                        </option>
                        <option value="Class 1 (A)"
                            {{ old('children.0.dhamma_class') == 'Class 1 (A)' ? 'selected' : '' }}>
                            Class 1 (A)
                        </option>
                        <option value="Class 1 (B)"
                            {{ old('children.0.dhamma_class') == 'Class 1 (B)' ? 'selected' : '' }}>
                            Class 1 (B)
                        </option>
                        <option value="Class 2 (C)"
                            {{ old('children.0.dhamma_class') == 'Class 2 (C)' ? 'selected' : '' }}>
                            Class 2 (C)
                        </option>
                        <option value="Class 3 (D)"
                            {{ old('children.0.dhamma_class') == 'Class 3 (D)' ? 'selected' : '' }}>
                            Class 3 (D)
                        </option>
                        <option value="Class 4 (E)"
                            {{ old('children.0.dhamma_class') == 'Class 4 (E)' ? 'selected' : '' }}>
                            Class 4 (E)
                        </option>
                    </select>
                    @error('children.0.dhamma_class')
                    <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-sm-3 col-form-label">Sinhala Class Last Year (in {{ date('Y') - 1 }})</label>
                <div class="col-sm-3">
                    <select class="form-select @error('children.0.sinhala_class') is-invalid @enderror"
                        name="children[0][sinhala_class]"
                        required>
                        <option value="Did not attend last year"
                            {{ old('children.0.sinhala_class') == 'Did not attend last year' ? 'selected' : '' }}>
                            Did not attend last year
                        </option>
                        <option value="Class 1 (A)"
                            {{ old('children.0.sinhala_class') == 'Class 1 (A)' ? 'selected' : '' }}>
                            Class 1 (A)
                        </option>
                        <option value="Class 1 (B)"
                            {{ old('children.0.sinhala_class') == 'Class 1 (B)' ? 'selected' : '' }}>
                            Class 1 (B)
                        </option>
                        <option value="Class 2 (C)"
                            {{ old('children.0.sinhala_class') == 'Class 2 (C)' ? 'selected' : '' }}>
                            Class 2 (C)
                        </option>
                        <option value="Class 3 (D)"
                            {{ old('children.0.sinhala_class') == 'Class 3 (D)' ? 'selected' : '' }}>
                            Class 3 (D)
                        </option>
                        <option value="Class 4 (E)"
                            {{ old('children.0.sinhala_class') == 'Class 4 (E)' ? 'selected' : '' }}>
                            Class 4 (E)
                        </option>
                    </select>
                    @error('children.0.sinhala_class')
                    <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <div class="checkbox">
                <label>
                    <input type="checkbox" 
                    name="children[0][photography_allowed]" 
                    {{ old('photography_allowed') ? 'checked' : ''}}>
                    I consent to my child's photo appearing on the school website
                </label>
            </div>

            <hr />
        </div>
        <!-- End child-block (index=0) -->
    </div>
    <!-- End #children-container -->


    <h3>Other Information</h3>

    <!-- Postcode (optional) -->
    <div class="mb-3 row">
        <label for="postcode" class="col-sm-3 col-form-label">Postcode (optional)</label>
        <div class="col-sm-2">
            <input type="text"
                class="form-control @error('postcode') is-invalid @enderror"
                name="postcode"
                id="postcode"
                value="{{ old('postcode') }}">
            @error('postcode')
            <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>
    </div>

    <!-- Guidelines (optional) -->
    <div class="checkbox">
        <label>
            <input type="checkbox" 
            name="guidelines_accepted" 
            {{ old('guidelines_accepted') ? 'checked' : ''}} required>
            I accept the school <a href="/guidelines" target="_blank">guidelines</a>
        </label>
    </div>

    <hr />    

    <!-- Buttons to dynamically manage children blocks -->
    <button type="button" class="btn btn-outline-primary mb-3" id="addChildBtn">
        + Add Another Child
    </button>
    <button type="button" class="btn btn-outline-danger mb-3" id="removeChildBtn">
        - Remove Last Child
    </button>

    <button type="submit" class="btn btn-primary mb-3">Register</button>
</form>

{{-- The add/remove-child behaviour lives in resources/js/registration.js
     (initRegistrationForm), bundled via @vite in the layout and unit-tested
     in tests/js/registration.test.js. --}}
@endsection
