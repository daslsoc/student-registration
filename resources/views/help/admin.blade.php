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
          <li><a href="#users">Users — adding &amp; removing staff</a></li>
          <li><a href="#roles">Roles &amp; permissions</a></li>
          <li><a href="#audit">Audit log</a></li>
        </ol>
      </div>
    </div>

    {{-- 1. Login --}}
    <section id="login" class="mb-5">
      <h2 class="h3">1. Logging in</h2>
      <p>Admin pages are private. Click <strong>Login</strong> (top-right) and sign in with your staff email and password. There is <strong>no self-sign-up</strong> — staff accounts are created by an administrator on the <a href="#users">Users</a> page. Once you're in, an <strong>Admin</strong> menu appears in the top bar; click <strong>Logout</strong> when you're finished.</p>
      <p><strong>Forgotten your password?</strong> Click <em>Forgot your password?</em> under the login form and enter your email. A link arrives by email that lets you set a new one; it works <strong>once</strong> and expires after <strong>60 minutes</strong>. If nothing arrives, check your junk folder and confirm the address on your account with an administrator — for safety the page says the same thing whether or not the address is registered, so it can't be used to fish for staff emails.</p>
      <p>What you see in the <strong>Admin</strong> menu depends on your <a href="#roles">role</a>. If a page you expect is missing, your role doesn't include it — ask an administrator.</p>
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

    {{-- 9. Users --}}
    <section id="users" class="mb-5">
      <h2 class="h3">9. Users — adding &amp; removing staff</h2>
      <p><strong>Admin → Users</strong> lists everyone who can sign in, with their role and whether the account is active. You only see this page if your role includes <em>Add, edit &amp; deactivate users</em>.</p>
      <p><strong>To add someone:</strong> click <strong>Add user</strong>, enter their name and email, set an initial password, and choose a <a href="#roles">role</a>. Hand them the password — they can change it themselves any time with <em>Forgot your password?</em> on the login page.</p>
      <p><strong>To remove someone:</strong> click <strong>Deactivate</strong> on their row. They're signed out immediately, can no longer log in, and can't reset their password. Their account is <strong>not</strong> deleted, on purpose: the audit log needs to keep naming a real person. If it was a mistake, <strong>Reactivate</strong> puts them straight back.</p>
      <div class="alert alert-warning">
        Two things the system won't let you do, to stop the school being locked out of its own records: you can't deactivate <strong>your own</strong> account, and you can't remove the <strong>last</strong> account that can manage users. Give someone else that permission first.
      </div>
      <p>To move someone between roles — say a helper becomes a registrar — click <strong>Edit</strong> and change the role there. Renames and role changes are recorded in the <a href="#audit">audit log</a> as separate entries.</p>
    </section>

    {{-- 10. Roles --}}
    <section id="roles" class="mb-5">
      <h2 class="h3">10. Roles &amp; permissions</h2>
      <p>A <strong>role</strong> is a named set of permissions, and every user holds exactly one. Change a role and everyone in it changes with it, immediately — nobody has to log out and back in.</p>
      <p>Three roles come set up:</p>
      <ul>
        <li><strong>Administrator</strong> — everything, including users, roles and the audit log.</li>
        <li><strong>Registrar</strong> — the day-to-day work: the lists, allocations, payment overrides, import and export. No user or role management.</li>
        <li><strong>Read-only</strong> — can look at registrations and export them, but change nothing.</li>
      </ul>
      <p><strong>Admin → Roles &amp; Permissions</strong> shows each role, how many permissions it carries, and how many people hold it (click the number to see them). <strong>Edit</strong> opens a tick-box grid grouped by area — Registrations, Class allocations, Payments, Administration — where you turn each permission on or off. <strong>Add role</strong> creates a new one from scratch if none of the three fit.</p>
      <p>A role can only be deleted once nobody holds it, so move its members somewhere else first. And, as with users, the system refuses an edit that would leave nobody able to manage users.</p>
    </section>

    {{-- 11. Audit log --}}
    <section id="audit" class="mb-5">
      <h2 class="h3">11. Audit log</h2>
      <p><strong>Admin → Audit Log</strong> records every account and permission change: who added a user, who deactivated one, who moved somebody between roles, and exactly which permissions a role gained or lost. Each entry shows when it happened, who did it, and the before/after detail.</p>
      <p>The log is append-only — there's no way to edit or delete an entry from inside the app, which is what makes it worth having. Filter by action if you're looking for something specific.</p>
    </section>

    <hr>
    <p class="text-muted">Need to run a report or reset the year? Those are one-off jobs for whoever runs the system — see the operations notes in the project's <code>docs/</code> folder.</p>

  </div>
</div>
@endsection
