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
                <span class="text-primary fw-light">Invoice Management</span>
            </h5>
        </div>

        <div class="col-md-6 text-end">
            <a href="{{ route('admin.invoice.index') }}" class="btn btn-primary">
                Back
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12 col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="mb-4">
                        <h5 class="card-title">
                            <span class="text-primary fw-bold">Edit Invoice</span>
                        </h5>
                        <hr>
                    </div>

                    <form
                        id="invoiceForm"
                        data-mode="edit"
                        action="{{ route('admin.invoice.update', $invoice->id) }}"
                        method="POST"
                    >
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date <span class="text-danger">*</span></label>
                                <input
                                    type="date"
                                    name="date"
                                    class="form-control"
                                    max="{{ date('Y-m-d') }}"
                                    value="{{ old('date', $invoice->date ? \Illuminate\Support\Carbon::parse($invoice->date)->format('Y-m-d') : '') }}"
                                >
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Invoice No. <span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    name="invoice_no"
                                    class="form-control"
                                    placeholder="Enter Invoice No."
                                    value="{{ old('invoice_no', $invoice->invoice_no) }}"
                                >
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Firm Name <span class="text-danger">*</span></label>
                                <select name="firm_id" class="form-select searchable-select" data-placeholder="Search Firm Name">
                                    <option value="">Select Firm</option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ (string) old('firm_id', $invoice->firm_id) === (string) $customer->id ? 'selected' : '' }}>
                                            {{ $customer->firm_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Salesperson Name <span class="text-danger">*</span></label>
                                <select name="salesperson_id" class="form-select searchable-select" data-placeholder="Search Salesperson Name">
                                    <option value="">Select Salesperson</option>
                                    @foreach ($salespersons as $salesperson)
                                        <option value="{{ $salesperson->id }}" {{ (string) old('salesperson_id', $invoice->salesperson_id) === (string) $salesperson->id ? 'selected' : '' }}>
                                            {{ $salesperson->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Amount <span class="text-danger">*</span></label>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="amount"
                                    id="amount"
                                    class="form-control"
                                    placeholder="Enter Amount"
                                    value="{{ old('amount', $invoice->amount) }}"
                                >
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Discount %</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="discount_percent"
                                    id="discount_percent"
                                    class="form-control"
                                    value="{{ old('discount_percent', $invoice->discount_percent) }}"
                                >
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Discount Amount</label>
                                <input
                                    type="text"
                                    name="discount_amount"
                                    id="discount_amount"
                                    class="form-control"
                                    value="{{ old('discount_amount', $invoice->discount_amount) }}"
                                    readonly
                                >
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Payable Amount</label>
                                <input
                                    type="text"
                                    name="payable_amount"
                                    id="payable_amount"
                                    class="form-control"
                                    value="{{ old('payable_amount', $invoice->payable_amount) }}"
                                    readonly
                                >
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-success">
                                Update Invoice
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
    const indexInvoiceUrl = "{{ route('admin.invoice.index') }}";
    const createInvoiceUrl = "{{ route('admin.invoice.create') }}";

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
<script src="{{ asset('assets/admin/customjs/invoice/index.js') }}"></script>
@endsection
