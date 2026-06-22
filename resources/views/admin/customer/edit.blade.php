@extends('admin.layouts.app')

@section('style')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')

<div class="container-fluid flex-grow-1 container-p-y">

    <div class="row">
        <div class="col-md-6 text-start">
            <h5 class="py-2 mb-2">
                <span class="text-primary fw-light">Customer Management</span>
            </h5>
        </div>

        <div class="col-md-6 text-end">
            <a href="{{ route('admin.customer.index') }}" class="btn btn-primary">
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
                            <span class="text-primary fw-bold">Edit Customer</span>
                        </h5>
                        <hr>
                    </div>

                    <form id="editCustomerForm" action="{{ route('admin.customer.update', $customer->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="id" value="{{ $customer->id }}">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Firm Name <span class="text-danger">*</span></label>
                                <input type="text" name="firm_name" class="form-control"
                                       value="{{ old('firm_name', $customer->firm_name) }}">
                                <small class="text-danger error-text firm_name_error"></small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone <span class="text-danger">*</span></label>
                                <input type="text" name="phone" class="form-control"
                                       value="{{ old('phone', $customer->phone) }}">
                                <small class="text-danger error-text phone_error"></small>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-success">
                                Update Customer
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
    const indexCustomerUrl = "{{ route('admin.customer.index') }}";
    const updateCustomerUrl = "{{ route('admin.customer.update', $customer->id) }}";
</script>
<script src="{{asset('assets/admin/customjs/customer/index.js')}}"></script>
@endsection
