<!--
    A simple success page after the user completes payment.
-->

@extends('layouts.app')

@section('title', 'Registration Success')

@section('content')
    <h1>Registration Complete!</h1>
    <p>Thank you for registering your child(ren) with the Dhamma and Sinhala Language School of Canberra. Your payment has been successfully processed.</p>

    @if(isset($parent))
        @if($parent->parent2_email)
            <p>A confirmation email has been sent to {{ $parent->parent1_email }} and {{ $parent->parent2_email }}.</p>
        @else
            <p>A confirmation email has been sent to {{ $parent->parent1_email }}.</p>
        @endif
        <p>We look forward to seeing you and your family at the school!</p>
    @endif
@endsection
