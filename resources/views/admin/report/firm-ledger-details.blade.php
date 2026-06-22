@extends('admin.layouts.app')

@section('style')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">

<style>
/* Select2 Fix */
.select2-container { width: 100% !important; }

.select2-container .select2-selection--single {
    height: calc(2.25rem + 2px);
    border: 1px solid #d9dee3;
    border-radius: 0.375rem;
    display: flex;
    align-items: center;
}

/* Layout */
.ledger-shell {
    background: #f6f7fb;
    min-height: calc(100vh - 140px);
    padding: 24px;
}

/* Toolbar */
.ledger-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.ledger-title {
    color: #5b64f6;
    font-size: 1.6rem;
    font-weight: 600;
}

.ledger-toolbar .btn {
    min-width: 90px;
    border-radius: 6px;
    font-weight: 500;
}

/* Card */
.ledger-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 6px 18px rgba(31, 45, 61, 0.08);
    padding: 20px 24px;
}

/* Company Header */
.company-header {
    text-align: center;
    margin-bottom: 15px;
}

.company-name {
    font-size: 20px;
    font-weight: 600;
}

.company-info {
    font-size: 13px;
    color: #6c7a89;
    line-height: 1.5;
}

/* Summary */
.ledger-summary {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
    margin-bottom: 18px;
}

.ledger-summary-card {
    border: 1px solid #d8e0ea;
    border-radius: 8px;
    padding: 14px 16px;
    background: #f9fbff;
}

.ledger-summary-label {
    font-size: 13px;
    color: #7a8ea6;
}

.ledger-summary-value {
    font-size: 18px;
    font-weight: 600;
    color: #42566f;
}

/* Table */
.ledger-table {
    width: 100%;
    border-collapse: collapse;
}

.ledger-table th {
    background: #f4f6fa;
    font-weight: 600;
}

.ledger-table th,
.ledger-table td {
    border: 1px solid #d8e0ea;
    padding: 10px 18px;
}

.text-end {
    text-align: right;
}

/* Print */
@media print {
    .btn, .ledger-toolbar, .ledger-filter {
        display: none !important;
    }
}
</style>
@endsection


@section('content')
<div class="ledger-shell">

    {{-- HEADER --}}
    <div class="ledger-toolbar">
        <h4 class="ledger-title">Ledger</h4>

        <div class="d-flex gap-2">
            @if($firmId)
                {{--  <button class="btn btn-primary" onclick="window.print()">Print</button>  --}}

                <a href="{{ route('admin.firm.ledger.pdf', ['firm_id' => $firmId]) }}"
                   class="btn btn-danger">
                    PDF
                </a>
            @endif
        </div>
    </div>


    {{-- CARD --}}
    <div class="ledger-card">

        {{-- FILTER --}}
        <form method="GET" action="{{ route('admin.firm.ledger.details.report') }}" class="ledger-filter">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label>Select Firm</label>
                    <select name="firm_id" class="form-select searchable-select">
                        <option value="">Select Firm</option>
                        @foreach($firms as $firm)
                            <option value="{{ $firm->id }}" {{ $firmId == $firm->id ? 'selected' : '' }}>
                                {{ $firm->firm_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <button class="btn btn-primary">Show Ledger</button>
                    <a href="{{ route('admin.firm.ledger.details.report') }}" class="btn btn-secondary">Reset</a>
                </div>
            </div>
        </form>


        {{-- DATA --}}
        @if($selectedFirm)

            {{-- COMPANY DETAILS --}}
            <div class="company-header mt-4">
                <div class="company-name">Bigbite Agency</div>

                <div class="company-info">
                    Number: +91 8107078020 <br>
                    Address: NEAR MAHABAL MALL, SHOP NO 1,<br>
                    NARSINGH JI PAYAO, MATA KA THAN ROAD,<br>
                    JODHPUR, Rajasthan - 342001
                </div>
            </div>

            <hr>

            {{-- FIRM DETAILS --}}
            <div class="text-center mt-2 mb-3">
                <div><strong>Firm :</strong> {{ $selectedFirm->firm_name }}</div>
                <div><strong>Mobile :</strong> {{ $selectedFirm->phone ?? '-' }}</div>
            </div>


            {{-- SUMMARY --}}
            <div class="ledger-summary">
                <div class="ledger-summary-card">
                    <div class="ledger-summary-label">Total Bill Amount</div>
                    <div class="ledger-summary-value">{{ number_format($totalBillAmount,2) }}</div>
                </div>

                <div class="ledger-summary-card">
                    <div class="ledger-summary-label">Total Receipt Amount</div>
                    <div class="ledger-summary-value">{{ number_format($totalReceiptAmount,2) }}</div>
                </div>

                <div class="ledger-summary-card">
                    <div class="ledger-summary-label">Total Discount</div>
                    <div class="ledger-summary-value">{{ number_format($totalDiscountAmount,2) }}</div>
                </div>

                <div class="ledger-summary-card">
                    <div class="ledger-summary-label">Total Pending Amount</div>
                    <div class="ledger-summary-value">{{ number_format($totalPendingAmount,2) }}</div>
                </div>
            </div>


            {{-- TABLE --}}
            <div class="table-responsive">
                <table class="ledger-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th class="text-end">Bill</th>
                            <th class="text-end">Receipt</th>
                            <th class="text-end">Discount</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($ledgerEntries as $entry)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($entry->date)->format('d/m/Y') }}</td>
                                <td>
                                    {{ $entry->entry_type == 'invoice' ? 'Sales Invoice ' : 'Receipt Voucher ' }}
                                    {{ $entry->reference_no }}
                                </td>
                                <td class="text-end">{{ number_format($entry->debit,2) }}</td>
                                <td class="text-end">{{ number_format($entry->credit,2) }}</td>
                                <td class="text-end">{{ number_format($entry->discount,2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No Data Found</td>
                            </tr>
                        @endforelse
                    </tbody>

                    <tfoot>
                        <tr>
                            <th colspan="2" class="text-end">Total</th>
                            <th class="text-end">{{ number_format($totalBillAmount,2) }}</th>
                            <th class="text-end">{{ number_format($totalReceiptAmount,2) }}</th>
                            <th class="text-end">{{ number_format($totalDiscountAmount,2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>

        @else
            <div class="alert alert-info">Select a firm to view ledger</div>
        @endif

    </div>
</div>
@endsection


@section('script')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$('.searchable-select').select2({
    width: '100%',
    placeholder: "Search Firm",
    allowClear: true
});
</script>
@endsection