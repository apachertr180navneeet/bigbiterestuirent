<table>
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
        @foreach($reports as $report)
        <tr>
            <td>{{ $report->receipt_no }}</td>
            <td>{{ $report->firm_name }}</td>
            <td>{{ $report->salesman_name }}</td>
            <td>{{ $report->cash_total }}</td>
            <td>{{ $report->cheque_total }}</td>
            <td>{{ $report->upi_total }}</td>
            <td>{{ $report->rtgs_total }}</td>
        </tr>
        @endforeach
    </tbody>

    {{-- ✅ TOTAL ROW --}}
    <tfoot>
        <tr>
            <th colspan="3">Total</th>
            <th>{{ $reports->sum('cash_total') }}</th>
            <th>{{ $reports->sum('cheque_total') }}</th>
            <th>{{ $reports->sum('upi_total') }}</th>
            <th>{{ $reports->sum('rtgs_total') }}</th>
        </tr>
    </tfoot>
</table>