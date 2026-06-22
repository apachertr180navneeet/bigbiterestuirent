@extends('user.layouts.app')

@section('style')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<style>
    .select2-container {
        width: 100% !important;
    }

    .select2-container .select2-selection--single {
        height: calc(2.25rem + 2px);
        border: 1px solid #d9dee3;
        border-radius: 0.375rem;
        display: flex;
        align-items: center;
    }

    .select2-container .select2-selection--single .select2-selection__rendered {
        color: #566a7f;
        line-height: normal;
        padding-left: 0.75rem;
        padding-right: 2rem;
    }

    .select2-container .select2-selection--single .select2-selection__arrow {
        height: 100%;
        right: 0.5rem;
    }

    .select2-dropdown {
        border-color: #d9dee3;
    }

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

    @media (max-width: 991.98px) {
        .ledger-summary {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 575.98px) {
        .ledger-summary {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-md-6">
            <h5 class="py-2 mb-3">
                <span class="text-primary fw-light">Firm Ledger Report</span>
            </h5>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('user.firm.ledger.report') }}">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Select Firm</label>
                        <select name="firm_id" class="form-select searchable-select" data-placeholder="Search Firm Name">
                            <option value="">All Firms</option>
                            @foreach($firms as $firm)
                                <option value="{{ $firm->id }}" {{ (string) $firmId === (string) $firm->id ? 'selected' : '' }}>
                                    {{ $firm->firm_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mt-4">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="{{ route('user.firm.ledger.report') }}" class="btn btn-secondary">Reset</a>
                    </div>
                </div>
            </form>

            @if(!$selectedFirm)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Firm Name</th>
                                <th>Total Debit</th>
                                <th>Total Credit</th>
                                <th>Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reports as $report)
                                <tr>
                                    <td>{{ $report->firm_name }}</td>
                                    <td>{{ number_format($report->total_debit, 2) }}</td>
                                    <td>{{ number_format($report->total_credit, 2) }}</td>
                                    <td>{{ number_format($report->balance, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No Data Found</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <th class="text-end">Total</th>
                                <th>{{ number_format($totalDebit, 2) }}</th>
                                <th>{{ number_format($totalCredit, 2) }}</th>
                                <th>{{ number_format($totalBalance, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif

            @if($selectedFirm)
                <div class="mt-4">
                    <div class="company-header">
                        <div class="company-name">Bigbite Agency</div>
                        <div class="company-info">
                            Number: +91 8107078020 <br>
                            Address: NEAR MAHABAL MALL, SHOP NO 1,<br>
                            NARSINGH JI PAYAO, MATA KA THAN ROAD,<br>
                            JODHPUR, Rajasthan - 342001
                        </div>
                    </div>

                    <hr>

                    <div class="text-center mt-2 mb-3">
                        <div><strong>Firm :</strong> {{ $selectedFirm->firm_name }}</div>
                        <div><strong>Mobile :</strong> {{ $selectedFirm->phone ?? '-' }}</div>
                    </div>

                    <div class="ledger-summary">
                        <div class="ledger-summary-card">
                            <div class="ledger-summary-label">Total Bill Amount</div>
                            <div class="ledger-summary-value">{{ number_format($totalBillAmount, 2) }}</div>
                        </div>

                        <div class="ledger-summary-card">
                            <div class="ledger-summary-label">Total Receipt Amount</div>
                            <div class="ledger-summary-value">{{ number_format($totalReceiptAmount, 2) }}</div>
                        </div>

                        <div class="ledger-summary-card">
                            <div class="ledger-summary-label">Total Discount</div>
                            <div class="ledger-summary-value">{{ number_format($totalDiscountAmount, 2) }}</div>
                        </div>

                        <div class="ledger-summary-card">
                            <div class="ledger-summary-label">Total Pending Amount</div>
                            <div class="ledger-summary-value">{{ number_format($totalPendingAmount, 2) }}</div>
                        </div>
                    </div>

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
                                        <td class="text-end">{{ number_format($entry->debit, 2) }}</td>
                                        <td class="text-end">{{ number_format($entry->credit, 2) }}</td>
                                        <td class="text-end">{{ number_format($entry->discount, 2) }}</td>
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
                                    <th class="text-end">{{ number_format($totalBillAmount, 2) }}</th>
                                    <th class="text-end">{{ number_format($totalReceiptAmount, 2) }}</th>
                                    <th class="text-end">{{ number_format($totalDiscountAmount, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $('.searchable-select').each(function () {
        const $select = $(this);
        const placeholder = $select.data('placeholder') || 'Search';

        $select.select2({
            width: '100%',
            placeholder: placeholder,
            allowClear: true
        }).on('select2:open', function () {
            $('.select2-search__field').attr('placeholder', placeholder);
        });
    });
</script>
@endsection
