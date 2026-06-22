@extends('admin.layouts.app')

@section('style')
@endsection

@section('content')

<div class="container-fluid flex-grow-1 container-p-y">

    <!-- Page Header -->
    <div class="row">
        <div class="col-md-6 text-start">
            <h5 class="py-2 mb-2">
                <span class="text-primary fw-light">Invoice Management</span>
            </h5>
        </div>

        <div class="col-md-6 text-end">
            <a 
                href="{{route('admin.invoice.create')}}" 
                class="btn btn-primary"
            >
                Add Invoice
            </a>
        </div>
    </div>

    <!-- Invoice Table -->
    <div class="row">
        <div class="col-xl-12 col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-2">
                            <label for="filter_invoice_no" class="form-label">Invoice Number</label>
                            <input type="text" id="filter_invoice_no" class="form-control" placeholder="Enter invoice number">
                        </div>
                        <div class="col-md-2">
                            <label for="filter_salesperson_id" class="form-label">Salesperson</label>
                            <select id="filter_salesperson_id" class="form-select">
                                <option value="">All</option>
                                @foreach ($salespersons as $salesperson)
                                    <option value="{{ $salesperson->id }}">{{ $salesperson->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="filter_date_from" class="form-label">Start Date</label>
                            <input type="date" id="filter_date_from" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label for="filter_date_to" class="form-label">End Date</label>
                            <input type="date" id="filter_date_to" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label for="filter_status" class="form-label">Status</label>
                            <select id="filter_status" class="form-select">
                                <option value="">All</option>
                                <option value="pending">Pending</option>
                                <option value="full_paid">Full Paid</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end gap-2">
                            <button type="button" id="applyInvoiceFilters" class="btn btn-primary">Apply</button>
                            <button type="button" id="resetInvoiceFilters" class="btn btn-outline-secondary">Reset</button>
                        </div>
                    </div>

                    <div class="table-responsive text-nowrap">
                        <table class="table table-bordered" id="invoiceTable">
                            <thead>
                                <tr>
                                    <th>Invoice No</th>
                                    <th>Date</th>
                                    <th>Firm</th>
                                    <th>Salesperson</th>
                                    <th>Amount</th>
                                    <th>Discount %</th>
                                    <th>Payable Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>

@endsection


@section('script')
<script>
    const getInvoiceUrl = "{{ route('admin.invoice.getall') }}";
    const indexInvoiceUrl = "{{ route('admin.invoice.index') }}";
    const createInvoiceUrl = "{{ route('admin.invoice.create') }}";
    const deleteInvoiceUrl = "{{ route('admin.invoice.delete', ':id') }}";
    const editInvoiceUrl = "{{ route('admin.invoice.edit', ':id') }}";
</script>
<script src="{{asset('assets/admin/customjs/invoice/index.js')}}"></script>
@endsection
