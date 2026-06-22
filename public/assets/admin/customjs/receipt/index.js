$(document).ready(function () {

    let receiptTable = null;

    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
        }
    });

    /*
    =========================
    STATUS BADGE
    =========================
    */

    function renderStatusBadge(value) {

        if (value === "accpet") {
            return '<span class="badge bg-label-success">Accept</span>';
        }

        if (value === "rejected") {
            return '<span class="badge bg-label-danger">Rejected</span>';
        }

        return '<span class="badge bg-label-warning">Pending</span>';
    }

    /*
    =========================
    DATATABLE
    =========================
    */

    if ($("#receiptTable").length) {

        receiptTable = $("#receiptTable").DataTable({

            processing: true,
            serverSide: true,
            autoWidth: true,
            searching: false,

            ajax: {
                url: getReceiptUrl,
                type: "GET",
                data: function (d) {

                    d.search = $("#global_search").val();
                    d.receipt_no = $("#filter_receipt_no").val();
                    d.date_from = $("#filter_date_from").val();
                    d.date_to = $("#filter_date_to").val();
                    d.mode = $("#filter_mode").val();
                    d.firm_id = $("#filter_firm_id").val();
                    d.salesperson_id = $("#filter_salesperson_id").val();
                    d.manager_status = $("#filter_manager_status").val();
                    d.status = $("#filter_status").val();

                }
            },

            columns: [

                ...(typeof isSuperAdmin !== 'undefined' && isSuperAdmin ? [{ data: "user", searchable: false, render: data => data ? data.full_name : '-' }] : []),

                { data: "date" },
                { data: "invoice_no", defaultContent: "-" },

                {
                    data: "amount",
                    render: data => Number(data || 0).toFixed(2)
                },

                {
                    data: "given_amount",
                    render: data => Number(data || 0).toFixed(2)
                },

                {
                    data: "firm_name",
                    defaultContent: "-"
                },

                {
                    data: "mode",
                    render: function (data) {

                        if (!data) return "-";

                        let mode = data.toLowerCase();

                        if (mode === "bank") {
                            return "RTGS / NEFT";
                        }

                        if (mode === "card") {
                            return "Cheque";
                        }

                        return data.toUpperCase();
                    }
                },

                {
                    data: "remark",
                    defaultContent: "-"
                },

                {
                    data: "status",
                    render: function (data, type, row) {

                        var canApprove = typeof isSuperAdmin !== 'undefined' && isSuperAdmin;

                        if (data === "pending") {
                            if (canApprove) {
                                return `
                                    <button class="btn btn-sm btn-success change-receipt-status"
                                        data-id="${row.id}"
                                        data-status="accpet">
                                        Approve
                                    </button>
                                `;
                            }
                            return '<span class="badge bg-label-warning">Pending</span>';
                        }

                        if (data === "accpet") {
                            var remarkHtml = row.approval_remark ? '<br><small class="text-muted">Tally: ' + row.approval_remark + '</small>' : '';
                            return '<span class="badge bg-label-success">Approved' + remarkHtml + '</span>';
                        }

                        if (data === "rejected") {
                            return '<span class="badge bg-label-danger">Rejected</span>';
                        }

                        return '<span class="badge bg-label-warning">Pending</span>';
                    }
                },

                {
                    data: "id",
                    orderable: false,
                    render: function (data) {

                        return `
                        <a href="${editReceiptUrl.replace(":id", data)}"
                        class="btn btn-sm btn-warning me-1">Edit</a>

                        <button class="btn btn-sm btn-danger delete-receipt"
                        data-id="${data}">Delete</button>`;
                    }
                }

            ]

        });

    }

    /*
    =========================
    RECEIPT FILTERS
    =========================
    */

    function reloadReceiptTable(resetPaging = true) {

        if (!receiptTable) {
            return;
        }

        receiptTable.ajax.reload(null, resetPaging);
    }

    $("#applyReceiptFilters").on("click", function () {
        reloadReceiptTable(true);
    });

    $("#resetReceiptFilters").on("click", function () {

        $("#global_search").val("");
        $("#filter_receipt_no").val("");
        $("#filter_date_from").val("");
        $("#filter_date_to").val("");
        $("#filter_firm_id").val("");
        $("#filter_salesperson_id").val("");
        $("#filter_mode").val("");
        $("#filter_manager_status").val("");
        $("#filter_status").val("");

        reloadReceiptTable(true);
    });

    $("#global_search, #filter_receipt_no, #filter_date_from, #filter_date_to").on("keypress", function (e) {

        if (e.which === 13) {
            reloadReceiptTable(true);
        }

    });

    $("#filter_firm_id, #filter_salesperson_id, #filter_mode, #filter_manager_status, #filter_status").on("change", function () {
        reloadReceiptTable(true);
    });

    /*
    =========================
    EXPORT EXCEL
    =========================
    */

    $(document).on("click", "#exportExcelBtn", function () {
        const params = new URLSearchParams();

        const fields = [
            "search", "receipt_no", "date_from", "date_to",
            "firm_id", "salesperson_id", "mode", "status"
        ];

        fields.forEach(function (field) {
            const val = $("#filter_" + field).val();
            if (val) {
                params.set(field, val);
            }
        });

        window.open(exportReceiptExcelUrl + "?" + params.toString(), "_blank");
    });

    /*
    =========================
    DELETE RECEIPT
    =========================
    */

    $(document).on("click", ".delete-receipt", function () {

        const id = $(this).data("id");

        Swal.fire({
            title: "Are you sure?",
            text: "This receipt will be permanently deleted!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {

            if (!result.isConfirmed) return;

            $.ajax({

                url: deleteReceiptUrl.replace(":id", id),
                type: "DELETE",

                success: function (response) {

                    toastr.success(response.message || "Receipt deleted");

                    receiptTable.ajax.reload(null, false);

                },

                error: function () {

                    toastr.error("Something went wrong");

                }

            });

        });

    });

    /*
    =========================
    FORM VALIDATION
    =========================
    */

    if ($("#receiptForm").length || $("#editReceiptForm").length) {

        const form = $("#receiptForm").length ? $("#receiptForm") : $("#editReceiptForm");

        form.validate({

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

                    error: function () {

                        $submitBtn.prop("disabled", false);

                        toastr.error("Something went wrong");

                    }

                });

                return false;
            }

        });

    }

    /*
    =========================
    EDIT MODE
    =========================
    */

    if ($("#editReceiptForm").length) {
        $("#invoice_id").prop("disabled", true);
    }

    /*
    =========================
    LOAD INVOICE DATA
    =========================
    */

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

    function isEditMode() {
        return $("#editReceiptForm").length > 0;
    }

    function getCurrentReceiptAdjustment(invoiceId) {

        if (!isEditMode()) {
            return 0;
        }

        const form = $("#editReceiptForm");
        const currentInvoiceId = String(form.data("current-invoice-id") || "");
        const currentGiven = parseFloat(form.data("current-given")) || 0;

        return currentInvoiceId === String(invoiceId) ? currentGiven : 0;
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

    /*
    =========================
    CALCULATE REMAINING
    =========================
    */

    function calculateRemaining() {

        const invoice = getSelectedInvoiceData();

        if (!invoice.id) {
            $("#remaining_amount").val("");
            return;
        }

        const given = parseFloat($("#given_amount").val()) || 0;
        const editableOutstanding = invoice.payable - invoice.paid + getCurrentReceiptAdjustment(invoice.id);
        let remaining = editableOutstanding - given;

        if (remaining < 0) {
            remaining = 0;
        }

        $("#remaining_amount").val(remaining.toFixed(2));
    }

    /*
    =========================
    EVENTS
    =========================
    */

    $("#invoice_id").on("change", loadInvoiceData);

    $("#given_amount").on("input", calculateRemaining);

    /*
    =========================
    PAGE LOAD
    =========================
    */

    if ($("#invoice_id").val()) {
        loadInvoiceData();
    }


    /*
    =========================
    CHANGE RECEIPT STATUS
    =========================
    */

    $(document).on("click", ".change-receipt-status", function () {

        const id = $(this).data("id");
        const status = $(this).data("status");

        $("#approval_receipt_id").val(id);
        $("#approval_status").val(status);
        $("#approval_remark").val("");
        $("#approvalRemarkModal").modal("show");

    });

    $("#confirmApprovalBtn").on("click", function () {

        const id = $("#approval_receipt_id").val();
        const status = $("#approval_status").val();
        const approval_remark = $("#approval_remark").val();

        $.ajax({

            url: changeReceiptStatusUrl.replace(":id", id),
            type: "POST",

            data: {
                status: status,
                approval_remark: approval_remark
            },

            success: function (response) {

                toastr.success(response.message || "Status updated");
                $("#approvalRemarkModal").modal("hide");
                receiptTable.ajax.reload(null, false);

            },

            error: function () {

                toastr.error("Something went wrong");

            }

        });

    });


     /*
    =========================
    LOAD CUSTOMER INVOICES
    =========================
    */

    $("#firm_id").on("change", function () {

        let firm_id = $(this).val();
        let invoiceDropdown = $("#invoice_id");

        invoiceDropdown.html('<option value="">Loading...</option>');

        if (firm_id !== "") {
            let finalUrl = "https://shrilalitrealestate.com/bigbite/public/get-pending-invoices/" + firm_id;
            $.ajax({

                url: finalUrl,
                type: "GET",

                success: function (data) {

                    invoiceDropdown.html('<option value="">Select Invoice</option>');

                    if (data.length > 0) {

                        $.each(data, function (index, invoice) {

                            let paid = invoice.paid_amount ?? 0;
                            let payable = invoice.payable_amount ?? 0;

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

                    } else {

                        invoiceDropdown.html('<option value="">No Pending Invoice</option>');

                    }

                }

            });

        } else {

            invoiceDropdown.html('<option value="">Select Invoice</option>');

        }

    });



});





