<!--
    A form that displays existing children in a loop, allows adding new children,
    and includes an "X" button for each child block to remove it individually.

    Requirements:
    - A `$parent` variable passed to this view, containing an Eloquent relationship:
      $parent->children
    - A route for POST/PUT submission, e.g., route('registration.update.submit', ['token' => $parent->update_token])
    - On the server side, if a child block is removed from the DOM, that child
      won't appear in the request. So your controller logic might remove that child
      from the DB if it's missing in the updated array.
-->

@extends('layouts.app')

@section('title', 'Update Registration')

@section('content')
<h1>Update Registration Details</h1>

<p>Please review and update your family's registration information below. Ensure all details are accurate to help us provide the best experience for your child(ren).</p>

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

<form method="POST" action="{{ route('registration.update.submit', ['token' => $parent->update_token]) }}">
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
                value="{{ old('parent1_first_name', $parent->parent1_first_name) }}"
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
                value="{{ old('parent1_last_name', $parent->parent1_last_name) }}"
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
                value="{{ old('parent1_email', $parent->parent1_email) }}"
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
                value="{{ old('parent1_phone', $parent->parent1_phone) }}"
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
                value="{{ old('parent2_first_name', $parent->parent2_first_name) }}">
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
                value="{{ old('parent2_last_name', $parent->parent2_last_name) }}">
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
                value="{{ old('parent2_email', $parent->parent2_email) }}">
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
                value="{{ old('parent2_phone', $parent->parent2_phone) }}">
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
                value="{{ old('emergency_contact_name', $parent->emergency_contact_name) }}"
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
                value="{{ old('emergency_contact_phone', $parent->emergency_contact_phone) }}"
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
                value="{{ old('relationship_to_family', $parent->relationship_to_family) }}"
                required>
            @error('relationship_to_family')
            <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>
    </div>

    <hr />

    <!-- ================= CHILDREN SECTION ================= -->
    <h3>Children</h3>
    <!-- We'll display existing children, then allow adding new ones dynamically. -->
    <div id="children-container">
        @php
            // Track the highest existing index so new children can continue from there
            $lastIndex = 0;
        @endphp

        @foreach($parent->children as $index => $child)
            <div class="child-block mb-4 border p-3 position-relative">
                <!-- Hidden ID so the server knows which child to update -->
                <input type="hidden" name="children[{{ $index }}][id]" value="{{ $child->id }}">

                <!-- 'Remove' button in top-right corner -->
                <button type="button"
                        class="btn btn-sm btn-danger remove-child"
                        style="position: absolute; top: 5px; right: 5px;">
                    X
                </button>

                <!-- Allocated class for the current year. Read-only — shown for
                     visibility only; allocations are managed by the school.
                     mt-4 keeps it clear of the absolutely-positioned X button. -->
                <div class="alert alert-info py-2 mt-4 allocated-info">
                    <strong>Allocated class for {{ date('Y') }}</strong>
                    (set by the school — shown for your information):<br>
                    Buddhism: {{ $child->allocated_dhamma_class ?? 'To be advised' }}
                    &middot;
                    Sinhala: {{ $child->allocated_sinhala_class ?? 'To be advised' }}
                </div>

                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label">Child First Name</label>
                    <div class="col-sm-7">
                        <input type="text"
                                class="form-control @error('children.'.$index.'.first_name') is-invalid @enderror"
                                name="children[{{ $index }}][first_name]"
                                value="{{ old('children.'.$index.'.first_name', $child->first_name) }}"
                                required>
                        @error('children.'.$index.'.first_name')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
                
                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label">Child Last Name</label>
                    <div class="col-sm-7">
                        <input type="text"
                                class="form-control @error('children.'.$index.'.last_name') is-invalid @enderror"
                                name="children[{{ $index }}][last_name]"
                                value="{{ old('children.'.$index.'.last_name', $child->last_name) }}"
                                required>
                        @error('children.'.$index.'.last_name')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label">Gender</label>
                    <div class="col-sm-2">
                        <select class="form-select @error('children.'.$index.'.gender') is-invalid @enderror"
                                name="children[{{ $index }}][gender]"
                                required>
                            <option value="Male"   {{ old('children.'.$index.'.gender', $child->gender) == 'Male'   ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('children.'.$index.'.gender', $child->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                        </select>
                        @error('children.'.$index.'.gender')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label">Date of Birth</label>
                    <div class="col-sm-2">
                        <input type="date"
                                class="form-control @error('children.'.$index.'.date_of_birth') is-invalid @enderror"
                                name="children[{{ $index }}][date_of_birth]"
                                value="{{ old('children.'.$index.'.date_of_birth', $child->date_of_birth) }}"
                                required>
                        @error('children.'.$index.'.date_of_birth')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label">Residency Status</label>
                    <div class="col-sm-3">
                        <select class="form-select @error('children.'.$index.'.residency_status') is-invalid @enderror"
                                name="children[{{ $index }}][residency_status]"
                                required>
                            <option value="Citizen"
                                {{ old('children.'.$index.'.residency_status', $child->residency_status) == 'Citizen' ? 'selected' : '' }}>
                                Citizen
                            </option>
                            <option value="Permanent Resident"
                                {{ old('children.'.$index.'.residency_status', $child->residency_status) == 'Permanent Resident' ? 'selected' : '' }}>
                                Permanent Resident
                            </option>
                            <option value="Temporary Resident"
                                {{ old('children.'.$index.'.residency_status', $child->residency_status) == 'Temporary Resident' ? 'selected' : '' }}>
                                Temporary Resident
                            </option>
                        </select>
                        @error('children.'.$index.'.residency_status')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
                
                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label">Day School Name</label>
                    <div class="col-sm-7">
                        <input type="text"
                                class="form-control @error('children.'.$index.'.day_school_name') is-invalid @enderror"
                                name="children[{{ $index }}][day_school_name]"
                                value="{{ old('children.'.$index.'.day_school_name', $child->day_school_name) }}"
                                required>
                        @error('children.'.$index.'.day_school_name')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label">Current School Grade in {{ date('Y') }}</label>
                    <div class="col-sm-2">
                        <select class="form-select @error('children.0.day_school_year') is-invalid @enderror"
                            name="children[{{ $index }}][day_school_year]"
                            required>
                            <option value="Pre School"
                                {{ old('children.'.$index.'.day_school_year', $child->day_school_year) == 'Pre School' ? 'selected' : '' }}>
                                Pre School
                            </option>
                            <option value="Kindergarten"
                                {{ old('children.'.$index.'.day_school_year', $child->day_school_year) == 'Kindergarten' ? 'selected' : '' }}>
                                Kindergarten
                            </option>
                            <option value="Grade 1"
                                {{ old('children.'.$index.'.day_school_year', $child->day_school_year) == 'Grade 1' ? 'selected' : '' }}>
                                Grade 1
                            </option>
                            <option value="Grade 2"
                                {{ old('children.'.$index.'.day_school_year', $child->day_school_year) == 'Grade 2' ? 'selected' : '' }}>
                                Grade 2
                            </option>
                            <option value="Grade 3"
                                {{ old('children.'.$index.'.day_school_year', $child->day_school_year) == 'Grade 3' ? 'selected' : '' }}>
                                Grade 3
                            </option>
                            <option value="Grade 4"
                                {{ old('children.'.$index.'.day_school_year', $child->day_school_year) == 'Grade 4' ? 'selected' : '' }}>
                                Grade 4
                            </option>
                            <option value="Grade 5"
                                {{ old('children.'.$index.'.day_school_year', $child->day_school_year) == 'Grade 5' ? 'selected' : '' }}>
                                Grade 5
                            </option>
                            <option value="Grade 6"
                                {{ old('children.'.$index.'.day_school_year', $child->day_school_year) == 'Grade 6' ? 'selected' : '' }}>
                                Grade 6
                            </option>
                            <option value="Grade 7"
                                {{ old('children.'.$index.'.day_school_year', $child->day_school_year) == 'Grade 7' ? 'selected' : '' }}>
                                Grade 7
                            </option>
                            <option value="Grade 8"
                                {{ old('children.'.$index.'.day_school_year', $child->day_school_year) == 'Grade 8' ? 'selected' : '' }}>
                                Grade 8
                            </option>
                            <option value="Grade 9"
                                {{ old('children.'.$index.'.day_school_year', $child->day_school_year) == 'Grade 9' ? 'selected' : '' }}>
                                Grade 9
                            </option>
                            <option value="Grade 10"
                                {{ old('children.'.$index.'.day_school_year', $child->day_school_year) == 'Grade 10' ? 'selected' : '' }}>
                                Grade 10
                            </option>
                            <option value="Grade 11"
                                {{ old('children.'.$index.'.day_school_year', $child->day_school_year) == 'Grade 11' ? 'selected' : '' }}>
                                Grade 11
                            </option>
                            <option value="Grade 12"
                                {{ old('children.'.$index.'.day_school_year', $child->day_school_year) == 'Grade 12' ? 'selected' : '' }}>
                                Grade 12
                            </option>
                        </select>                        
                        @error('children.'.$index.'.day_school_year')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label">Allergies</label>
                    <div class="col-sm-7">
                        <input type="text"
                                class="form-control @error('children.'.$index.'.allergies') is-invalid @enderror"
                                name="children[{{ $index }}][allergies]"
                                value="{{ old('children.'.$index.'.allergies', $child->allergies) }}">
                        @error('children.'.$index.'.allergies')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label">Special Needs</label>
                    <div class="col-sm-7">
                        <input type="text"
                                class="form-control @error('children.'.$index.'.special_needs') is-invalid @enderror"
                                name="children[{{ $index }}][special_needs]"
                                value="{{ old('children.'.$index.'.special_needs', $child->special_needs) }}">
                        @error('children.'.$index.'.special_needs')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label">Year First Registered</label>
                    <div class="col-sm-2">
                        <input type="text"
                                class="form-control @error('children.'.$index.'.year_of_first_registration') is-invalid @enderror"
                                name="children[{{ $index }}][year_of_first_registration]"
                                value="{{ old('children.'.$index.'.year_of_first_registration', $child->year_of_first_registration) }}">
                        @error('children.'.$index.'.year_of_first_registration')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="checkbox">
                    <label>
                        <input type="checkbox"
                        name="children[{{ $index }}][photography_allowed]" 
                        @checked($child->photography_allowed)>
                        I consent to my child's photo appearing on the school website
                    </label>
                </div>
            
                <hr />
            </div>
            @php $lastIndex = $index; @endphp
        @endforeach
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
                value="{{ old('postcode', $parent->postcode) }}">
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
            @checked($parent->guidelines_accepted) required>
            I accept the school <a href="/guidelines" target="_blank">guidelines</a>
        </label>
    </div>

    <hr />       

    <!-- Buttons to dynamically add new child blocks -->
    <button type="button" class="btn btn-outline-primary mb-3" id="addChildBtn">+ Add Another Child</button>

    <button type="submit" class="btn btn-success mb-3">Update Registration</button>
</form>

<!-- JavaScript for dynamic child addition and removal -->
<script>
    // We'll set childIndex to the last existing child's index + 1
    let childIndex = {{ $lastIndex }} + 1;

    const addChildBtn = document.getElementById('addChildBtn');
    const childrenContainer = document.getElementById('children-container');

    // 1) Add new child block
    addChildBtn.addEventListener('click', () => {
        // If there's at least one child-block, clone the last one
        // If there are none, you can decide how to handle (rare for 'update').
        const childBlocks = document.querySelectorAll('.child-block');
        let newBlock;
        if (childBlocks.length > 0) {
            // Clone the last child-block
            const template = childBlocks[childBlocks.length - 1];
            newBlock = template.cloneNode(true);

            // Remove any hidden 'id' field, because new child = no existing ID
            const hiddenId = newBlock.querySelector('input[type=hidden][name^=\"children\"]');
            if (hiddenId) {
                hiddenId.remove();
            }

            // A new child has no allocation yet — drop the read-only info box.
            const allocatedInfo = newBlock.querySelector('.allocated-info');
            if (allocatedInfo) {
                allocatedInfo.remove();
            }
        } else {
            // Alternatively, create a fresh block from scratch. For brevity,
            // we'll do a minimal approach here.
            newBlock = document.createElement('div');
            newBlock.classList.add('child-block', 'mb-4', 'border', 'p-3', 'position-relative');
            newBlock.innerHTML = `
                <button type=\"button\" class=\"btn btn-sm btn-danger remove-child\" style=\"position: absolute; top: 5px; right: 5px;\">X</button>
                <div class=\"row mb-3\">
                    <div class=\"col-md-6\">
                        <label class=\"form-label\">Child First Name</label>
                        <input type=\"text\" class=\"form-control\" name=\"children[${childIndex}][first_name]\" required>
                    </div>
                    <div class=\"col-md-6\">
                        <label class=\"form-label\">Child Last Name</label>
                        <input type=\"text\" class=\"form-control\" name=\"children[${childIndex}][last_name]\" required>
                    </div>
                </div>
                <!-- ... Add additional fields here if needed ... -->
                <hr />
            `;
        }

        // For the cloned version: rename all name attributes from children[index] to children[childIndex]
        const inputs = newBlock.querySelectorAll('input, select, textarea');
        inputs.forEach(el => {
            let oldName = el.getAttribute('name');
            // e.g. children[2][first_name]
            // We'll replace the bracketed index with our new childIndex
            let bracketIndex = oldName.match(/\[\d+\]/);
            if (bracketIndex) {
                                    let newName = oldName.replace(bracketIndex[0], `[${childIndex}]`);
                el.setAttribute('name', newName);
            }
            // Clear any is-invalid classes or old values
            el.classList.remove('is-invalid');
            if (el.tagName !== 'SELECT') el.value = '';
            // Default allergies / special needs to "None" for a new child.
            if (oldName && (oldName.includes('[allergies]') || oldName.includes('[special_needs]'))) {
                el.value = 'None';
            }
        });

        childrenContainer.appendChild(newBlock);
        childIndex++;
    });

    // 2) Remove any child block on "X" button click
    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('remove-child')) {
            // .remove-child is the "X" button in each block
            let block = e.target.closest('.child-block');
            if (block) {
                block.remove();
            }
        }
    });
</script>
@endsection
