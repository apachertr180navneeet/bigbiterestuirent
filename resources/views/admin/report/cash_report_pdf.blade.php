<h3 style="text-align:center;">Cash Report</h3>

<table border="1" width="100%" cellspacing="0" cellpadding="5">
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
            <td>{{ number_format($report->cash_total,2) }}</td>
            <td>{{ number_format($report->cheque_total,2) }}</td>
            <td>{{ number_format($report->upi_total,2) }}</td>
            <td>{{ number_format($report->rtgs_total,2) }}</td>
        </tr>
        @endforeach
    </tbody>

    {{-- ✅ TOTAL ROW --}}
    <tfoot>
        <tr>
            <th colspan="3">Total</th>
            <th>{{ number_format($reports->sum('cash_total'),2) }}</th>
            <th>{{ number_format($reports->sum('cheque_total'),2) }}</th>
            <th>{{ number_format($reports->sum('upi_total'),2) }}</th>
            <th>{{ number_format($reports->sum('rtgs_total'),2) }}</th>
        </tr>
    </tfoot>
</table>