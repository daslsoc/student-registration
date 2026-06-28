@extends('layouts.app')

@section('title', 'Payment Status Override')

@section('content')
<h1 class="h3 mb-1">Payment Status Override</h1>
<p class="text-muted">Record a payment taken at the desk by <strong>cash</strong> or <strong>EFTPOS</strong>, or <strong>waive</strong> the fee (financial hardship / recently migrated). Every change is audit-logged below.</p>

@if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<div class="card mb-4">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.payment_override.store') }}" class="row g-3">
            @csrf
            <div class="col-md-6">
                <label for="parent_id" class="form-label">Family</label>
                <select name="parent_id" id="parent_id" class="form-select" required>
                    <option value="" disabled selected>Choose a family…</option>
                    @foreach($parents as $p)
                        <option value="{{ $p->id }}">
                            {{ $p->parent1_last_name }}, {{ $p->parent1_first_name }}
                            — {{ ucfirst($p->registration_status) }}{{ $p->payments->isNotEmpty() ? ' (paid)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label d-block">Action</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="action" id="action_paid" value="marked_paid" checked>
                    <label class="form-check-label" for="action_paid">Mark as paid</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="action" id="action_revert" value="reverted">
                    <label class="form-check-label" for="action_revert">Revert to pending</label>
                </div>
            </div>

            <div class="col-md-4" data-paid-only>
                <label for="method" class="form-label">Method</label>
                <select name="method" id="method" class="form-select">
                    <option value="cash">Cash</option>
                    <option value="eftpos">EFTPOS</option>
                    <option value="waived">Waived (hardship / recently migrated)</option>
                </select>
            </div>
            <div class="col-md-4" data-paid-only>
                <label for="amount" class="form-label">Amount (optional)</label>
                <input type="number" step="0.01" min="0" name="amount" id="amount" class="form-control" placeholder="Standard fee; waived = $0">
            </div>
            <div class="col-md-4">
                <label for="note" class="form-label">Note (optional)</label>
                <input type="text" name="note" id="note" class="form-control" maxlength="500" placeholder="e.g. paid at orientation desk">
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary">Apply &amp; log</button>
            </div>
        </form>
    </div>
</div>

<h2 class="h5">Families</h2>
<div class="table-responsive mb-4">
    <table class="table table-sm table-bordered align-middle" id="families-table">
        <thead class="table-light">
            <tr><th>Family</th><th>Email</th><th>Children</th><th>Status</th><th>Last method</th></tr>
        </thead>
        <tbody>
            @foreach($parents as $p)
                <tr>
                    <td>{{ $p->parent1_last_name }}, {{ $p->parent1_first_name }}</td>
                    <td class="small">{{ $p->parent1_email }}</td>
                    <td>{{ $p->children_count }}</td>
                    <td>
                        @if($p->registration_status === \App\Models\ParentModel::STATUS_COMPLETED)
                            <span class="badge text-bg-success">Completed</span>
                        @else
                            <span class="badge text-bg-warning">Pending</span>
                        @endif
                    </td>
                    <td>{{ optional($p->payments->sortByDesc('paid_date')->first())->method ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<h2 class="h5">Recent overrides (audit log)</h2>
<div class="table-responsive">
    <table class="table table-sm table-bordered align-middle">
        <thead class="table-light">
            <tr><th>When</th><th>Family</th><th>Action</th><th>Method</th><th>Amount</th><th>By</th><th>Note</th></tr>
        </thead>
        <tbody>
            @forelse($recentOverrides as $o)
                <tr>
                    <td class="small text-nowrap">{{ $o->created_at->format('j M Y H:i') }}</td>
                    <td>{{ optional($o->parent)->parent1_first_name }} {{ optional($o->parent)->parent1_last_name }}</td>
                    <td>{{ $o->action === \App\Models\PaymentOverride::ACTION_MARKED_PAID ? 'Marked paid' : 'Reverted' }}</td>
                    <td>{{ $o->method ?? '—' }}</td>
                    <td>{{ $o->amount !== null ? '$'.number_format($o->amount, 2) : '—' }}</td>
                    <td>{{ $o->performed_by ?? '—' }}</td>
                    <td class="small">{{ $o->note }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted">No overrides recorded yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function () {
        $('#families-table').DataTable({ pageLength: 10, order: [] });

        // Hide method/amount when reverting (the server enforces this too).
        const paidOnly = document.querySelectorAll('[data-paid-only]');
        const toggle = () => {
            const paid = document.getElementById('action_paid').checked;
            paidOnly.forEach(el => el.style.display = paid ? '' : 'none');
        };
        document.getElementById('action_paid').addEventListener('change', toggle);
        document.getElementById('action_revert').addEventListener('change', toggle);
        toggle();
    });
</script>
@endsection
