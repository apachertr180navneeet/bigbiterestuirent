/**
 * ---------------------------------------------------------
 * Sales Person Management Script
 * File: assets/admin/customjs/Customer/index.js
 * ---------------------------------------------------------
 */

$(document).ready(function () {

    /* ==========================================================
     * 1️⃣ Global AJAX Setup (CSRF Token)
     * ========================================================== */
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });


    /* ==========================================================
     * 2️⃣ DATATABLE INITIALIZATION (Only if table exists)
     * ========================================================== */

    if ($('#customerTable').length) {

        // Initialize DataTable
        const table = $('#customerTable').DataTable({

            processing: true,   // Show processing loader
            serverSide: true,   // Enable server-side processing
            autoWidth: false,   // Prevent DataTable from auto adjusting column width

            ajax: {
                url: getCustomerUrl, // Route for fetching customer data
                type: 'GET',            // HTTP method
            },

            // Define table columns
            columns: [

                // Firm Name
                {
                    data: "firm_name",
                    searchable: true
                },

                // Phone
                {
                    data: "phone",
                    searchable: true
                },

                // Status
                {
                    data: "status",
                    searchable: false,
                    render: function (data) {
                        return data === "active"
                            ? '<span class="badge bg-label-success">Active</span>'
                            : '<span class="badge bg-label-danger">Inactive</span>';
                    }
                },

                // Action
                {
                    data: "id",
                    searchable: false,
                    orderable: false,
                    render: function (data, type, row) {

                        const statusBtn = `
                            <button class="btn btn-sm ${row.status === 'inactive' ? 'btn-success' : 'btn-danger'} change-status me-1"
                                data-id="${data}"
                                data-status="${row.status}">
                                ${row.status === 'inactive' ? 'Activate' : 'Deactivate'}
                            </button>
                        `;

                        const editBtn = `
                            <a href="${editCustomerUrl.replace(':id', data)}"
                                class="btn btn-sm btn-warning me-1">
                                Edit
                            </a>
                        `;

                        const deleteBtn = `
                            <button class="btn btn-sm btn-danger delete-customer"
                                data-id="${data}">
                                Delete
                            </button>
                        `;

                        return `${statusBtn}${editBtn}${deleteBtn}`;
                    }
                }
            ],

            columnDefs: [
                { width: "30%", targets: 0 }, // Firm Name
                { width: "25%", targets: 1 }, // Phone
                { width: "15%", targets: 2 }, // Status
                { width: "30%", targets: 3 }  // Action
            ]

        });
    }


    /* ==========================================================
     * 3️⃣ KEYBOARD SHORTCUTS
     * ========================================================== */
    $(document).on('keydown', function (e) {

        // Prevent shortcut while typing inside input/textarea
        if ($(e.target).is('input, textarea')) {
            return;
        }

        // F2 → Open Create Page
        if (e.key === "F2") {
            e.preventDefault();
            window.location.href = createCustomerUrl;
        }

        // F1 → Back to Index Page
        if (e.key === "F1") {
            e.preventDefault();
            window.location.href = indexCustomerUrl;
        }
    });


    /* ==========================================================
     * 4️⃣ FORM VALIDATION (Only if form exists)
     * ========================================================== */
    if ($('#customerForm').length) {

        // Initialize jQuery validation on customer form
        $("#customerForm").validate({

            // Validation rules
            rules: {

                firm_name: {
                    required: true,
                    maxlength: 100
                },

                phone: {
                    required: true,
                    digits: true,
                    minlength: 10,
                    maxlength: 15
                },
            },

            // Custom validation messages
            messages: {
                firm_name: {
                    required: "Firm name is required"
                },

                phone: {
                    required: "Phone number is required",
                    digits: "Only numeric values allowed",
                    minlength: "Minimum 10 digits required"
                },
            },

            // Error styling
            errorElement: 'small',
            errorClass: 'text-danger',

            // When form is valid and submitted
            submitHandler: function (form) {

                let formData = $(form).serialize();

                $.ajax({
                    url: $(form).attr('action'),
                    type: "POST",
                    data: formData,

                    // Disable submit button before request
                    beforeSend: function () {
                        $("button[type=submit]").prop('disabled', true);
                    },

                    // On successful response
                    success: function (response) {

                        toastr.success("Customer saved successfully!");

                        form.reset();

                        // Redirect to index page after 2 seconds
                        setTimeout(function () {
                            window.location.href = indexCustomerUrl;
                        }, 2000);
                    },

                    // On validation or server error
                    error: function (xhr) {

                        $("button[type=submit]").prop('disabled', false);

                        if (xhr.status === 422) {

                            let errors = xhr.responseJSON.errors;

                            $.each(errors, function (key, value) {
                                toastr.error(value[0]);
                            });

                        } else {
                            toastr.error("Something went wrong! Please try again.");
                        }
                    }
                });

                return false; // Prevent default form submission
            }
        });
    }

    /* ==========================================================
    * DELETE Customer (AJAX + SWEETALERT CONFIRMATION)
    * ==========================================================
    * This handles delete button click using delegated event.
    * Steps:
    * 1. Capture Customer ID
    * 2. Show SweetAlert confirmation popup
    * 3. If confirmed → Send DELETE request via AJAX
    * 4. On success → Show success message & reload DataTable
    * ========================================================== */

    $(document).on('click', '.delete-customer', function () {

        /*----------------------------------------------------------
        | Get Customer ID from data attribute
        | Example: <button data-id="5">
        ----------------------------------------------------------*/
        let id = $(this).data('id');


        /*----------------------------------------------------------
        | Show SweetAlert Confirmation Popup
        ----------------------------------------------------------*/
        Swal.fire({
            title: "Are you sure?",
            text: "This record will be permanently deleted!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {

            /*------------------------------------------------------
            | If User Confirms Delete
            ------------------------------------------------------*/
            if (result.isConfirmed) {

                /*--------------------------------------------------
                | Replace ':id' Placeholder in Route with Real ID
                | Example:
                | /admin/Customer/delete/:id
                | becomes
                | /admin/Customer/delete/5
                --------------------------------------------------*/
                let url = deleteCustomerUrl.replace(':id', id);


                /*--------------------------------------------------
                | Send AJAX DELETE Request to Server
                --------------------------------------------------*/
                $.ajax({
                    url: url,
                    type: "DELETE",

                    /*----------------------------------------------
                    | On Successful Delete
                    ----------------------------------------------*/
                    success: function (response) {

                        // Show success notification
                        toastr.success(response.message);

                        // Reload DataTable without refreshing page
                        if ($.fn.DataTable.isDataTable('#customerTable')) {
                            $('#customerTable').DataTable().ajax.reload(null, false);
                        }
                    },

                    /*----------------------------------------------
                    | On Error (Server/Network Issue)
                    ----------------------------------------------*/
                    error: function () {
                        toastr.error("Something went wrong. Please try again.");
                    }
                });

            }
        });

    });


    /* ==========================================================
    * CHANGE Customer STATUS (AJAX + CONFIRMATION)
    * ==========================================================
    * Flow:
    * 1. Capture ID & current status
    * 2. Ask confirmation
    * 3. Send AJAX request
    * 4. Reload DataTable on success
    * ========================================================== */

    $(document).on('click', '.change-status', function () {

        /*----------------------------------------------------------
        | Get ID & Current Status from Button Data Attributes
        ----------------------------------------------------------*/
        let id = $(this).data('id');
        let currentStatus = $(this).data('status');

        // Determine new status
        let newStatus = currentStatus === 'active' ? 'inactive' : 'active';

        /*----------------------------------------------------------
        | Show Confirmation Popup
        ----------------------------------------------------------*/
        Swal.fire({
            title: "Are you sure?",
            text: `Do you want to ${newStatus} this Customer?`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: `Yes, ${newStatus} it!`
        }).then((result) => {

            if (result.isConfirmed) {

                /*--------------------------------------------------
                | Replace :id Placeholder in Route
                --------------------------------------------------*/
                let url = changeStatusUrl.replace(':id', id);

                /*--------------------------------------------------
                | Send AJAX Request
                --------------------------------------------------*/
                $.ajax({
                    url: url,
                    type: "POST",
                    data: {
                        status: newStatus
                    },

                    /*----------------------------------------------
                    | On Success
                    ----------------------------------------------*/
                    success: function (response) {

                        toastr.success(response.message);

                        // Reload DataTable without page refresh
                        if ($.fn.DataTable.isDataTable('#customerTable')) {
                            $('#customerTable').DataTable().ajax.reload(null, false);
                        }
                    },

                    /*----------------------------------------------
                    | On Error
                    ----------------------------------------------*/
                    error: function () {
                        toastr.error("Something went wrong. Please try again.");
                    }
                });

            }
        });

    });

    // ============================================================
    // Edit Customer Form Submit Event
    // ============================================================
    $('#editCustomerForm').on('submit', function (e) {

        // Prevent default form submission (page reload)
        e.preventDefault();

        // ============================================================
        // Clear All Previous Error Messages
        // ============================================================
        $('.error-text').text('');

        // ============================================================
        // Get Input Field Values (Trim removes extra spaces)
        // ============================================================
        let firmName = $('input[name="firm_name"]').val().trim();
        let phone = $('input[name="phone"]').val().trim();

        // Flag to check if form is valid
        let isValid = true;
        // ============================================================
        // FIRM NAME VALIDATION
        // - Required
        // - Max 100 chars
        // ============================================================
        if (firmName === '') {
            $('.firm_name_error').text('Firm name is required.');
            isValid = false;
        }
        else if (firmName.length > 100) {
            $('.firm_name_error').text('Firm name must not exceed 100 characters.');
            isValid = false;
        }

        // ============================================================
        // PHONE VALIDATION
        // - Required
        // - Digits only
        // - 10 to 15 digits
        // ============================================================
        if (phone === '') {
            $('.phone_error').text('Phone number is required.');
            isValid = false;
        }
        else if (!/^[0-9]+$/.test(phone)) {
            $('.phone_error').text('Phone number must contain only digits.');
            isValid = false;
        }
        else if (phone.length < 10 || phone.length > 15) {
            $('.phone_error').text('Phone number must be between 10 and 15 digits.');
            isValid = false;
        }

        // ============================================================
        // Stop Form Submission if Client Validation Fails
        // ============================================================
        if (!isValid) {
            return;
        }

        // ============================================================
        // Create FormData Object
        // (Supports file uploads in future if needed)
        // ============================================================
        let formData = new FormData(this);

        // ============================================================
        // AJAX Request to Update Customer
        // ============================================================
        $.ajax({

            url: updateCustomerUrl,   // Update route URL
            type: "POST",                // HTTP Method
            data: formData,              // Form data
            processData: false,          // Required for FormData
            contentType: false,          // Required for FormData

            // ========================================================
            // SUCCESS RESPONSE
            // ========================================================
            success: function (response) {

                // Check if update successful
                if (response.status === true) {

                    // Show success message using Toastr
                    toastr.success(response.message);

                    // Redirect to index page after 1 second
                    setTimeout(function () {
                        window.location.href = indexCustomerUrl;
                    }, 1000);
                }
            },

            // ========================================================
            // ERROR RESPONSE (Laravel Validation Errors)
            // ========================================================
            error: function (xhr) {

                // Handle Laravel 422 validation error response
                if (xhr.status === 422) {

                    // Loop through each validation error
                    $.each(xhr.responseJSON.errors, function (key, value) {

                        // Display error message under respective field
                        $('.' + key + '_error').text(value[0]);

                    });
                }
            }

        });

    });

});
