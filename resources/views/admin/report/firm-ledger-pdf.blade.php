<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Firm Ledger</title>

    <style>
        @page {
            margin: 18px 16px 20px;
        }

        body {
            margin: 0;
            color: #667a93;
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.45;
        }

        .page {
            width: 100%;
        }

        .company-header {
            text-align: center;
            margin-bottom: 14px;
        }

        .company-name {
            margin: 0 0 4px;
            color: #5d6f89;
            font-size: 18px;
            font-weight: bold;
        }

        .company-info {
            color: #73859b;
            font-size: 11px;
            line-height: 1.5;
        }

        .divider {
            margin: 12px 0 14px;
            border-top: 1px solid #d7e0eb;
        }

        .firm-info {
            margin-bottom: 14px;
            text-align: center;
            color: #6e8096;
            font-size: 11px;
            line-height: 1.6;
        }

        .firm-info strong {
            color: #5b6d84;
        }

        .summary-table {
            width: 100%;
            margin-bottom: 16px;
            border-collapse: separate;
            border-spacing: 10px 0;
            table-layout: fixed;
        }

        .summary-table td {
            width: 25%;
            padding: 0;
            border: none;
            vertical-align: top;
        }

        .summary-card {
            border: 1px solid #d8e0ea;
            border-radius: 8px;
            background: #f9fbff;
            padding: 12px 14px;
            min-height: 52px;
        }

        .summary-label {
            margin-bottom: 4px;
            color: #7a8ea6;
            font-size: 10px;
        }

        .summary-value {
            color: #42566f;
            font-size: 14px;
            font-weight: bold;
        }

        .ledger-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .ledger-table th,
        .ledger-table td {
            border: 1px solid #d8e0ea;
            padding: 10px 12px;
            color: #667a93;
            font-size: 11px;
            word-wrap: break-word;
        }

        .ledger-table thead th,
        .ledger-table tfoot th {
            background: #f4f6fa;
            color: #6a7d95;
            font-weight: bold;
        }

        .ledger-table tfoot th {
            font-size: 11.5px;
        }

        .col-date {
            width: 17%;
            text-align: left;
        }

        .col-description {
            width: 39%;
            text-align: left;
        }

        .col-amount {
            width: 14.66%;
            text-align: right;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="company-header">
            <p class="company-name">Bigbite Agency</p>
            <div class="company-info">
                Number: +91 8107078020<br>
                Address: NEAR MAHABAL MALL, SHOP NO 1,<br>
                NARSINGH JI PAYAO, MATA KA THAN ROAD,<br>
                JODHPUR, Rajasthan - 342001
            </div>
        </div>

        <div class="divider"></div>

        <div class="firm-info">
            <div><strong>Firm :</strong> {{ $selectedFirm->firm_name ?? '-' }}</div>
            <div><strong>Mobile :</strong> {{ $selectedFirm->phone ?? '-' }}</div>
        </div>

        <table class="summary-table">
            <tr>
                <td>
                    <div class="summary-card">
                        <div class="summary-label">Total Bill Amount</div>
                        <div class="summary-value">{{ number_format($totalBillAmount, 2) }}</div>
                    </div>
                </td>
                <td>
                    <div class="summary-card">
                        <div class="summary-label">Total Receipt Amount</div>
                        <div class="summary-value">{{ number_format($totalReceiptAmount, 2) }}</div>
                    </div>
                </td>
                <td>
                    <div class="summary-card">
                        <div class="summary-label">Total Discount</div>
                        <div class="summary-value">{{ number_format($totalDiscountAmount, 2) }}</div>
                    </div>
                </td>
                <td>
                    <div class="summary-card">
                        <div class="summary-label">Total Pending Amount</div>
                        <div class="summary-value">{{ number_format($totalPendingAmount, 2) }}</div>
                    </div>
                </td>
            </tr>
        </table>

        <table class="ledger-table">
            <thead>
                <tr>
                    <th class="col-date">Date</th>
                    <th class="col-description">Description</th>
                    <th class="col-amount">Bill</th>
                    <th class="col-amount">Receipt</th>
                    <th class="col-amount">Discount</th>
                </tr>
            </thead>

            <tbody>
                @forelse($ledgerEntries as $entry)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($entry->date)->format('d/m/Y') }}</td>
                        <td>
                            {{ $entry->entry_type == 'invoice' ? 'Sales Invoice ' : 'Receipt Voucher ' }}{{ $entry->reference_no }}
                        </td>
                        <td class="text-right">{{ number_format($entry->debit, 2) }}</td>
                        <td class="text-right">{{ number_format($entry->credit, 2) }}</td>
                        <td class="text-right">{{ number_format($entry->discount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">No Data Found</td>
                    </tr>
                @endforelse
            </tbody>

            <tfoot>
                <tr>
                    <th colspan="2" class="text-right">Total</th>
                    <th class="text-right">{{ number_format($totalBillAmount, 2) }}</th>
                    <th class="text-right">{{ number_format($totalReceiptAmount, 2) }}</th>
                    <th class="text-right">{{ number_format($totalDiscountAmount, 2) }}</th>
                </tr>
            </tfoot>
        </table>
    </div>
</body>
</html>
