@extends('layouts.app')

@section('title', 'Help — Registering and paying')

@section('content')
<div class="row justify-content-center">
  <div class="col-lg-9">

    <h1 class="mb-1">Help &amp; Guide</h1>
    <p class="text-muted">A step-by-step guide to enrolling your child, paying, and updating your details. You don't need a password — everything is done from the buttons on the home page and a secure link we email you.</p>

    {{-- Quick jump links --}}
    <div class="card bg-body-tertiary mb-4">
      <div class="card-body">
        <h2 class="h6 text-uppercase text-muted mb-3">On this page</h2>
        <ol class="mb-0">
          <li><a href="#before">Before you start</a></li>
          <li><a href="#register">Register a new family</a></li>
          <li><a href="#already">"You're already registered"</a></li>
          <li><a href="#retrieve">Get your update link</a></li>
          <li><a href="#update">Update your details</a></li>
          <li><a href="#cost">What it costs</a></li>
        </ol>
      </div>
    </div>

    {{-- 1. Before you start --}}
    <section id="before" class="mb-5">
      <h2 class="h3">1. Before you start</h2>
      <p>Open the school's registration website and you'll land on the home page. There are two choices:</p>
      <ul>
        <li><strong>Register New Family</strong> — you're enrolling for the first time.</li>
        <li><strong>Update Existing Registration</strong> — you've registered before and want to renew or change your details.</li>
      </ul>
      <figure class="figure w-100">
        <img src="/images/help/parent/landing.png" class="figure-img img-fluid rounded border shadow-sm" alt="The home page with Register New Family and Update Existing Registration buttons.">
        <figcaption class="figure-caption">The home page — start a new registration or update an existing one.</figcaption>
      </figure>
      <p>Before registering, please read the <strong>Guidelines</strong>. They explain the timetable, what's expected of students and parents, and how classes are grouped. You'll be asked to tick a box confirming you've read them.</p>
      <figure class="figure w-100">
        <img src="/images/help/parent/guidelines.png" class="figure-img img-fluid rounded border shadow-sm" alt="The Guidelines page listing school policies and the timetable.">
        <figcaption class="figure-caption">The Guidelines page — please read it before you register.</figcaption>
      </figure>
    </section>

    {{-- 2. Register --}}
    <section id="register" class="mb-5">
      <h2 class="h3">2. Register a new family</h2>
      <p>Click <strong>Register New Family</strong> on the home page. The form takes about five minutes and ends with an online card payment.</p>
      <figure class="figure w-100">
        <img src="/images/help/parent/register.png" class="figure-img img-fluid rounded border shadow-sm" alt="The top of the registration form showing the Parent / Guardian Information fields.">
        <figcaption class="figure-caption">The registration form starts with your parent / guardian details.</figcaption>
      </figure>

      <h3 class="h5 mt-4">Step 1 — Your details</h3>
      <ul>
        <li><strong>Parent 1</strong> — first name, last name, email, and mobile are all required. This is the email we use for your confirmation and any future update links, so make sure it's correct.</li>
        <li><strong>Parent 2</strong> is optional. If you add a Parent 2 email, they'll get the emails too.</li>
        <li><strong>Emergency Contact</strong> — a name, phone number, and their relationship to your family.</li>
      </ul>

      <h3 class="h5 mt-4">Step 2 — Add your children</h3>
      <p>Scroll down to the <strong>Children</strong> section. For each child you enter their name, gender, date of birth, residency status, day-school name, and current school grade.</p>
      <figure class="figure w-100">
        <img src="/images/help/parent/register-child.png" class="figure-img img-fluid rounded border shadow-sm" alt="The fields for one child, with Allergies and Special Needs pre-filled with the word None.">
        <figcaption class="figure-caption">Allergies and Special Needs start as "None" — replace them only if there's something to tell us.</figcaption>
      </figure>
      <ul>
        <li><strong>Allergies</strong> and <strong>Special Needs</strong> start as <strong>None</strong>. If your child has an allergy or special need we should know about, replace "None" with the details. Otherwise leave it as <strong>None</strong>.</li>
        <li>Tick the <strong>photography consent</strong> box if you're happy for your child's photo to appear on the school website.</li>
      </ul>
      <p>To enrol more than one child, click <strong>+ Add Another Child</strong> at the bottom — a new blank child section appears. <strong>- Remove Last Child</strong> removes the most recently added one.</p>

      <h3 class="h5 mt-4">Step 3 — Accept and pay</h3>
      <p>Tick <strong>I accept the school guidelines</strong> and click <strong>Register</strong>. You're taken to a secure <strong>Stripe</strong> page to pay by card — the school never sees or stores your card details. When the payment succeeds you're brought back to a <strong>Registration Complete</strong> page.</p>
      <div class="alert alert-info">
        <strong>Check your email.</strong> We email a confirmation to Parent 1 (and Parent 2, if added). It includes the <strong>class your child has been placed in</strong> for the year — keep it as a handy record. If it hasn't arrived after a few minutes, check your spam folder, then contact the school.
      </div>
    </section>

    {{-- 3. Already registered --}}
    <section id="already" class="mb-5">
      <h2 class="h3">3. "It looks like you're already registered"</h2>
      <p>If you start a new registration with an email we already have on file, we won't create a duplicate. Instead we send you a secure link to update your existing registration and show a message explaining this. Just check your email and follow the link — see <a href="#update">Update your details</a> below.</p>
    </section>

    {{-- 4. Retrieve --}}
    <section id="retrieve" class="mb-5">
      <h2 class="h3">4. Get your update link</h2>
      <p>To renew for a new year or change anything, you need a secure update link. You don't have a password — we email you a link each time.</p>
      <ol>
        <li>On the home page, click <strong>Update Existing Registration</strong>.</li>
        <li>Enter the email you registered with (Parent 1 <strong>or</strong> Parent 2 works).</li>
        <li>Click <strong>Retrieve</strong>. We email you a secure link.</li>
      </ol>
      <figure class="figure w-100">
        <img src="/images/help/parent/retrieve.png" class="figure-img img-fluid rounded border shadow-sm" alt="The retrieve page with a single email box and a Retrieve button.">
        <figcaption class="figure-caption">Enter your email and we'll send you a secure update link.</figcaption>
      </figure>
      <div class="alert alert-warning">
        The link is <strong>temporary</strong> — it expires after a few hours for your security. If yours has expired, just request a new one the same way, and always use the link in the <strong>most recent</strong> email.
      </div>
    </section>

    {{-- 5. Update --}}
    <section id="update" class="mb-5">
      <h2 class="h3">5. Update your details</h2>
      <p>Open the link from the email. You'll see the same form as registration, but already filled in with your details.</p>
      <figure class="figure w-100">
        <img src="/images/help/parent/update.png" class="figure-img img-fluid rounded border shadow-sm" alt="The update page, pre-filled with the parent's saved details.">
        <figcaption class="figure-caption">Your details are pre-filled — change anything, add a child, or remove one.</figcaption>
      </figure>
      <p><strong>One thing you can't change:</strong> each child shows the <strong>class the school has placed them in</strong>. This is shown for your information only — there's no box to edit it.</p>
      <figure class="figure w-100">
        <img src="/images/help/parent/update-allocated-class.png" class="figure-img img-fluid rounded border shadow-sm" alt="A read-only blue notice showing the child's allocated Buddhism and Sinhala classes.">
        <figcaption class="figure-caption">The allocated class is set by the school and shown here for reference.</figcaption>
      </figure>
      <p>When you're done, click <strong>Update Registration</strong>. If you'd <strong>already paid</strong>, your changes save straight away; if your registration <strong>hadn't been paid yet</strong>, you'll be taken to the payment page to finish.</p>
    </section>

    {{-- 6. Cost --}}
    <section id="cost" class="mb-5">
      <h2 class="h3">6. What it costs</h2>
      <p>The fee for the year is shown on the home page — a single price for one child and a slightly higher price for two or more children. Payment is by card through Stripe at the end of registration.</p>
    </section>

    <hr>
    <p class="text-muted">Still stuck? Contact the school using the email address in the footer of every page (and in your confirmation email). Include your name and your child's name so we can find your registration quickly.</p>

  </div>
</div>
@endsection
