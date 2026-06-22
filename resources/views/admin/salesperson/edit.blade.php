@extends('admin.layouts.app')

@section('style')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')

<div class="container-fluid flex-grow-1 container-p-y">

    <!-- Page Header -->
    <div class="row">
        <div class="col-md-6 text-start">
            <h5 class="py-2 mb-2">
                <span class="text-primary fw-light">Sales Person Management</span>
            </h5>
        </div>

        <div class="col-md-6 text-end">
            <a href="{{ route('admin.salesperson.index') }}" class="btn btn-primary">
                Back
            </a>
        </div>
    </div>

    <!-- Form Card -->
    <div class="row">
        <div class="col-xl-12 col-lg-12">
            <div class="card">
                <div class="card-body">

                    <!-- Form Heading -->
                    <div class="mb-4">
                        <h5 class="card-title">
                            <span class="text-primary fw-bold">Edit Salesperson</span>
                        </h5>
                        <hr>
                    </div>

                    <form id="editsalespersonForm" 
                        action="{{ route('admin.salesperson.update', $salesperson->id) }}" 
                        method="POST">
                        @csrf
                        <input type="hidden" name="id" value="{{ $salesperson->id }}">

                        <div class="row">

                            <!-- Salesperson Code -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Salesperson Code</label>
                                <input type="text" 
                                       name="salesperson_code" 
                                       class="form-control"
                                       value="{{ old('salesperson_code', $salesperson->salesperson_code) }}">
                                <small class="text-danger error-text salesperson_code_error"></small>
                            </div>

                            <!-- Name -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" 
                                       name="name" 
                                       class="form-control"
                                       value="{{ old('name', $salesperson->name) }}">
                                <small class="text-danger error-text name_error"></small>
                            </div>

                            <!-- Mobile -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Mobile <span class="text-danger">*</span></label>
                                <input type="text" 
                                       name="mobile" 
                                       class="form-control"
                                       value="{{ old('mobile', $salesperson->mobile) }}">
                                <small class="text-danger error-text mobile_error"></small>
                            </div>

                            <!-- Email -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" 
                                       name="email" 
                                       class="form-control"
                                       value="{{ old('email', $salesperson->email) }}">
                                <small class="text-danger error-text email_error"></small>
                            </div>

                            <!-- Password -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" 
                                       name="password" 
                                       class="form-control"
                                       placeholder="Leave blank if not changing">
                                <small class="text-danger error-text password_error"></small>
                            </div>

                            <!-- Address -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Address</label>
                                <textarea name="address" 
                                          class="form-control" 
                                          rows="2">{{ old('address', $salesperson->address) }}</textarea>
                                <small class="text-danger error-text address_error"></small>
                            </div>

                            <!-- DOB -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" 
                                       name="dob" 
                                       class="form-control"
                                       value="{{ old('dob', $salesperson->dob) }}">
                                <small class="text-danger error-text dob_error"></small>
                            </div>

                            <!-- Alternative Phone -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Alternative Phone</label>
                                <input type="text" 
                                       name="alternative_phone" 
                                       class="form-control"
                                       value="{{ old('alternative_phone', $salesperson->alternative_phone) }}">
                                <small class="text-danger error-text alternative_phone_error"></small>
                            </div>

                        </div>

                        <!-- Submit Button -->
                        <div class="text-end">
                            <button type="submit" class="btn btn-success">
                                Update Salesperson
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

<script>
    const indexSalespersonUrl = "{{ route('admin.salesperson.index') }}";
    const updateSalespersonUrl = "{{ route('admin.salesperson.update', $salesperson->id) }}";
    const createSalespersonUrl = "{{ route('admin.salesperson.create') }}";
</script>
<script src="{{asset('assets/admin/customjs/salesperson/index.js')}}"></script>

@endsection
