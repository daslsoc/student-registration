@extends('layouts.app')

@section('title', 'Help — Running the school office')

@section('content')
<div class="row justify-content-center">
  <div class="col-lg-9">

    <h1 class="mb-1">Admin Help &amp; Guide</h1>
    <p class="text-muted">A guide to every page in the admin area: the family lists, the medical list, class placement, recording payments taken in person, and importing or exporting data. Parents don't need any of this — they use the public <a href="{{ route('help') }}">Help page</a>.</p>

    {{-- Quick jump links --}}
    <div class="card bg-body-tertiary mb-4">
      <div class="card-body">
        <h2 class="h6 text-uppercase text-muted mb-3">On this page</h2>
        <ol class="mb-0">
          <li><a href="#login">Logging in</a></li>
          <li><a href="#list">Parents &amp; Child List</a></li>
          <li><a href="#allergies">Allergies &amp; Medical</a></li>
          <li><a href="#placement">How class placement works</a></li>
          <li><a href="#unallocated">Unallocated Students</a></li>
          <li><a href="#relocation">Class Relocation</a></li>
          <li><a href="#override">Payment Override</a></li>
          <li><a href="#import">Import &amp; Export CSV</a></li>
        </ol>
      </div>
    </div>

    {{-- 1. Login --}}
    <section id="login" class="mb-5">
      <h2 class="h3">1. Logging in</h2>
      <p>Admin pages are private. Click <strong>Login</strong> (top-right) and sign in with your staff email and password. There is <strong>no self-sign-up</strong> — staff accounts are created for you by whoever runs the system. Once you're in, an <strong>Admin</strong> menu appears in the top bar; click <strong>Logout</strong> when you're finished.</p>
      <figure class="figure w-100">
        <img src="/images/help/admin/login.png" class="figure-img img-fluid rounded border shadow-sm" alt="The admin login page with email and password boxes.">
        <figcaption class="figure-caption">The login page — administrators only.</figcaption>
      </figure>
    </section>

    {{-- 2. Parents & Child List --}}
    <section id="list" class="mb-5">
      <h2 class="h3">2. Parents &amp; Child List</h2>
      <p><strong>Admin → Parents &amp; Child List</strong> is your master list: one row per child with the family's full details, allocated class, allergies, both parents' contacts, and the emergency contact.</p>
      <figure class="figure w-100">
        <img src="/images/help/admin/parents-students.png" class="figure-img img-fluid rounded border shadow-sm" alt="The Parents and Child List table with one row per child.">
        <figcaption class="figure-caption">One row per child. A <strong>Completed</strong> row means the family has paid; <strong>Pending</strong> means not yet.</figcaption>
      </figure>
      <ul>
        <li>Use the <strong>Registration status</strong> filter to show <strong>Completed only</strong>, <strong>Pending only</strong>, or both.</li>
        <li>The <strong>Search</strong> box filters by any name, email, or number instantly.</li>
        <li><strong>Export CSV</strong> (top-right) downloads the whole list — see <a href="#import">Import &amp; Export CSV</a>.</li>
      </ul>
    </section>

    {{-- 3. Allergies & Medical --}}
    <section id="allergies" class="mb-5">
      <h2 class="h3">3. Allergies &amp; Medical</h2>
      <p><strong>Admin → Allergies &amp; Medical</strong> is a focused list of only the children who have something medical recorded — anything other than "None". It's a quick reference for teachers on the day.</p>
      <figure class="figure w-100">
        <img src="/images/help/admin/allergies.png" class="figure-img img-fluid rounded border shadow-sm" alt="The Allergies and Medical list showing children with an allergy or special need, their class, and contacts.">
        <figcaption class="figure-caption">Only children with a real allergy (shown in red) or special need appear here, with who to contact.</figcaption>
      </figure>
    </section>

    {{-- 4. Placement rule --}}
    <section id="placement" class="mb-5">
      <h2 class="h3">4. How class placement works</h2>
      <p>When a family pays, each child is automatically placed into a class based on their day-school grade. The same class is used for both Buddhism and Sinhala to start with; you can change either one later. The parent is emailed their child's class as part of the confirmation.</p>
      <div class="table-responsive">
        <table class="table table-sm table-bordered w-auto">
          <thead><tr><th>Day-school grade</th><th>Class</th></tr></thead>
          <tbody>
            <tr><td>Pre School, Kindergarten, Grade 1</td><td>Class A</td></tr>
            <tr><td>Grade 2</td><td>Class B</td></tr>
            <tr><td>Grade 3, Grade 4</td><td>Class C</td></tr>
            <tr><td>Grade 5, Grade 6</td><td>Class D</td></tr>
            <tr><td>Grade 7–12</td><td>Class E</td></tr>
          </tbody>
        </table>
      </div>
      <p>If a child's grade doesn't fit the table, they're left <strong>unallocated</strong> for you to place by hand — that's the next page.</p>
    </section>

    {{-- 5. Unallocated --}}
    <section id="unallocated" class="mb-5">
      <h2 class="h3">5. Unallocated Students</h2>
      <p><strong>Admin → Unallocated Students</strong> is a worklist of <strong>paid</strong> students who still don't have a class for at least one subject — usually because their grade wasn't in the table above.</p>
      <figure class="figure w-100">
        <img src="/images/help/admin/unallocated.png" class="figure-img img-fluid rounded border shadow-sm" alt="The Unallocated Students worklist with Buddhism and Sinhala class dropdowns per student.">
        <figcaption class="figure-caption">Pick a class for each subject, then <strong>Save allocations</strong>. When empty, everyone paid has a class.</figcaption>
      </figure>
      <div class="alert alert-info">This page is only for students with <strong>no</strong> class yet. To move a student who already has one, use <strong>Class Relocation</strong>.</div>
    </section>

    {{-- 6. Class Relocation --}}
    <section id="relocation" class="mb-5">
      <h2 class="h3">6. Class Relocation</h2>
      <p><strong>Admin → Class Relocation</strong> moves a student who is <strong>already placed</strong> into a different class.</p>
      <figure class="figure w-100">
        <img src="/images/help/admin/class-relocation.png" class="figure-img img-fluid rounded border shadow-sm" alt="The Class Relocation page: a search box and a result row with Buddhism and Sinhala class dropdowns.">
        <figcaption class="figure-caption">Search by name or student number, change the class, and Save allocations.</figcaption>
      </figure>
      <ol>
        <li>Type part of the student's <strong>name</strong> or their <strong>student number</strong> and click <strong>Search</strong>.</li>
        <li>Change their <strong>Buddhism</strong> and/or <strong>Sinhala</strong> class with the dropdowns.</li>
        <li>Click <strong>Save allocations</strong>.</li>
      </ol>
      <div class="alert alert-success"><strong>Parents are emailed automatically</strong> whenever a class actually changes, so they always know where their child should be. Saving with no change sends no email.</div>
    </section>

    {{-- 7. Payment Override --}}
    <section id="override" class="mb-5">
      <h2 class="h3">7. Payment Override</h2>
      <p><strong>Admin → Payment Override</strong> is for payments that don't come through the website — a family pays <strong>cash</strong> or by <strong>EFTPOS</strong> at the desk, or you <strong>waive</strong> the fee. You can also <strong>revert</strong> a payment recorded by mistake.</p>
      <figure class="figure w-100">
        <img src="/images/help/admin/payment-override.png" class="figure-img img-fluid rounded border shadow-sm" alt="The Payment Override page with a family selector, action radios, method, and an audit-logged families table.">
        <figcaption class="figure-caption">Record a desk payment or waive a fee. Every change is audit-logged below.</figcaption>
      </figure>
      <p><strong>To record a payment:</strong> choose the <strong>Family</strong>, leave <strong>Action</strong> on <strong>Mark as paid</strong>, pick the <strong>Method</strong> (Cash, EFTPOS, or Waived — which completes at $0 for hardship or newly-arrived families), optionally add an <strong>Amount</strong> and <strong>Note</strong>, then click <strong>Apply &amp; log</strong>. The family is marked <strong>Completed</strong> and their children are placed into classes, exactly as if they'd paid online.</p>
      <div class="alert alert-warning">
        <strong>Reverting</strong> a payment sets the family back to <strong>Pending</strong>, removes the recorded payment, <strong>and clears their class placement</strong> — which also removes the students from the attendance system on its next sync. Use it to undo a mistake or a refund.
      </div>
      <p>The <strong>Recent overrides</strong> table is an audit log of every manual change — who did it, when, and any note. These records can't be edited or deleted, so there's always a clear history.</p>
    </section>

    {{-- 8. CSV --}}
    <section id="import" class="mb-5">
      <h2 class="h3">8. Import &amp; Export CSV</h2>
      <p><strong>Admin → Import CSV</strong> bulk-loads families from a spreadsheet — handy for carrying over last year's families or a batch entered offline.</p>
      <figure class="figure w-100">
        <img src="/images/help/admin/import-csv.png" class="figure-img img-fluid rounded border shadow-sm" alt="The Import CSV page with a file chooser and a default registration year field.">
        <figcaption class="figure-caption">Choose a CSV file, set the default registration year, and Import.</figcaption>
      </figure>
      <p>Your CSV needs a <strong>header row</strong>: the parent columns first —</p>
      <pre class="bg-body-tertiary p-3 rounded small"><code>Parent1FirstName, Parent1LastName, Parent1Email, Parent1Phone,
