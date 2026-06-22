<?php

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FirmLedgerExport implements FromCollection, WithHeadings
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data['ledgerEntries'])->map(function ($entry) {
            return [
                'Date' => \Carbon\Carbon::parse($entry->date)->format('d/m/Y'),
                'Description' => ($entry->entry_type == 'invoice' ? 'Sales Invoice ' : 'Receipt ') . $entry->reference_no,
                'Bill' => $entry->debit,
                'Receipt' => $entry->credit,
                'Discount' => $entry->discount,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Date',
            'Description',
            'Bill',
            'Receipt',
            'Discount'
        ];
    }
}
