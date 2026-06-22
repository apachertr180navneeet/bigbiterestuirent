@extends('admin.layouts.app')

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
</style>
@endsection

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-md-6 text-start">
            <h5 class="py-2 mb-2">
                <span class="text-primary fw-light">Receipt Management</span>
            </h5>
        </div>

        <div class="col-md-6 text-end">
            <a href="{{ route('admin.receipt.index') }}" class="btn btn-primary">Back</a>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12 col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="mb-4">
                        <h5 class="card-title"><span class="text-primary fw-bold">Edit Receipt</span></h5>
                        <hr>
                    </div>

                    <form id="editReceiptForm"
                        data-mode="edit"
                        data-current-invoice-id="{{ $receipt->invoice_id }}"
                        data-current-given="{{ $receipt->given_amount }}"
                        action="{{ route('admin.receipt.update',$receipt->id) }}"
                        method="POST">

                        @csrf

                        <input type="hidden" name="invoice_id" value="{{ $receipt->invoice_id }}">

                        <div class="row">

                            {{-- Date --}}
                            <div class="col-md-4 mb-3">
                                <label>Date</label>
                                <input type="date"
                                    name="date"
                                    class="form-control"
                                    value="{{ \Carbon\Carbon::parse($receipt->date)->format('Y-m-d') }}">
                            </div>

                            {{-- Receipt No --}}
                            <div class="col-md-4 mb-3">
                                <label>Receipt No</label>
                                <input type="text"
                                    name="receipt_no"
                                    class="form-control"
                                    value="{{ $receipt->receipt_no }}"
                                    readonly>
                            </div>

                            {{-- Firm --}}
                            <div class="col-md-4 mb-3">
                                <label>Firm Name</label>

                                <select name="firm_id" id="firm_id" class="form-select searchable-select" data-placeholder="Search Firm Name">

                                    <option value="">Select Firm</option>

                                    @foreach($customers as $customer)

                                        <option value="{{ $customer->id }}"
                                            {{ $receipt->firm_id == $customer->id ? 'selected':'' }}>
                                            {{ $customer->firm_name }}
                                        </option>

                                    @endforeach

                                </select>

                            </div>

                            {{-- Invoice --}}
                            <div class="col-md-4 mb-3">

                                <label>Invoice</label>

                                <select id="invoice_id" class="form-select searchable-select" data-placeholder="Search Invoice">

                                    @foreach($invoices as $invoice)

                                        <option value="{{ $invoice->id }}"
                                            data-amount="{{ $invoice->amount }}"
                                            data-payable="{{ $invoice->payable_amount }}"
                                            data-paid="{{ $invoice->paid_amount ?? 0 }}"
                                            data-sales-person="{{ optional($invoice->salesperson)->name }}"
                                            {{ $receipt->invoice_id == $invoice->id ? 'selected':'' }}>

                                            {{ $invoice->invoice_no }}

                                        </option>

                                    @endforeach

                                </select>

                            </div>

                            {{-- Invoice Amount --}}
                            <div class="col-md-4 mb-3">

                                <label>Invoice Amount</label>

                                <input type="number"
                                    id="amount"
                                    class="form-control"
                                    readonly>

                            </div>

                            {{-- Remaining --}}
                            <div class="col-md-4 mb-3">

                                <label>Remaining Amount</label>

                                <input type="number"
                                    id="remaining_amount"
                                    class="form-control"
                                    readonly>

                            </div>

                            {{-- Given Amount --}}
                            <div class="col-md-4 mb-3">

                                <label>Given Amount</label>

                                <input type="number"
                                    step="0.01"
                                    name="given_amount"
                                    id="given_amount"
                                    class="form-control"
                                    value="{{ $receipt->given_amount }}">

                            </div>

                            {{-- Sales Person --}}
                            <div class="col-md-4 mb-3">

                                <label>Sales Person</label>

                                <input type="text"
                                    id="sales_person"
                                    class="form-control"
                                    readonly>

                            </div>

                            {{-- Mode --}}
                            <div class="col-md-4 mb-3">

                                <label>Mode</label>

                                <select name="mode" class="form-select">

                                    <option value="cash" {{ $receipt->mode == 'cash' ? 'selected':'' }}>Cash</option>
                                    <option value="upi" {{ $receipt->mode == 'upi' ? 'selected':'' }}>UPI</option>
                                    <option value="bank" {{ $receipt->mode === 'bank' ? 'selected' : '' }}>RTGS / NEFT</option>
                                    <option value="card" {{ $receipt->mode === 'card' ? 'selected' : '' }}>Cheque</option>

                                </select>

                            </div>

                            {{-- Remark --}}
                            <div class="col-md-12 mb-3">

                                <label>Remark</label>

                                <input type="text"
                                    name="remark"
                                    class="form-control"
                                    value="{{ $receipt->remark }}">

                            </div>

                        </div>

                        <div class="text-end">

                            <button class="btn btn-success">
                                Update Receipt
                            </button>

                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    const indexReceiptUrl = "{{ route('admin.receipt.index') }}";
    const createReceiptUrl = "{{ route('admin.receipt.create') }}";

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
<script src="{{ asset('assets/admin/customjs/receipt/index.js') }}"></script>
@endsection
