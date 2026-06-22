@extends('admin.layouts.app')

@section('style')
@endsection

@section('content')

<div class="container-fluid flex-grow-1 container-p-y">

    <!-- Page Header -->
    <div class="row">
        <div class="col-md-6 text-start">
            <h5 class="py-2 mb-2">
                <span class="text-primary fw-light">Customer Firms</span>
            </h5>
        </div>

        <div class="col-md-6 text-end">
            <a 
                href="{{route('admin.customer.index')}}" 
                class="btn btn-primary"
            >
                Back
            </a>
        </div>
    </div>

    <!-- Customer Form Card -->
    <div class="row">
        <div class="col-xl-12 col-lg-12">
            <div class="card">
                <div class="card-body">

                    <!-- Form Heading -->
                    <div class="mb-4">
                        <h5 class="card-title">
                            <span class="text-primary fw-bold">Add Firms</span>
                        </h5>
                        <hr>
                    </div>

                    <form id="customerForm" action="{{ route('admin.customer.store') }}" method="POST">
                        @csrf

                        <div class="row">

                            <!-- Firm Name -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Firm Name <span class="text-danger">*</span></label>
                                <input type="text" name="firm_name" class="form-control"
                                       placeholder="Enter Firm Name"
                                       value="{{ old('firm_name') }}">
                                @error('firm_name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Phone -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone <span class="text-danger">*</span></label>
                                <input type="text" name="phone" class="form-control"
                                       placeholder="Enter Phone Number"
                                       value="{{ old('phone') }}">
                                @error('phone')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                        </div>

                        <!-- Submit Button -->
                        <div class="text-end">
                            <button type="submit" class="btn btn-success">
                                Save Customer
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
</script>
<script src="{{asset('assets/admin/customjs/customer/index.js')}}"></script>
@endsection
