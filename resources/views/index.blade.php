@extends('layouts.app')

@section('title', 'Online Registration')

@section('content')
<h1>{{ config('custom.school.name') }}</h1>

<h2>Online Registration</h2>

<p>
  We're delighted that you're interested in enrolling your child at the Dhamma and Sinhala Language School of Canberra. Our program runs throughout the year, offering comprehensive lessons in both Dhamma and the Sinhala language. Our school supports children from Kindergarten through to Year 10.
</p>

<div class="row bg-light mt-4 mb-4"  style="border: 2px solid red; padding: 5px; border-radius: 5px;">
  <p><strong>To ensure a smooth registration process, we kindly ask that you take a few minutes to review our <a href="/guidelines">guidelines</a> in full.</strong></p>
</div>

<div class="row mb-4">
  <div class="col-md-6 mb-4">
    <h4>Update Existing Registration</h4>
    <p>
      If you have previously registered, click the <b>Update Existing Registration</b> option to review your information and proceed with {{ date('Y') }}'s payment. You will receive a unique link via email which will give you access to update your details and then make payment. During the renewal process, we may require some additional details, so kindly ensure all data is accurate.
    </p>
    <a href="/registration/retrieve" class="btn btn-primary">Update Existing Registration</a>
  </div>

  <div class="col-md-6">
    <h4>Register New Family</h4>
    <p>
      Please attend the orientation session on <b>{{ config('custom.school.orientation_day') }}</b>. To complete the registration process, click the <b>Register New Family</b> button below.
    </p>
    <a href="/registration" class="btn btn-success">Register New Family</a>
  </div>
</div>

<p>
  For your reference, the tuition fee for the year is ${{ config('custom.pricing.single_child') }} for a single child and ${{ config('custom.pricing.multiple_children') }} for two or more children.
</p>

<p>
  For any questions, clarifications or feedback, please email <a href="mailto:{{ config('custom.school.email') }}">{{ config('custom.school.email') }}</a>.
</p>

@endsection