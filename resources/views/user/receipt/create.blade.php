@extends('user.layouts.app')

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
            <a href="{{ route('user.dashboard') }}" class="btn btn-primary">Back</a>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12 col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="mb-4">
                        <h5 class="card-title"><span class="text-primary fw-bold">Add Receipt</span></h5>
                        <hr>
                    </div>

                    <form id="receiptForm" data-mode="create" action="{{ route('user.receipt.store') }}" method="POST">
                        @csrf
                        @if($selectedInvoice)
                            <input type="hidden" name="firm_id" value="{{ $selectedInvoice->firm_id }}">
                            <input type="hidden" name="invoice_id" value="{{ $selectedInvoice->id }}">
                        @endif

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" name="date" class="form-control" value="{{ old('date', now()->format('Y-m-d')) }}" readonly>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Receipt No. <span class="text-danger">*</span></label>
                                <input type="text" name="receipt_no" id="receipt_no" class="form-control" value="{{ old('receipt_no', $generatedReceiptNo) }}" readonly>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Firm Name <span class="text-danger">*</span></label>
                                @if($selectedInvoice)
                                    <input type="text" class="form-control" value="{{ optional($selectedInvoice->firm)->firm_name ?? '-' }}" readonly>
                                @else
                                    <select name="firm_id" id="firm_id" class="form-select">
                                        <option value="">Select Firm</option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}" {{ old('firm_id') == $customer->id ? 'selected' : '' }}>
                                                {{ $customer->firm_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Invoice <span class="text-danger">*</span></label>
                                @if($selectedInvoice)
                                    <input type="text" class="form-control" value="{{ $selectedInvoice->invoice_no }}" readonly>
                                    <select class="form-select d-none" id="invoice_id">
                                        <option value="{{ $selectedInvoice->id }}"
                                            selected
                                            data-amount="{{ $selectedInvoice->amount }}"
                                            data-payable="{{ $selectedInvoice->payable_amount }}"
                                            data-paid="{{ $selectedInvoice->paid_amount ?? 0 }}"
                                            data-sales-person="{{ optional($selectedInvoice->salesperson)->name }}">
                                            {{ $selectedInvoice->invoice_no }}
                                        </option>
                                    </select>
                                @else
                                    <select name="invoice_id" class="form-select" id="invoice_id">
                                        <option value="">Select Invoice</option>
                                    </select>
                                @endif
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Invoice Amount</label>
                                <input type="number" step="0.01" min="0" name="amount" id="amount" class="form-control" value="{{ $selectedInvoice ? number_format((float) $selectedInvoice->amount, 2, '.', '') : old('amount') }}" readonly>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Remaining Amount</label>
                                <input type="number" step="0.01" min="0" id="remaining_amount" class="form-control" value="" readonly>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Given Amount <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" name="given_amount" id="given_amount" class="form-control" value="{{ old('given_amount', 0) }}" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Sales Person</label>
                                <input type="text" name="sales_person" id="sales_person" class="form-control" placeholder="Auto from invoice" value="{{ $selectedInvoice ? optional($selectedInvoice->salesperson)->name : old('sales_person') }}" readonly>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Mode</label>
                                <select name="mode" class="form-select" required>
                                    <option value="">Select Mode</option>
                                    <option value="cash" {{ old('mode') === 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="upi" {{ old('mode') === 'upi' ? 'selected' : '' }}>UPI</option>
                                    <option value="bank" {{ old('mode') === 'bank' ? 'selected' : '' }}>RTGS / NEFT</option>
                                    <option value="card" {{ old('mode') === 'card' ? 'selected' : '' }}>Cheque</option>
                                </select>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Remark</label>
                                <input type="text" name="remark" id="remark" class="form-control" placeholder="Enter Remark" value="{{ old('remark') }}" required>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-success">Save Receipt</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function () {
        const indexReceiptUrl = "{{ route('user.dashboard') }}";
        const pendingInvoicesUrlTemplate = "{{ route('user.receipt.pending.invoices', ':firm_id') }}";
        const hasSelectedInvoice = {{ $selectedInvoice ? 'true' : 'false' }};

        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
            }
        });

        function getSelectedInvoiceData() {
            const selected = $("#invoice_id").find(":selected");

            return {
                id: String(selected.val() || ""),
                amount: parseFloat(selected.data("amount")) || 0,
                payable: parseFloat(selected.data("payable")) || 0,
                paid: parseFloat(selected.data("paid")) || 0,
                salesPerson: selected.data("sales-person") || ""
            };
        }

        function calculateRemaining() {
            const invoice = getSelectedInvoiceData();

            if (!invoice.id) {
                $("#remaining_amount").val("");
                return;
            }

            const given = parseFloat($("#given_amount").val()) || 0;
            let remaining = (invoice.payable - invoice.paid) - given;

            if (remaining < 0) {
                remaining = 0;
            }

            $("#remaining_amount").val(remaining.toFixed(2));
        }

        function loadInvoiceData() {
            const invoice = getSelectedInvoiceData();

            if (!invoice.id) {
                $("#amount").val("");
                $("#remaining_amount").val("");
                $("#sales_person").val("");
                return;
            }

            $("#amount").val(invoice.amount.toFixed(2));
            $("#sales_person").val(invoice.salesPerson);
            calculateRemaining();
        }

        $("#firm_id").on("change", function () {
            let firmId = $(this).val();
            let invoiceDropdown = $("#invoice_id");

            invoiceDropdown.html('<option value="">Loading...</option>');

            if (!firmId) {
                invoiceDropdown.html('<option value="">Select Invoice</option>');
                $("#amount").val("");
                $("#remaining_amount").val("");
                $("#sales_person").val("");
                return;
            }

            $.ajax({
                url: pendingInvoicesUrlTemplate.replace(":firm_id", firmId),
                type: "GET",
                success: function (data) {
                    invoiceDropdown.html('<option value="">Select Invoice</option>');

                    if (data.length === 0) {
                        invoiceDropdown.html('<option value="">No Pending Invoice</option>');
                        return;
                    }

                    $.each(data, function (index, invoice) {
                        let paid = invoice.paid_amount || 0;
                        let payable = invoice.payable_amount || 0;
                        let remaining = payable - paid;

                        invoiceDropdown.append(`
                            <option value="${invoice.id}"
                                data-amount="${invoice.amount}"
                                data-payable="${payable}"
                                data-paid="${paid}"
                                data-sales-person="${invoice.salesperson ? invoice.salesperson.name : ''}">
                                ${invoice.invoice_no} (Remaining: ${remaining})
                            </option>
                        `);
                    });
                },
                error: function () {
                    invoiceDropdown.html('<option value="">Unable to load invoices</option>');
                }
            });
        });

        $("#invoice_id").on("change", loadInvoiceData);
        $("#given_amount").on("input", calculateRemaining);

        if (hasSelectedInvoice) {
            loadInvoiceData();
        }

        $("#receiptForm").validate({
            rules: {
                date: { required: true },
                receipt_no: { required: true },
                firm_id: { required: true },
                invoice_id: { required: true },
                given_amount: { required: true, number: true, min: 0.01 }
            },
            messages: {
                date: { required: "Date is required" },
                receipt_no: { required: "Receipt number required" },
                firm_id: { required: "Select firm" },
                invoice_id: { required: "Select invoice" }
            },
            errorElement: "small",
            errorClass: "text-danger",
            submitHandler: function (formEl) {
                const $submitBtn = $(formEl).find("button[type='submit']");

                $.ajax({
                    url: $(formEl).attr("action"),
                    type: "POST",
                    data: $(formEl).serialize(),
                    beforeSend: function () {
                        $submitBtn.prop("disabled", true);
                    },
                    success: function (response) {
                        toastr.success(response.message || "Receipt saved");
                        window.location.href = indexReceiptUrl;
                    },
                    error: function (xhr) {
                        $submitBtn.prop("disabled", false);

                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            const firstError = Object.values(xhr.responseJSON.errors)[0];
                            toastr.error(Array.isArray(firstError) ? firstError[0] : firstError);
                            return;
                        }

                        toastr.error("Something went wrong");
                    }
                });

                return false;
            }
        });
    });
</script>
@endsection
