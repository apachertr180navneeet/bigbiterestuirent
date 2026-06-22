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
                href="{{route('admin.salesperson.create')}}" 
                class="btn btn-primary"
            >
                Add Sales Person
            </a>
        </div>
    </div>

    <!-- Sales Person Table -->
    <div class="row">
        <div class="col-xl-12 col-lg-12">
            <div class="card">
                <div class="card-body">

                    <div class="table-responsive text-nowrap">
                        <table class="table table-bordered" id="salespersonTable">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Mobile</th>
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


@section('script')
<script>
    const getSalespersonUrl = "{{ route('admin.salesperson.getall') }}";
    const createSalespersonUrl = "{{ route('admin.salesperson.create') }}";
    const deleteSalespersonUrl = "{{ route('admin.salesperson.delete', ':id') }}";
    const changeStatusUrl = "{{ route('admin.salesperson.status', ':id') }}";
    const editSalespersonUrl = "{{ route('admin.salesperson.edit', ':id') }}";
</script>
<script src="{{asset('assets/admin/customjs/salesperson/index.js')}}"></script>
@endsection