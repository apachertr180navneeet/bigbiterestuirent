@extends('admin.layouts.app')

@section('style')
@endsection

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">

    <div class="row">
        <div class="col-md-6 text-start">
            <h5 class="py-2 mb-3">
                <span class="text-primary fw-light">Sales Man Report</span>
            </h5>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12 col-lg-12">
            <div class="card">
                <div class="card-body">

                    {{-- Filter Section --}}
                    <form method="GET" action="{{ route('admin.sales.person.report') }}">
                        <div class="row mb-3">

                            <div class="col-md-4">
                                <label class="form-label">Select Sales Person</label>
                                <select name="salesman_id" class="form-control">
                                    <option value="">All Sales Person</option>

                                    @foreach($salesmen as $salesman)
                                        <option value="{{ $salesman->id }}"
                                            {{ $salesmanId == $salesman->id ? 'selected' : '' }}>
                                            {{ $salesman->name }}
                                        </option>
                                    @endforeach

                                </select>
                            </div>

                            <div class="col-md-3 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    Filter
                                </button>

                                <a href="{{ route('admin.sales.person.report') }}" class="btn btn-secondary">
                                    Reset
                                </a>
                            </div>

                        </div>
                    </form>

                    <div class="mb-3">
                        <a href="{{ route('admin.sales.person.report.excel', request()->all()) }}" 
                        class="btn btn-success">
                            Export Excel
                        </a>

                        <a href="{{ route('admin.sales.person.report.pdf', request()->all()) }}" 
                        class="btn btn-danger">
                            Export PDF
                        </a>
                    </div>

                    {{-- Table Section --}}
                    <div class="table-responsive text-nowrap">
                        <table class="table table-bordered" id="receiptTable">

                            <thead>
                                <tr>
                                    <th>Invoice No.</th>
                                    <th>Date</th>
                                    <th>Firm Name</th>
                                    <th>Sales Person</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($reports as $report)
                                    <tr>
                                        <td>{{ $report->invoice_no }}</td>
                                        <td>{{ \Carbon\Carbon::parse($report->date)->format('d/m/Y') }}</td>
                                        <td>{{ $report->firm_name }}</td>
                                        <td>{{ $report->salesman_name }}</td>
                                        <td>{{ number_format($report->remaining_amount,2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No Data Found</td>
                                    </tr>
                                @endforelse
                            </tbody>

                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-end">Total</th>
                                    <th>{{ number_format($totalAmount,2) }}</th>
                                </tr>
                            </tfoot>

                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>
@endsection


@section('script')
@endsection