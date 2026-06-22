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
                            <span class="text-primary fw-bold">Add Invoice</span>
                        </h5>
                        <hr>
                    </div>

                    <form id="invoiceForm" data-mode="create" action="{{ route('admin.invoice.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" 
                                    name="date" 
                                    class="form-control" 
                                    max="{{ date('Y-m-d') }}"
                                    value="{{ old('date') }}">
                                @error('date')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Invoice No. <span class="text-danger">*</span></label>
                                <input type="text" name="invoice_no" class="form-control" placeholder="Enter Invoice No." value="{{ old('invoice_no') }}">
                                @error('invoice_no')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Firm Name <span class="text-danger">*</span></label>
                                <select name="firm_id" class="form-select searchable-select" data-placeholder="Search Firm Name">
                                    <option value="">Select Firm</option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ old('firm_id') == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->firm_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('firm_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Salesperson Name <span class="text-danger">*</span></label>
                                <select name="salesperson_id" class="form-select searchable-select" data-placeholder="Search Salesperson Name">
                                    <option value="">Select Salesperson</option>
                                    @foreach ($salespersons as $salesperson)
                                        <option value="{{ $salesperson->id }}" {{ old('salesperson_id') == $salesperson->id ? 'selected' : '' }}>
                                            {{ $salesperson->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('salesperson_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Amount <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" name="amount" id="amount" class="form-control" placeholder="Enter Amount" value="{{ old('amount') }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Discount %</label>
                                <input type="number" step="0.01" min="0" name="discount_percent" id="discount_percent" class="form-control" placeholder="Enter Discount %" value="{{ old('discount_percent') }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Discount Amount</label>
                                <input type="text" name="discount_amount" id="discount_amount" class="form-control" readonly>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Payable Amount</label>
                                <input type="text" name="payable_amount" id="payable_amount" class="form-control" readonly>
                            </div>

                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-success">
                                Save Invoice
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