Parent2FirstName, Parent2LastName, Parent2Email, Parent2Phone,
EmergencyContactName, EmergencyContactPhone, RelationshipToFamily, Postcode</code></pre>
      <p>— then up to <strong>four</strong> children, numbered 1–4. For each child <code>N</code>:</p>
      <pre class="bg-body-tertiary p-3 rounded small"><code>ChildNFirstName, ChildNLastName, ChildNGender, ChildNDateOfBirth,
ChildNResidencyStatus, ChildNDaySchoolName, ChildNDaySchoolYear,
ChildNAllergies, ChildNSpecialNeeds, ChildNStudentNumber, ChildNPhotographyAllowed</code></pre>
      <p>A child is only created if their <strong>first name</strong> column has a value, so you can leave unused child columns blank. The easiest way to get the column names exactly right is to <strong>Export CSV</strong> first (the button on the Parents &amp; Child List page) and use that file as your template.</p>
      <div class="alert alert-info">
        Imported families are <strong>not</strong> charged and are <strong>not</strong> auto-placed into classes — use <a href="#unallocated">Unallocated Students</a> or <a href="#relocation">Class Relocation</a> to place them, and <a href="#override">Payment Override</a> if you need to mark them paid.
      </div>
    </section>

    <hr>
    <p class="text-muted">Need to add a staff login, run a report, or reset the year? Those are one-off jobs for whoever runs the system — see the operations notes in the project's <code>docs/</code> folder.</p>

  </div>
</div>
@endsection
