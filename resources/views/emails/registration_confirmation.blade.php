<!--
    resources/views/emails/registration_confirmation.blade.php

    This email is sent to the parent(s) after successful payment,
    confirming registration details. It can be styled more elaborately
    (with CSS, tables, etc.) if desired.
-->
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Registration Confirmation</title>
</head>
<body style="font-family: Arial, sans-serif;">

<!-- Email Header Section -->
<h1 style="color: #333;">School Registration Confirmation</h1>
<p>Dear {{ $parent->parent1_first_name }} {{ $parent->parent1_last_name }},</p>

<!-- Confirmation Message -->
<p>Thank you for completing your school registration. We have received your payment, and your child(ren) are now registered for the {{ date('Y') }} session.</p>

<!-- Parent Details -->
<h3>Parent Details</h3>
<ul>
    <li><strong>Parent 1 Name:</strong> {{ $parent->parent1_first_name }} {{ $parent->parent1_last_name }}</li>
    <li><strong>Parent 1 Email:</strong> {{ $parent->parent1_email }}</li>
    <li><strong>Parent 1 Phone:</strong> {{ $parent->parent1_phone }}</li>
    @if($parent->parent2_first_name || $parent->parent2_last_name)
    <li><strong>Parent 2 Name:</strong> {{ $parent->parent2_first_name }} {{ $parent->parent2_last_name }}</li>
    @endif
    @if($parent->parent2_email)
    <li><strong>Parent 2 Email:</strong> {{ $parent->parent2_email }}</li>
    @endif
    @if($parent->parent2_phone)
    <li><strong>Parent 2 Phone:</strong> {{ $parent->parent2_phone }}</li>
    @endif
    <li><strong>Postal Code:</strong> {{ $parent->postcode ?? 'N/A' }}</li>
    <li><strong>Guidelines Accepted:</strong> {{ $parent->guidelines_accepted ? 'Yes' : 'No' }}</li>
</ul>

<h3>Emergency Contact</h3>
<ul>
    <li><strong>Emergency Contact Name:</strong> {{ $parent->emergency_contact_name }}</li>
    <li><strong>Emergency Contact Phone:</strong> {{ $parent->emergency_contact_phone }}</li>
    <li><strong>Relationship to Family:</strong> {{ $parent->relationship_to_family }}</li>
</ul>

<!-- Children Section -->
<h3>Child(ren) Details</h3>
@foreach($parent->children as $child)
<div style="margin-bottom: 10px;">
    <strong>Child Name:</strong> {{ $child->first_name }} {{ $child->last_name }}<br>
    <strong>Gender:</strong> {{ $child->gender }}<br>
    <strong>Date of Birth:</strong> {{ $child->date_of_birth }}<br>
    <strong>Residency Status:</strong> {{ $child->residency_status }}<br>
    <strong>Day School:</strong> {{ $child->day_school_name }} (Year: {{ $child->day_school_year }})<br>
    <strong>Allocated Class for {{ date('Y') }}:</strong>
        {{ $child->allocated_dhamma_class ?? 'to be advised' }}@if($child->allocated_sinhala_class && $child->allocated_sinhala_class !== $child->allocated_dhamma_class) (Sinhala: {{ $child->allocated_sinhala_class }})@endif<br>
    <strong>Allergies:</strong> {{ $child->allergies ?? 'N/A' }}<br>
    <strong>Special Needs:</strong> {{ $child->special_needs ?? 'N/A' }}<br>
    <strong>Photography Allowed:</strong> {{ $child->photography_allowed ? 'Yes' : 'No' }}<br>
    <hr>
</div>
@endforeach

<!-- Closing -->
<p>We look forward to an amazing year! If you have any questions or need to update your registration details, please contact us at {{ config('custom.school.email') }}.</p>

<p style="margin-top: 20px;">Best regards,<br>
Dhamma and Sinhala Language School Management Team</p>

</body>
</html>
