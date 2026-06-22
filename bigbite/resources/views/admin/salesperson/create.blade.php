@extends('admin.layouts.app')

@section('style')
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
            <a 
                href="{{route('admin.salesperson.index')}}" 
                class="btn btn-primary"
            >
                Back
            </a>
        </div>
    </div>

    <!-- Banner Table -->
    <div class="row">
        <div class="col-xl-12 col-lg-12">
            <div class="card">
                <div class="card-body">
                    <!-- Form Heading -->
                    <div class="mb-4">
                        <h5 class="card-title">
                            <span class="text-primary fw-bold">Add Salesperson</span>
                        </h5>
                        <hr>
                    </div>

                    <form id="salespersonForm" action="{{ route('admin.salesperson.store') }}" method="POST">
                        @csrf

                        <div class="row">

                            <!-- Salesperson Code -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Salesperson Code</label>
                                <input type="text" name="salesperson_code" class="form-control" placeholder="Enter Salesperson Code" value="{{ old('salesperson_code') }}">
                                @error('salesperson_code')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Name -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" placeholder="Enter Name" value="{{ old('name') }}">
                                @error('name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Mobile -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Mobile <span class="text-danger">*</span></label>
                                <input type="text" name="mobile" class="form-control" placeholder="Enter Mobile Number" value="{{ old('mobile') }}">
                                @error('mobile')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="Enter Email" value="{{ old('email') }}">
                                @error('email')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Password -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control" placeholder="Enter Password">
                                @error('password')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Address -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control" rows="2" placeholder="Enter Address">{{ old('address') }}</textarea>
                                @error('address')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- DOB -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" name="dob" class="form-control" value="{{ old('dob') }}">
                                @error('dob')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Alternative Phone -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Alternative Phone</label>
                                <input type="text" name="alternative_phone" class="form-control" placeholder="Enter Alternative Phone" value="{{ old('alternative_phone') }}">
                                @error('alternative_phone')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                        </div>

                        <!-- Submit Button -->
                        <div class="text-end">
                            <button type="submit" class="btn btn-success">
                                Save Salesperson
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
</script>


<script src="{{asset('assets/admin/customjs/salesperson/index.js')}}"></script>
@endsection