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
            <a href="{{ route('admin.receipt.create') }}" class="btn btn-primary">Add Receipt</a>
            <button type="button" id="exportExcelBtn" class="btn btn-success">Export Excel</button>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12 col-lg-12">
            <div class="card">
                <div class="card-body">
                    <form id="receiptFilterForm" class="row g-3 mb-3" onsubmit="return false;">
                        <div class="col-md-3">
                            <label for="global_search" class="form-label">Search</label>
                            <input
                                type="text"
                                id="global_search"
                                name="search"
                                class="form-control"
                                value="{{ request('search') }}"
                                placeholder="Search by receipt no, invoice no, or amount">
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
                            <label for="filter_date_to" class="form-label">End Date</label>
                            <input
                                type="date"
                                id="filter_date_to"
                                name="date_to"
                                class="form-control"
                                value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="filter_firm_id" class="form-label">Firm Name</label>
                            <select id="filter_firm_id" name="firm_id" class="form-select searchable-select" data-placeholder="Select Firm">
                                <option value="">All</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ (string) request('firm_id') === (string) $customer->id ? 'selected' : '' }}>
                                        {{ $customer->firm_name }}
                                    </option>
                                @endforeach
                            </select>
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
                                    @if(\App\Helpers\Helper::isSuperAdmin())<th>Company</th>@endif
                                    <th>Date</th>
                                    <th>B./No.</th>
                                    <th>Bill Amt</th>
                                    <th>CD/DSC</th>
                                    <th>R. Amt</th>
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

{{-- Discount Modal --}}
<div class="modal fade" id="discountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Enter Discount Value</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="discount_receipt_id" value="">
                <input type="hidden" id="discount_type_val" value="">
                <div class="mb-3">
                    <label for="discount_value" class="form-label">Discount Amount</label>
                    <input type="number" step="0.01" min="0" id="discount_value" class="form-control" placeholder="Enter discount amount">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmDiscountBtn">Save</button>
            </div>
        </div>
    </div>
</div>

{{-- Approval Remark Modal --}}
<div class="modal fade" id="approvalRemarkModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Approve Receipt</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="approval_receipt_id" value="">
                <input type="hidden" id="approval_status" value="">
                <div class="mb-3">
                    <label for="approval_remark" class="form-label">Tally Recipt</label>
                    <textarea id="approval_remark" class="form-control" rows="3" placeholder="Enter remark for approval"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmApprovalBtn">Approve</button>
            </div>
        </div>
    </div>
</div>

@section('script')
<script>
    const getReceiptUrl = "{{ route('admin.receipt.getall') }}";
    const indexReceiptUrl = "{{ route('admin.receipt.index') }}";
    const createReceiptUrl = "{{ route('admin.receipt.create') }}";
    const deleteReceiptUrl = "{{ route('admin.receipt.delete', ':id') }}";
    const editReceiptUrl = "{{ route('admin.receipt.edit', ':id') }}";
    const changeReceiptStatusUrl = "{{ route('admin.receipt.status', ':id') }}";
    const getPendingInvoicesUrl = "{{ route('get.pending.invoices', ':id') }}";
    const updateDiscountTypeUrl = "{{ route('admin.receipt.updateDiscountType', ':id') }}";
    const exportReceiptExcelUrl = "{{ route('admin.receipt.excel') }}";
    const isSuperAdmin = {{ \App\Helpers\Helper::isSuperAdmin() ? 'true' : 'false' }};
</script>
<script>
    let window.baseUrl = "{{ url('/') }}";
</script>
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
<!-- IMPORTANT: ye baad me hona chahiye -->
<script src="{{ asset('assets/admin/customjs/receipt/index.js') }}"></script>
@endsection



