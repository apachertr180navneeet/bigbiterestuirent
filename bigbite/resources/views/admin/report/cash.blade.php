@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">

    <div class="row">
        <div class="col-md-6">
            <h5 class="py-2 mb-3">
                <span class="text-primary fw-light">Cash Report</span>
            </h5>
        </div>
    </div>

    <div class="card">
        <div class="card-body">

            {{-- Filter --}}
            <form method="GET" action="{{ route('admin.cash.report') }}">
                <div class="row mb-3">

                    <div class="col-md-3">
                        <label>Select Date</label>
                        <input type="date" name="date" class="form-control" value="{{ $date }}">
                    </div>

                    <div class="col-md-3 mt-4">
                        <button class="btn btn-primary">Filter</button>
                        <a href="{{ route('admin.cash.report') }}" class="btn btn-secondary">Reset</a>
                    </div>

                </div>
            </form>

            <div class="col-md-6 mt-4 mb-4">
                <a href="{{ route('admin.cash.report.excel', request()->all()) }}" class="btn btn-success">
                    Export Excel
                </a>

                <a href="{{ route('admin.cash.report.pdf', request()->all()) }}" class="btn btn-danger">
                    Export PDF
                </a>
            </div>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-bordered">

                    <thead>
                        <tr>
                            <th>Receipt No</th>
                            <th>Firm Name</th>
                            <th>Sales Person</th>
                            <th>Cash</th>
                            <th>Cheque</th>
                            <th>UPI</th>
                            <th>RTGS</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($reports as $report)

                        <tr>
                            <td>{{ $report->receipt_no }}</td>
                            <td>{{ $report->firm_name }}</td>
                            <td>{{ $report->salesman_name }}</td>

                            <td>{{ number_format($report->cash_total ?? 0,2) }}</td>
                            <td>{{ number_format($report->cheque_total ?? 0,2) }}</td>
                            <td>{{ number_format($report->upi_total ?? 0,2) }}</td>
                            <td>{{ number_format($report->rtgs_total ?? 0,2) }}</td>
                        </tr>

                        @empty
                        <tr>
                            <td colspan="7" class="text-center">No Data Found</td>
                        </tr>
                        @endforelse
                    </tbody>

                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">Total</th>
                            <th>{{ number_format($reports->sum('cash_total'),2) }}</th>
                            <th>{{ number_format($reports->sum('cheque_total'),2) }}</th>
                            <th>{{ number_format($reports->sum('upi_total'),2) }}</th>
                            <th>{{ number_format($reports->sum('rtgs_total'),2) }}</th>
                        </tr>
                    </tfoot>

                </table>
            </div>

        </div>
    </div>

</div>
@endsection