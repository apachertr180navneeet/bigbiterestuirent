/**
 * ---------------------------------------------------------
 * Invoice Management Script
 * File: assets/admin/customjs/invoice/index.js
 * ---------------------------------------------------------
 */

$(document).ready(function () {
    let invoiceTable = null;

    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
        }
    });

    if ($("#invoiceTable").length) {
        invoiceTable = $("#invoiceTable").DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            searching: false,
            ajax: {
                url: getInvoiceUrl,
                type: "GET",
                data: function (d) {
                    d.invoice_no = $("#filter_invoice_no").val();
                    d.salesperson_id = $("#filter_salesperson_id").val();
                    d.date_from = $("#filter_date_from").val();
                    d.date_to = $("#filter_date_to").val();
                    d.status = $("#filter_status").val();
                }
            },
            columns: [

                { data: "invoice_no", searchable: true },

                { data: "date", searchable: true },

                { data: "firm_name", searchable: true },

                { data: "salesperson_name", searchable: true },

                {
                    data: "amount",
                    searchable: false,
                    render: function (data) {
                        return Number(data).toFixed(2);
                    }
                },

                {
                    data: "discount_percent",
                    searchable: false,
                    render: function (data) {
                        return data ? Number(data).toFixed(2) + " %" : "0 %";
                    }
                },

                {
                    data: "payable_amount",
                    searchable: false,
                    render: function (data) {
                        return data ? Number(data).toFixed(2) : "0.00";
                    }
                },

                {
                    data: "status",
                    searchable: true,
                    render: function (data) {

                        if (data === "full_paid") {
                            return '<span class="badge bg-label-success">Full Paid</span>';
                        }

                        return '<span class="badge bg-label-warning">Pending</span>';
                    }
                },

                {
                    data: "id",
                    searchable: false,
                    orderable: false,
                    render: function (data) {

                        const editBtn = `
                            <a href="${editInvoiceUrl.replace(":id", data)}" class="btn btn-sm btn-warning me-1">
                                Edit
                            </a>
                        `;

                        const deleteBtn = `
                            <button class="btn btn-sm btn-danger delete-invoice" data-id="${data}">
                                Delete
                            </button>
                        `;

                        return `${editBtn}${deleteBtn}`;
                    }
                }

            ]
        });
    }

    $(document).on("click", "#applyInvoiceFilters", function () {
        if (invoiceTable) {
            invoiceTable.ajax.reload();
        }
    });

    $(document).on("click", "#resetInvoiceFilters", function () {
        $("#filter_invoice_no").val("");
        $("#filter_salesperson_id").val("");
        $("#filter_date_from").val("");
        $("#filter_date_to").val("");
        $("#filter_status").val("");

        if (invoiceTable) {
            invoiceTable.ajax.reload();
        }
    });

    $(document).on("keydown", function (e) {
        if ($(e.target).is("input, textarea, select")) {
            return;
        }

        if (e.key === "F2" && typeof createInvoiceUrl !== "undefined") {
            e.preventDefault();
            window.location.href = createInvoiceUrl;
        }

        if (e.key === "F1" && typeof indexInvoiceUrl !== "undefined") {
            e.preventDefault();
            window.location.href = indexInvoiceUrl;
        }
    });

    $(document).on("click", ".delete-invoice", function () {
        const id = $(this).data("id");

        Swal.fire({
            title: "Are you sure?",
            text: "This invoice will be permanently deleted!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }

            const url = deleteInvoiceUrl.replace(":id", id);

            $.ajax({
                url: url,
                type: "DELETE",
                success: function (response) {
                    toastr.success(response.message || "Invoice deleted successfully.");

                    if ($.fn.DataTable.isDataTable("#invoiceTable")) {
                        $("#invoiceTable").DataTable().ajax.reload(null, false);
                    }
                },
                error: function () {
                    toastr.error("Something went wrong. Please try again.");
                }
            });
        });
    });

    if ($("#invoiceForm").length) {
        $("#invoiceForm").validate({
            rules: {
                date: { required: true },
                invoice_no: { required: true },
                firm_id: { required: true },
                salesperson_id: { required: true },
                amount: { required: true, number: true, min: 0 }
            },
            messages: {
                date: { required: "Date is required" },
                invoice_no: { required: "Invoice number is required" },
                firm_id: { required: "Please select a firm" },
                salesperson_id: { required: "Please select a salesperson" },
                amount: {
                    required: "Amount is required",
                    number: "Amount must be numeric",
                    min: "Amount must be 0 or more"
                }
            },
            errorElement: "small",
            errorClass: "text-danger",
            submitHandler: function (form) {
                const $submitBtn = $(form).find("button[type='submit']");
                const isEditMode = $(form).data("mode") === "edit";

                $.ajax({
                    url: $(form).attr("action"),
                    type: "POST",
                    data: $(form).serialize(),
                    beforeSend: function () {
                        $submitBtn.prop("disabled", true);
                    },
                    success: function (response) {
                        toastr.success(response.message || "Invoice saved successfully!");

                        if (!isEditMode) {
                            form.reset();
                        }

                        setTimeout(function () {
                            if (typeof indexInvoiceUrl !== "undefined") {
                                window.location.href = indexInvoiceUrl;
                            }
                        }, 1000);
                    },
                    error: function (xhr) {
                        $submitBtn.prop("disabled", false);

                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            $.each(xhr.responseJSON.errors, function (key, value) {
                                toastr.error(value[0]);
                            });
                        } else {
                            toastr.error("Something went wrong! Please try again.");
                        }
                    }
                });

                return false;
            }
        });
    }


    $(document).on('keyup change','#amount,#discount_percent',function(){

        let amount = parseFloat($('#amount').val()) || 0;
        let percent = parseFloat($('#discount_percent').val()) || 0;

        let discount = (amount * percent) / 100;
        let payable = amount - discount;

        $('#discount_amount').val(discount.toFixed(2));
        $('#payable_amount').val(payable.toFixed(2));

    });
});
