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
    @endif

    <div class="card mt-4 mb-4">
        <div class="card-body">
            <h5 class="card-title">What happens next</h5>
            <ul class="mb-0">
                @if(config('custom.school.orientation_day'))
                    <li>Please attend the orientation session on <strong>{{ config('custom.school.orientation_day') }}</strong>.</li>
                @endif
                @if(config('custom.socials.whatsapp_join_url'))
                    <li>Join our parents' WhatsApp group to stay up to date:
                        <a href="{{ config('custom.socials.whatsapp_join_url') }}" target="_blank" rel="noopener noreferrer">join the group</a>.
                        (A link has also been emailed to you.)</li>
                @endif
                <li>Keep your confirmation email &mdash; you can use the
                    <a href="{{ route('registration.retrieve') }}">Update Existing Registration</a> option any time to review or change your details.</li>
                @if(config('custom.school.email'))
                    <li>Questions? Email us at
                        <a href="mailto:{{ config('custom.school.email') }}">{{ config('custom.school.email') }}</a>.</li>
                @endif
            </ul>
        </div>
    </div>

    <p>We look forward to seeing you and your family at the school!</p>
@endsection
