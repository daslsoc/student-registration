@extends('layouts.app')

@section('title', 'Online Registration')

@section('content')
<h1>{{ config('custom.school.name') }}</h1>

<h2>Online Registration</h2>

<p>
  We're delighted that you're interested in enrolling your child at the Dhamma and Sinhala Language School of Canberra. Our program runs throughout the year, offering comprehensive lessons in both Dhamma and the Sinhala language. Our school supports children from Pre School through to Grade 12.
</p>

<div class="alert alert-warning mt-4 mb-4" role="alert">
  <strong>Before you begin:</strong> to ensure a smooth registration process, we kindly ask that you take a few minutes to review our <a href="{{ route('guidelines') }}" class="alert-link">guidelines</a> in full.
</div>

<div class="row mb-4">
  <div class="col-md-6 mb-4">
    <h4>Update Existing Registration</h4>
    <p>
      If you have previously registered, click the <b>Update Existing Registration</b> option to review your information and proceed with {{ date('Y') }}'s payment. You will receive a unique link via email which will give you access to update your details and then make payment. During the renewal process, we may require some additional details, so kindly ensure all data is accurate.
    </p>
    <a href="{{ route('registration.retrieve') }}" class="btn btn-primary">Update Existing Registration</a>
  </div>

  <div class="col-md-6">
    <h4>Register New Family</h4>
    <p>
      New to the school? Click <b>Register New Family</b> below to complete your registration and payment online first. Once registered, please attend the orientation session on <b>{{ config('custom.school.orientation_day') }}</b>.
    </p>
    <a href="{{ route('registration.form') }}" class="btn btn-success">Register New Family</a>
  </div>
</div>

<p>
  For your reference, the tuition fee for the year is ${{ config('custom.pricing.single_child') }} for a single child and ${{ config('custom.pricing.multiple_children') }} for two or more children. Payment is processed securely by Stripe at the end of registration &mdash; your card details are never stored on our servers.
</p>

<p>
  For any questions, clarifications or feedback, please email <a href="mailto:{{ config('custom.school.email') }}">{{ config('custom.school.email') }}</a>.
</p>

@endsection