<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReceiptExport implements FromCollection, WithHeadings
{
    protected $receipts;

    public function __construct($receipts)
    {
        $this->receipts = $receipts;
    }

    public function collection()
    {
        return collect($this->receipts)->map(function ($item) {
            $mode = $item->mode ? strtolower($item->mode) : '';
            $modeLabel = match ($mode) {
                'bank' => 'RTGS / NEFT',
                'card' => 'Cheque',
                'cash' => 'Cash',
                'upi' => 'UPI',
                default => $item->mode ?? '-',
            };

            $statusLabel = match ($item->status) {
                'accpet' => 'Approved',
                'rejected' => 'Rejected',
                default => 'Pending',
            };

            return [
                'Date' => $item->date,
                'Receipt No' => $item->receipt_no,
                'Bill No' => $item->invoice_no,
                'Bill Amount' => number_format($item->amount, 2),
                'Received Amount' => number_format($item->given_amount, 2),
                'Sales Person' => $item->sales_person,
                'Firm Name' => $item->firm_name,
                'Payment Mode' => $modeLabel,
                'Remark' => $item->remark ?? '-',
                'Status' => $statusLabel,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Date',
            'Receipt No',
            'Bill No',
            'Bill Amount',
            'Received Amount',
            'Sales Person',
            'Firm Name',
            'Payment Mode',
            'Remark',
            'Status',
        ];
    }
}
