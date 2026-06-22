@extends('admin.layouts.app')

@section('style')
<style>
    .dashboard-shell {
        position: relative;
        overflow: hidden;
    }

    .dashboard-shell::before,
    .dashboard-shell::after {
        content: "";
        position: absolute;
        border-radius: 50%;
        filter: blur(10px);
        opacity: 0.45;
        pointer-events: none;
    }

    .dashboard-shell::before {
        width: 220px;
        height: 220px;
        top: -90px;
        right: -60px;
        background: radial-gradient(circle, rgba(255, 159, 67, 0.35) 0%, rgba(255, 159, 67, 0) 70%);
    }

    .dashboard-shell::after {
        width: 260px;
        height: 260px;
        left: -100px;
        bottom: -120px;
        background: radial-gradient(circle, rgba(105, 108, 255, 0.22) 0%, rgba(105, 108, 255, 0) 72%);
    }

    .hero-panel {
        position: relative;
        overflow: hidden;
        border: 0;
        border-radius: 28px;
        background:
            radial-gradient(circle at top right, rgba(255, 255, 255, 0.22), transparent 30%),
            linear-gradient(135deg, #203a8f 0%, #2b57d9 38%, #ff9f43 100%);
        box-shadow: 0 24px 60px rgba(32, 58, 143, 0.22);
    }

    .hero-panel .card-body {
        padding: 2rem;
    }

    .hero-kicker {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.45rem 0.85rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.14);
        color: #fff;
        font-size: 0.8rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .hero-title,
    .hero-copy,
    .hero-label,
    .hero-value {
        color: #fff;
    }

    .hero-title {
        font-size: 2rem;
        line-height: 1.2;
        margin: 1.25rem 0 0.75rem;
    }

    .hero-copy {
        max-width: 540px;
        color: rgba(255, 255, 255, 0.8);
        margin-bottom: 1.5rem;
    }

    .hero-metrics {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
        margin-top: 1.75rem;
    }

    .hero-metric {
        padding: 1rem 1.1rem;
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.12);
        backdrop-filter: blur(8px);
    }

    .hero-label {
        display: block;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: rgba(255, 255, 255, 0.72);
        margin-bottom: 0.45rem;
    }

    .hero-value {
        font-size: 1.4rem;
        font-weight: 700;
    }

    .hero-visual {
        position: relative;
        min-height: 100%;
        display: flex;
        align-items: end;
        justify-content: center;
    }

    .hero-orbit {
        position: absolute;
        border: 1px solid rgba(255, 255, 255, 0.22);
        border-radius: 50%;
    }

    .hero-orbit.orbit-lg {
        width: 250px;
        height: 250px;
        top: 16px;
    }

    .hero-orbit.orbit-sm {
        width: 165px;
        height: 165px;
        top: 58px;
    }

    .hero-visual img {
        position: relative;
        z-index: 2;
        max-width: 41%;
        height: 210px;
        object-fit: contain;
        transform: translateY(12px);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .metric-card,
    .table-card {
        border: 0;
        border-radius: 24px;
        box-shadow: 0 14px 40px rgba(67, 89, 113, 0.08);
    }

    .metric-card {
        height: 100%;
        overflow: hidden;
    }

    .metric-card .card-body {
        padding: 1.35rem;
    }

    .metric-icon {
        width: 48px;
        height: 48px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        color: #fff;
        margin-bottom: 1rem;
    }

    .metric-icon.blue { background: linear-gradient(135deg, #3158d9, #6c8cff); }
    .metric-icon.orange { background: linear-gradient(135deg, #ff8a3d, #ffb067); }
    .metric-icon.green { background: linear-gradient(135deg, #1f9d74, #58c99e); }
    .metric-icon.red { background: linear-gradient(135deg, #d84b68, #ff7c97); }

    .metric-title {
        color: #566a7f;
        font-size: 0.9rem;
        margin-bottom: 0.35rem;
    }

    .metric-value {
        margin: 0;
        font-size: 1.7rem;
        font-weight: 700;
        color: #22304a;
    }

    .metric-note {
        display: inline-flex;
        margin-top: 0.8rem;
        padding: 0.35rem 0.65rem;
        border-radius: 999px;
        background: #f4f6fb;
        color: #6b7a90;
        font-size: 0.78rem;
    }

    .spotlight-card {
        height: 100%;
        border: 0;
        border-radius: 24px;
        background: linear-gradient(180deg, #fff8ef 0%, #ffffff 100%);
        box-shadow: 0 14px 40px rgba(67, 89, 113, 0.08);
    }

    .spotlight-card .card-body {
        padding: 1.5rem;
    }

    .spotlight-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.35rem 0.75rem;
        border-radius: 999px;
        background: rgba(255, 159, 67, 0.14);
        color: #c96e1b;
        font-weight: 600;
        font-size: 0.82rem;
    }

    .spotlight-value {
        font-size: 2rem;
        font-weight: 700;
        color: #22304a;
        margin: 1rem 0 0.35rem;
    }

    .spotlight-copy {
        color: #697a8d;
        margin: 0;
    }

    .table-card .card-header {
        padding: 1.35rem 1.35rem 0;
        background: transparent;
        border: 0;
    }

    .table-title {
        margin: 0;
        color: #22304a;
        font-weight: 700;
    }

    .table-subtitle {
        margin: 0.35rem 0 0;
        color: #7b8a9f;
    }

    .table-card .card-body {
        padding: 1.25rem 1.35rem 1.35rem;
    }

    .pending-table {
        margin: 0;
        vertical-align: middle;
    }

    .pending-table thead th {
        border-bottom: 0;
        background: #f5f7fb;
        color: #5d6d83;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 0.95rem 0.85rem;
        white-space: nowrap;
    }

    .pending-table tbody td {
        padding: 1rem 0.85rem;
        color: #33455c;
    }

    .pending-table tbody tr:hover {
        background: #fbfcff;
    }

    .invoice-pill {
        display: inline-flex;
        align-items: center;
        padding: 0.35rem 0.7rem;
        border-radius: 999px;
        background: rgba(49, 88, 217, 0.08);
        color: #3158d9;
        font-weight: 600;
    }

    .days-pill {
        display: inline-flex;
        align-items: center;
        padding: 0.35rem 0.7rem;
        border-radius: 999px;
        background: rgba(216, 75, 104, 0.1);
        color: #c1435e;
        font-weight: 600;
        white-space: nowrap;
    }

    .amount-text {
        font-weight: 700;
        color: #1f9d74;
    }

    @media (max-width: 991.98px) {
        .hero-panel .card-body {
            padding: 1.5rem;
        }

        .hero-title {
            font-size: 1.6rem;
        }

        .hero-metrics,
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .hero-visual {
            min-height: 220px;
            margin-top: 1rem;
        }
    }

    @media (max-width: 575.98px) {
        .container-p-y {
            padding-top: 1rem !important;
        }

        .hero-title {
            font-size: 1.35rem;
        }
    }

    .pagination {
        margin-bottom: 0;
    }

    .pagination .page-link {
        border-radius: 8px !important;
        margin: 0 3px;
        color: #566a7f;
        border: 1px solid #e0e6ed;
        transition: all 0.2s ease;
        padding: 6px 12px;
    }

    .pagination .page-link:hover {
        background-color: #696cff;
        color: #fff;
        border-color: #696cff;
    }

    .pagination .page-item.active .page-link {
        background: linear-gradient(135deg, #696cff, #5f61e6);
        border-color: #696cff;
        color: #fff;
    }

    .pagination .page-item.disabled .page-link {
        opacity: 0.5;
    }
</style>
@endsection

@section('content')
<div class="container-fluid flex-grow-1 container-p-y dashboard-shell">

    <div class="row g-4">
        <div class="col-12 col-xl-8">
            <div class="card hero-panel">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-lg-7">
                            <span class="hero-kicker">
                                <i class="bx bx-pulse"></i>
                                Admin dashboard
                            </span>
                            <h1 class="hero-title">
                                Welcome back, {{ Auth::user()->full_name }}
                            </h1>
                            <p class="hero-copy">
                                Keep invoices, collections, and outstanding balances in one sharp view built for daily follow-up.
                            </p>

                            <div class="hero-metrics">
                                <div class="hero-metric">
                                    <span class="hero-label">Total bill count</span>
                                    <div class="hero-value">{{ $pendingInvoiceCount }}</div>
                                </div>
                                <div class="hero-metric">
                                    <span class="hero-label">Total bill amount</span>
                                    <div class="hero-value">{{ $totalPendingBillAmount }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <div class="hero-visual">
                                <span class="hero-orbit orbit-lg"></span>
                                <span class="hero-orbit orbit-sm"></span>
                                <img
                                    src="{{ asset('assets/admin/img/illustrations/dg1.png') }}"
                                    alt="Dashboard illustration"
                                    data-app-dark-img="illustrations/dg1.png"
                                    data-app-light-img="illustrations/dg1.png" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="stats-grid">
                <div class="card metric-card">
                    <div class="card-body">
                        <span class="metric-icon red">
                            <i class="bx bx-time-five"></i>
                        </span>
                        <div class="metric-title">Unapproved receipt</div>
                        <h3 class="metric-value">{{ $unapprovedReceiptCount }}</h3>
                        <span class="metric-note">Pending confirmation</span>
                    </div>
                </div>

                <div class="card metric-card">
                    <div class="card-body">
                        <span class="metric-icon red">
                            <i class="bx bx-time-five"></i>
                        </span>
                        <div class="metric-title">Unapproved receipt amount</div>
                        <h3 class="metric-value">{{ $unapprovedReceivedAmount }}</h3>
                        <span class="metric-note">Pending confirmation</span>
                    </div>
                </div>
            </div>
        </div>

        

        <div class="col-12 col-xl-12">
            <div class="card table-card">
                <div class="card-header">
                    <h5 class="table-title">Old pending invoices</h5>
                    <p class="table-subtitle">
                        Ageing invoices that need the fastest follow-up from the collection team.
                    </p>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table pending-table">
                            <thead>
                                <tr>
                                    @if(\App\Helpers\Helper::isSuperAdmin())<th>Company</th>@endif
                                    <th>Invoice No</th>
                                    <th>Firm Name</th>
                                    <th>Salesman</th>
                                    <th>Invoice Date</th>
                                    <th>Pending Days</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendingInvoices as $invoice)
                                    <tr>
                                        @if(\App\Helpers\Helper::isSuperAdmin())<td>{{ $invoice->company_name ?? '-' }}</td>@endif
                                        <td>
                                            <span class="invoice-pill">{{ $invoice->invoice_no }}</span>
                                        </td>
                                        <td>{{ $invoice->firm_name }}</td>
                                        <td>{{ $invoice->salesman_name }}</td>
                                        <td>{{ \Carbon\Carbon::parse($invoice->date)->format('d/m/Y') }}</td>
                                        <td>
                                            <span class="days-pill">{{ $invoice->pending_days }} Days</span>
                                        </td>
                                        <td class="amount-text">{{ $invoice->payable_amount }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            No pending invoices found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($pendingInvoices->hasPages())
                        <div class="mt-4 d-flex justify-content-between align-items-center flex-wrap">

                            <!-- Showing info -->
                            <div class="text-muted small mb-2">
                                Showing 
                                {{ $pendingInvoices->firstItem() }} 
                                to 
                                {{ $pendingInvoices->lastItem() }} 
                                of 
                                {{ $pendingInvoices->total() }} entries
                            </div>

                            <!-- Pagination -->
                            <div>
                                {{ $pendingInvoices->onEachSide(1)->links('pagination::bootstrap-5') }}
                            </div>

                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
@endsection
