<h3>Salesman Report</h3>

<table width="100%" border="1" cellspacing="0" cellpadding="5">
    <thead>
        <tr>
            <th>Invoice No</th>
            <th>Date</th>
            <th>Firm Name</th>
            <th>Sales Person</th>
            <th>Amount</th>
        </tr>
    </thead>

    <tbody>
        @foreach($reports as $report)
        <tr>
            <td>{{ $report->invoice_no }}</td>
            <td>{{ \Carbon\Carbon::parse($report->date)->format('d/m/Y') }}</td>
            <td>{{ $report->firm_name }}</td>
            <td>{{ $report->salesman_name }}</td>
            <td>{{ number_format($report->remaining_amount,2) }}</td>
        </tr>
        @endforeach
    </tbody>

    <tfoot>
        <tr>
            <td colspan="4"><strong>Total</strong></td>
            <td>{{ number_format($totalAmount,2) }}</td>
        </tr>
    </tfoot>
</table>