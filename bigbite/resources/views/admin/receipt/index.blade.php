@extends('admin.layouts.app')

@section('style')
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
            <a href="{{ route('admin.receipt.create') }}" class="btn btn-primary">Add Receipt</a>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12 col-lg-12">
            <div class="card">
                <div class="card-body">
                    <form id="receiptFilterForm" class="row g-3 mb-3" onsubmit="return false;">
                        <div class="col-md-2">
                            <label for="filter_receipt_no" class="form-label">Receipt Number</label>
                            <input
                                type="text"
                                id="filter_receipt_no"
                                name="receipt_no"
                                class="form-control"
                                value="{{ request('receipt_no') }}"
                                placeholder="Enter receipt number">
                        </div>
                        <div class="col-md-2">
                            <label for="filter_date_from" class="form-label">Start Date</label>
                            <input
                                type="date"
                                id="filter_date_from"
                                name="date_from"
                                class="form-control"
                                value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="filter_salesperson_id" class="form-label">Salesperson</label>
                            <select id="filter_salesperson_id" name="salesperson_id" class="form-select">
                                <option value="">All</option>
                                @foreach ($salespersons as $salesperson)
                                    <option value="{{ $salesperson->id }}" {{ (string) request('salesperson_id') === (string) $salesperson->id ? 'selected' : '' }}>
                                        {{ $salesperson->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="filter_date_to" class="form-label">End Date</label>
                            <input
                                type="date"
                                id="filter_date_to"
                                name="date_to"
                                class="form-control"
                                value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="filter_mode" class="form-label">Mode</label>
                            <select id="filter_mode" name="mode" class="form-select">
                                <option value="">All</option>
                                <option value="cash" {{ request('mode') === 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="upi" {{ request('mode') === 'upi' ? 'selected' : '' }}>UPI</option>
                                <option value="bank" {{ request('mode') === 'bank' ? 'selected' : '' }}>RTGS / NEFT</option>
                                <option value="card" {{ request('mode') === 'chq' ? 'selected' : '' }}>Cheque</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="filter_status" class="form-label">Status</label>
                            <select id="filter_status" name="status" class="form-select">
                                <option value="">All</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="accpet" {{ request('status') === 'accpet' ? 'selected' : '' }}>Accept</option>
                            </select>
                        </div>
                        <div class="col-md-12 d-flex align-items-end gap-2">
                            <button type="button" id="applyReceiptFilters" class="btn btn-primary">Apply</button>
                            <button type="button" id="resetReceiptFilters" class="btn btn-outline-secondary">Reset</button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered" id="receiptTable">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>B./No.</th>
                                    <th>Bill Amt</th>
                                    <th>R. Amt</th>
                                    <th>Sales Person</th>
                                    <th>Firm Name</th>
                                    <th>Payment Mode</th>
                                    <th>Remark</th>
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
    const getReceiptUrl = "{{ route('admin.receipt.getall') }}";
    const indexReceiptUrl = "{{ route('admin.receipt.index') }}";
    const createReceiptUrl = "{{ route('admin.receipt.create') }}";
    const deleteReceiptUrl = "{{ route('admin.receipt.delete', ':id') }}";
    const editReceiptUrl = "{{ route('admin.receipt.edit', ':id') }}";
    const changeReceiptStatusUrl = "{{ route('admin.receipt.status', ':id') }}";
    const getPendingInvoicesUrl = "{{ route('get.pending.invoices', ':id') }}";
</script>
<script>
    let window.baseUrl = "{{ url('/') }}";
</script>
<!-- IMPORTANT: ye baad me hona chahiye -->
<script src="{{ asset('assets/admin/customjs/receipt/index.js') }}"></script>
@endsection



