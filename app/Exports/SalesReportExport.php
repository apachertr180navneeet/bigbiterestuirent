<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SalesReportExport implements FromCollection, WithHeadings
{
    protected $reports;

    public function __construct($reports)
    {
        $this->reports = $reports;
    }

    public function collection()
    {
        return collect($this->reports)->map(function ($row) {
            return [
                'Invoice No' => $row->invoice_no,
                'Date' => \Carbon\Carbon::parse($row->date)->format('d/m/Y'),
                'Firm Name' => $row->firm_name,
                'Sales Person' => $row->salesman_name,
                'Amount' => $row->remaining_amount, // ✅ SAME as UI
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Invoice No',
            'Date',
            'Firm Name',
            'Sales Person',
            'Amount'
        ];
    }
}
