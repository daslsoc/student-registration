<!--
    resources/views/emails/class_allocation_changed.blade.php

    Sent when an admin changes a child's allocated class on the Class
    Allocations page. Only the subjects that actually changed are listed.
-->
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Class Allocation Update</title>
</head>
<body style="font-family: Arial, sans-serif;">

<h1 style="color: #333;">Class Allocation Update</h1>

<p>Dear {{ $child->parent->parent1_first_name }} {{ $child->parent->parent1_last_name }},</p>

<p>
    The class allocation for <strong>{{ $child->first_name }} {{ $child->last_name }}</strong>
    (student number {{ $child->student_number }}) has been updated for the {{ date('Y') }} session:
</p>

<ul>
    @foreach($changes as $change)
        <li>
            <strong>{{ $change['subject'] }}:</strong>
            now <strong>{{ $change['to'] ?? 'to be advised' }}</strong>@if(! empty($change['from'])) (previously {{ $change['from'] }})@endif.
        </li>
    @endforeach
</ul>

<p>No action is needed from you &mdash; this email is just to keep you informed. If you have any questions, please contact us at {{ config('custom.school.email') }}.</p>

<p style="margin-top: 20px;">Best regards,<br>
{{ config('custom.school.name') }} Management Team</p>

</body>
</html>
