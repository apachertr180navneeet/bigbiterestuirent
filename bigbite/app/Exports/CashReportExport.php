<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class CashReportExport implements FromView
{
    protected $reports;

    public function __construct($reports)
    {
        $this->reports = $reports;
    }

    public function view(): View
    {
        return view('admin.report.cash_report_excel', [
            'reports' => $this->reports
        ]);
    }
}
