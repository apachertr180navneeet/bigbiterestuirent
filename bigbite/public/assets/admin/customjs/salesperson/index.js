/**
 * ---------------------------------------------------------
 * Sales Person Management Script
 * File: assets/admin/customjs/salesperson/index.js
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
    if ($('#salespersonTable').length) {

        const table = $('#salespersonTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: getSalespersonUrl,
                type: 'GET',
            },
            columns: [

                { data: "salesperson_code", searchable: true },
                { data: "name", searchable: true },
                { data: "mobile", searchable: false },

                {
                    data: "status",
                    searchable: false,
                    render: function (data) {
                        return data === "active"
                            ? '<span class="badge bg-label-success">Active</span>'
                            : '<span class="badge bg-label-danger">Inactive</span>';
                    }
                },

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
                            <a href="${editSalespersonUrl.replace(':id', data)}"
                                class="btn btn-sm btn-warning me-1">
                                Edit
                            </a>
                        `;

                        const deleteBtn = `
                            <button class="btn btn-sm btn-danger delete-salesperson"
                                data-id="${data}">
                                Delete
                            </button>
                        `;

                        return `${statusBtn}${editBtn}${deleteBtn}`;
                    }
                }
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
            window.location.href = createSalespersonUrl;
        }

        // F1 → Back to Index Page
        if (e.key === "F1") {
            e.preventDefault();
            window.location.href = indexSalespersonUrl;
        }
    });


    /* ==========================================================
     * 4️⃣ FORM VALIDATION (Only if form exists)
     * ========================================================== */
    if ($('#salespersonForm').length) {

        $("#salespersonForm").validate({

            rules: {
                salesperson_code: { required: true },
                name: { required: true, minlength: 3, maxlength: 50 },
                mobile: { required: true, digits: true, minlength: 10, maxlength: 15 },
                email: { required: false, email: true },
                password: { required: true, minlength: 6 }
            },

            messages: {
                salesperson_code: { required: "Salesperson code is required" },
                name: {
                    required: "Name is required",
                    minlength: "Minimum 3 characters required"
                },
                mobile: {
                    required: "Mobile number is required",
                    digits: "Only numeric values allowed"
                },
                email: {
                    email: "Enter a valid email address"
                },
                password: {
                    required: "Password is required",
                    minlength: "Minimum 6 characters required"
                }
            },

            errorElement: 'small',
            errorClass: 'text-danger',

            submitHandler: function (form) {

                let formData = $(form).serialize();

                $.ajax({
                    url: $(form).attr('action'),
                    type: "POST",
                    data: formData,

                    beforeSend: function () {
                        $("button[type=submit]").prop('disabled', true);
                    },

                    success: function (response) {

                        toastr.success("Salesperson saved successfully!");

                        form.reset();

                        setTimeout(function () {
                            window.location.href = indexSalespersonUrl;
                        }, 2000);
                    },

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

                return false;
            }
        });
    }

    /* ==========================================================
    * DELETE SALESPERSON (AJAX + SWEETALERT CONFIRMATION)
    * ==========================================================
    * This handles delete button click using delegated event.
    * Steps:
    * 1. Capture salesperson ID
    * 2. Show SweetAlert confirmation popup
    * 3. If confirmed → Send DELETE request via AJAX
    * 4. On success → Show success message & reload DataTable
    * ========================================================== */

    $(document).on('click', '.delete-salesperson', function () {

        /*----------------------------------------------------------
        | Get Salesperson ID from data attribute
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
                | /admin/salesperson/delete/:id
                | becomes
                | /admin/salesperson/delete/5
                --------------------------------------------------*/
                let url = deleteSalespersonUrl.replace(':id', id);


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
                        $('#salespersonTable')
                            .DataTable()
                            .ajax.reload(null, false);
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
    * CHANGE SALESPERSON STATUS (AJAX + CONFIRMATION)
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
            text: `Do you want to ${newStatus} this salesperson?`,
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
                        $('#salespersonTable')
                            .DataTable()
                            .ajax.reload(null, false);
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
    // Edit Salesperson Form Submit Event
    // ============================================================
    $('#editsalespersonForm').on('submit', function (e) {

            // Prevent default form submission (page reload)
            e.preventDefault();

            // ============================================================
            // Clear All Previous Error Messages
            // ============================================================
            $('.error-text').text('');

            // ============================================================
            // Get Input Field Values (Trim removes extra spaces)
            // ============================================================
            let name = $('input[name="name"]').val().trim();
            let mobile = $('input[name="mobile"]').val().trim();
            let email = $('input[name="email"]').val().trim();
            let password = $('input[name="password"]').val().trim();

            // Flag to check if form is valid
            let isValid = true;

            // ============================================================
            // NAME VALIDATION
            // - Required
            // - Only alphabets and spaces
            // - Min 3 characters
            // - Max 50 characters
            // ============================================================
            if (name === '') {
                $('.name_error').text('Name is required.');
                isValid = false;
            } 
            else if (name.length < 3 || name.length > 50) {
                $('.name_error').text('Name must be between 3 and 50 characters.');
                isValid = false;
            }

            // ============================================================
            // MOBILE VALIDATION
            // - Required
            // - Digits only
            // - 10 to 15 digits
            // ============================================================
            if (mobile === '') {
                $('.mobile_error').text('Mobile number is required.');
                isValid = false;
            } 
            else if (!/^[0-9]+$/.test(mobile)) {
                $('.mobile_error').text('Mobile number must contain only digits.');
                isValid = false;
            } 
            else if (mobile.length < 10 || mobile.length > 15) {
                $('.mobile_error').text('Mobile number must be between 10 and 15 digits.');
                isValid = false;
            }

            // ============================================================
            // PASSWORD VALIDATION (Optional Field)
            // - If filled, minimum 6 characters required
            // ============================================================
            if (password !== '' && password.length < 6) {
                $('.password_error').text('Password must be at least 6 characters.');
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
            // AJAX Request to Update Salesperson
            // ============================================================
            $.ajax({

                url: updateSalespersonUrl,   // Update route URL
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
                            window.location.href = indexSalespersonUrl;
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
