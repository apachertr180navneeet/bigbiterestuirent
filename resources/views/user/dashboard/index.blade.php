@extends('user.layouts.app')
@section('style')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .pending-invoice-card {
        border: 1px solid #e7e7ff;
        border-radius: 0.75rem;
        padding: 1rem;
        background: #fff;
    }

    .pending-invoice-card + .pending-invoice-card {
        margin-top: 1rem;
    }

    .pending-invoice-label {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        color: #8592a3;
        letter-spacing: 0.04em;
    }

    .pending-invoice-value {
        font-size: 0.95rem;
        font-weight: 600;
        color: #566a7f;
    }

    .select2-container {
        width: 100% !important;
    }

    .select2-container .select2-selection--single {
        height: calc(2.25rem + 2px);
        border: 1px solid #d9dee3;
        border-radius: 0.375rem;
        padding: 0.375rem 0.75rem;
    }

    .select2-container .select2-selection--single .select2-selection__rendered {
        color: #697a8d;
        line-height: 1.5rem;
        padding-left: 0;
        padding-right: 1.5rem;
    }

    .select2-container .select2-selection--single .select2-selection__arrow {
        height: 100%;
        right: 0.5rem;
    }
</style>
@endsection  

@section('content')

<!-- Content -->

<div class="container-fluid flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-8 mb-4 order-0">
            <div class="card">
                <div class="d-flex align-items-end row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">
                                {{ Auth::guard('sales')->user()->name }}! 🎉
                            </h5>
                            <p class="mb-4">
                                Welcome to your dashboard
                            </p>
                        </div>
                    </div>
                    <div class="col-sm-12 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 order-1">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="mb-0">Pending Invoices</h5>
                    </div>
                    <span class="badge bg-label-primary">{{ $pendingInvoices->count() }} Total</span>
                </div>

                <div class="card-body">
                    <form method="GET" action="{{ route('user.dashboard') }}" class="row g-3 align-items-end mb-4">
                        <div class="col-12 col-md-6 col-lg-4">
                            <label for="firm_id" class="form-label">Filter By Firm</label>
                            <select id="firm_id" name="firm_id" class="form-select">
                                <option value="">All Firms</option>
                                @foreach($firms as $firm)
                                    <option value="{{ $firm->id }}" {{ (string) ($firmId ?? '') === (string) $firm->id ? 'selected' : '' }}>
                                        {{ $firm->firm_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-auto">
                            <button type="submit" class="btn btn-primary">Search</button>
                            <a href="{{ route('user.dashboard') }}" class="btn btn-outline-secondary">Reset</a>
                        </div>
                    </form>

                    @if($pendingInvoices->isEmpty())
                        <div class="text-center py-4 text-muted">
                            No pending invoices found.
                        </div>
                    @else
                        <div class="d-md-none">
                            @foreach($pendingInvoices as $invoice)
                                <div class="pending-invoice-card">
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <div class="pending-invoice-label">Invoice No</div>
                                            <div class="pending-invoice-value">{{ $invoice->invoice_no }}</div>
                                        </div>
                                        <div class="col-6">
                                            <div class="pending-invoice-label">Date</div>
                                            <div class="pending-invoice-value">
                                                {{ \Carbon\Carbon::parse($invoice->date)->format('d-m-Y') }}
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="pending-invoice-label">Firm Name</div>
                                            <div class="pending-invoice-value">{{ optional($invoice->firm)->firm_name ?? '-' }}</div>
                                        </div>
                                        <div class="col-6">
                                            <div class="pending-invoice-label">Status</div>
                                            <div class="pending-invoice-value text-warning text-capitalize">
                                                {{ $invoice->status }}
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="pending-invoice-label">Remaining Amount</div>
                                            <div class="pending-invoice-value">
                                                {{ number_format((float) $invoice->remaining_amount, 2) }}
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <a href="{{ route('user.receipt.create', ['invoice_id' => $invoice->id]) }}" class="btn btn-sm btn-primary w-100">
                                                Create Receipt
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="table-responsive d-none d-md-block">
                            <table class="table table-bordered table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Invoice No</th>
                                        <th>Firm Name</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Remaining Amount</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingInvoices as $invoice)
                                        <tr>
                                            <td>{{ $invoice->invoice_no }}</td>
                                            <td>{{ optional($invoice->firm)->firm_name ?? '-' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($invoice->date)->format('d-m-Y') }}</td>
                                            <td>
                                                <span class="badge bg-label-warning text-capitalize">
                                                    {{ $invoice->status }}
                                                </span>
                                            </td>
                                            <td>{{ number_format((float) $invoice->remaining_amount, 2) }}</td>
                                            <td>
                                                <a href="{{ route('user.receipt.create', ['invoice_id' => $invoice->id]) }}" class="btn btn-sm btn-primary">
                                                    Create Receipt
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<!-- / Content -->

<!-- Footer -->

<!-- / Footer -->

                   
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(function () {
        $('#firm_id').select2({
            placeholder: 'Select firm',
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endsection
