@extends('admin.layouts.app')

@section('style')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<style>
    .select2-container {
        width: 100% !important;
    }

    .select2-container .select2-selection--single {
        height: calc(2.25rem + 2px);
        border: 1px solid #d9dee3;
        border-radius: 0.375rem;
        display: flex;
        align-items: center;
    }

    .select2-container .select2-selection--single .select2-selection__rendered {
        color: #566a7f;
        line-height: normal;
        padding-left: 0.75rem;
        padding-right: 2rem;
    }

    .select2-container .select2-selection--single .select2-selection__arrow {
        height: 100%;
        right: 0.5rem;
    }

    .select2-dropdown {
        border-color: #d9dee3;
    }
</style>
@endsection

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">

    <div class="row">
        <div class="col-md-6">
            <h5 class="py-2 mb-3">
                <span class="text-primary fw-light">Firm Ledger Report</span>
            </h5>
        </div>
    </div>

    <div class="card">
        <div class="card-body">

            <form method="GET" action="{{ route('admin.firm.ledger.report') }}">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Select Firm</label>
                        <select name="firm_id" class="form-select searchable-select" data-placeholder="Search Firm Name">
                            <option value="">All Firms</option>
                            @foreach($firms as $firm)
                                <option value="{{ $firm->id }}" {{ (string) $firmId === (string) $firm->id ? 'selected' : '' }}>
                                    {{ $firm->firm_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mt-4">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="{{ route('admin.firm.ledger.report') }}" class="btn btn-secondary">Reset</a>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            @if(\App\Helpers\Helper::isSuperAdmin())<th>Company</th>@endif
                            <th>Firm Name</th>
                            <th>Total Debit</th>
                            <th>Total Credit</th>
                            <th>Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $report)
                            <tr>
                                @if(\App\Helpers\Helper::isSuperAdmin())<td>{{ $report->company_name ?? '-' }}</td>@endif
                                <td>{{ $report->firm_name }}</td>
                                <td>{{ number_format($report->total_debit, 2) }}</td>
                                <td>{{ number_format($report->total_credit, 2) }}</td>
                                <td>{{ number_format($report->balance, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No Data Found</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            @if(\App\Helpers\Helper::isSuperAdmin())<th></th>@endif
                            <th class="text-end">Total</th>
                            <th>{{ number_format($totalDebit, 2) }}</th>
                            <th>{{ number_format($totalCredit, 2) }}</th>
                            <th>{{ number_format($totalBalance, 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>
    </div>

</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $('.searchable-select').each(function () {
        const $select = $(this);
        const placeholder = $select.data('placeholder') || 'Search';

        $select.select2({
            width: '100%',
            placeholder: placeholder,
            allowClear: true
        }).on('select2:open', function () {
            $('.select2-search__field').attr('placeholder', placeholder);
        });
    });
</script>
@endsection
