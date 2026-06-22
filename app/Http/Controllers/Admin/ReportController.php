<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// Models
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Receipt;
use App\Models\Salesperson;

// Export & PDF
use App\Exports\SalesReportExport;
use App\Exports\CashReportExport;
use App\Exports\FirmLedgerExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

// Helpers
use Carbon\Carbon;
use App\Helpers\Helper;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    /**
     * ----------------------------------------------------------
     * FIRM LEDGER SUMMARY REPORT
     * ----------------------------------------------------------
     * Shows total debit, credit and balance for each firm
     */
    public function firmLedgerReport(Request $request)
    {
        try {
            // Get selected firm filter (if any)
            $firmId = $request->firm_id;

            // Fetch active firms for dropdown
            $firmsQuery = Customer::where('status', 'active');
            Helper::applyUserScope($firmsQuery, 'customers');
            $firms = $firmsQuery->orderBy('firm_name')->get(['id', 'firm_name']);

            /**
             * Subquery: Total Debit (Invoices)
             */
            $invoiceTotals = Invoice::select(
                    'firm_id',
                    DB::raw('SUM(COALESCE(payable_amount, amount)) as total_debit')
                );
            Helper::applyUserScope($invoiceTotals, 'invoices');
            $invoiceTotals = $invoiceTotals->groupBy('firm_id');

            /**
             * Subquery: Total Credit (Receipts)
             */
            $receiptTotals = Receipt::select(
                    'firm_id',
                    DB::raw('SUM(given_amount) as total_credit')
                )
                ->where('status', 'accpet');
            Helper::applyUserScope($receiptTotals, 'receipts');
            $receiptTotals = $receiptTotals->groupBy('firm_id');

            /**
             * Main Query:
             * Combine debit & credit using LEFT JOIN
             */
            $reports = Customer::query()
                ->select(
                    'customers.id',
                    'customers.firm_name',
                    'users.full_name as company_name',

                    // Debit & Credit
                    DB::raw('COALESCE(invoice_totals.total_debit, 0) as total_debit'),
                    DB::raw('COALESCE(receipt_totals.total_credit, 0) as total_credit'),

                    // Balance Calculation
                    DB::raw('COALESCE(invoice_totals.total_debit, 0) - COALESCE(receipt_totals.total_credit, 0) as balance')
                )
                ->leftJoin('users', 'users.id', '=', 'customers.user_id')
                ->leftJoinSub($invoiceTotals, 'invoice_totals', function ($join) {
                    $join->on('invoice_totals.firm_id', '=', 'customers.id');
                })
                ->leftJoinSub($receiptTotals, 'receipt_totals', function ($join) {
                    $join->on('receipt_totals.firm_id', '=', 'customers.id');
                })

                // Apply filter if firm selected
                ->when($firmId, function ($query) use ($firmId) {
                    $query->where('customers.id', $firmId);
                });

            Helper::applyUserScope($reports, 'customers');

            $reports = $reports->orderBy('customers.firm_name')->get();

            // Calculate totals
            return view('admin.report.firm-ledger', [
                'reports' => $reports,
                'firms' => $firms,
                'firmId' => $firmId,
                'totalDebit' => $reports->sum('total_debit'),
                'totalCredit' => $reports->sum('total_credit'),
                'totalBalance' => $reports->sum('balance'),
            ]);

        } catch (\Exception $e) {
            Log::error('Firm Ledger Report Error: '.$e->getMessage());
            return back()->with('error', 'Something went wrong!');
        }
    }

    // /**
    //  * ----------------------------------------------------------
    //  * FIRM LEDGER DETAIL REPORT
    //  * ----------------------------------------------------------
    //  * Shows detailed entries (Invoices + Receipts)
    //  */
    // public function firmLedgerDetailsReport(Request $request)
    // {
    //     try {
    //         $firmId = $request->firm_id;

    //         // Fetch firms list
    //         $firms = Customer::where('status', 'active')
    //             ->orderBy('firm_name')
    //             ->get(['id', 'firm_name']);

    //         // Initialize variables
    //         $selectedFirm = null;
    //         $ledgerEntries = collect();

    //         $totalPendingAmount = 0;
    //         $totalBillAmount = 0;
    //         $totalReceiptAmount = 0;
    //         $totalDiscountAmount = 0;

    //         if ($firmId) {

    //             // Selected firm details
    //             $selectedFirm = Customer::find($firmId, ['id', 'firm_name', 'phone']);

    //             /**
    //              * Invoice Entries (Debit)
    //              */
    //             $invoiceEntries = Invoice::select(
    //                     'id',
    //                     'date',
    //                     'invoice_no as reference_no',
    //                     DB::raw("'invoice' as entry_type"),
    //                     DB::raw('COALESCE(payable_amount, amount) as debit'),
    //                     DB::raw('0 as credit'),
    //                     DB::raw('COALESCE(discount_amount, 0) as discount'),
    //                     DB::raw('NULL as remark')
    //                 )
    //                 ->where('firm_id', $firmId)
    //                 ->get();

    //             /**
    //              * Receipt Entries (Credit)
    //              */
    //             $receiptEntries = Receipt::select(
    //                     'id',
    //                     'date',
    //                     'receipt_no as reference_no',
    //                     DB::raw("'receipt' as entry_type"),
    //                     DB::raw('0 as debit'),
    //                     'given_amount as credit',
    //                     DB::raw('COALESCE(discount, 0) as discount'),
    //                     'remark'
    //                 )
    //                 ->where('firm_id', $firmId)
    //                 ->where('status', 'accpet')
    //                 ->get();

    //             /**
    //              * Merge & Sort Entries
    //              */
    //             $ledgerEntries = $invoiceEntries
    //                 ->concat($receiptEntries)
    //                 ->sortBy([['date','asc'],['id','asc']])
    //                 ->values();

    //             /**
    //              * Running Balance Calculation
    //              */
    //             $runningBalance = 0;
    //             $ledgerEntries = $ledgerEntries->reverse()->map(function ($entry) use (&$runningBalance) {
    //                 $runningBalance += (float)$entry->debit - (float)$entry->credit;
    //                 $entry->running_balance = $runningBalance;
    //                 return $entry;
    //             })->reverse()->values();

    //             // Totals
    //             $totalBillAmount = $invoiceEntries->sum('debit');
    //             $totalReceiptAmount = $receiptEntries->sum('credit');
    //             $totalDiscountAmount = $invoiceEntries->sum('discount') + $receiptEntries->sum('discount');
    //             $totalPendingAmount = $totalBillAmount - $totalReceiptAmount;
    //         }

    //         return view('admin.report.firm-ledger-details', compact(
    //             'firms','firmId','selectedFirm','ledgerEntries',
    //             'totalPendingAmount','totalBillAmount','totalReceiptAmount','totalDiscountAmount'
    //         ));

    //     } catch (\Exception $e) {
    //         Log::error('Firm Ledger Details Error: '.$e->getMessage());
    //         return back()->with('error', 'Something went wrong!');
    //     }
    // }

    /**
     * ----------------------------------------------------------
     * SALESPERSON REPORT
     * ----------------------------------------------------------
     * Shows pending invoices with received & remaining amount
     */
    public function salespersionreport(Request $request)
    {
        try {
            $salesmanId = $request->salesman_id;

            // Fetch salespersons
            $salesmenQuery = Salesperson::where('status', 'active');
            Helper::applyUserScope($salesmenQuery, 'salespersons');
            $salesmen = $salesmenQuery->get();

            // Get report data (reusable method)
            $reports = $this->getReportData($request);

            return view('admin.report.salesman', [
                'reports' => $reports,
                'salesmen' => $salesmen,
                'salesmanId' => $salesmanId,
                'totalAmount' => $reports->sum('remaining_amount'),
            ]);

        } catch (\Exception $e) {
            Log::error('Sales Report Error: '.$e->getMessage());
            return back()->with('error', 'Something went wrong!');
        }
    }

    /**
     * ----------------------------------------------------------
     * COMMON REPORT QUERY (REUSABLE)
     * ----------------------------------------------------------
     */
    private function getReportData($request)
    {
        $query = Invoice::select(
                'invoices.id',
                'invoices.invoice_no',
                'invoices.date',
                'invoices.user_id',
                'customers.firm_name',
                'salespersons.name as salesman_name',
                'invoices.payable_amount',
                'users.full_name as company_name',

                // Received amount
                DB::raw('COALESCE(SUM(receipts.given_amount),0) as received_amount'),

                // Remaining amount
                DB::raw('(invoices.payable_amount - COALESCE(SUM(receipts.given_amount),0)) as remaining_amount')
            )
            ->join('customers', 'customers.id', '=', 'invoices.firm_id')
            ->join('salespersons', 'salespersons.id', '=', 'invoices.salesperson_id')
            ->leftJoin('users', 'users.id', '=', 'invoices.user_id')
            ->leftJoin('receipts', function ($join) {
                $join->on('receipts.invoice_id', '=', 'invoices.id')
                     ->whereNull('receipts.deleted_at');
            })
            ->where('invoices.status', 'pending');
        Helper::applyUserScope($query, 'invoices');
        return $query->groupBy(
                'invoices.id',
                'invoices.invoice_no',
                'invoices.date',
                'invoices.user_id',
                'customers.firm_name',
                'salespersons.name',
                'invoices.payable_amount',
                'users.full_name'
            )
            ->when($request->salesman_id, function ($q) use ($request) {
                $q->where('invoices.salesperson_id', $request->salesman_id);
            })
            ->orderBy('customers.firm_name')
            ->orderBy('invoices.date')
            ->get();
    }

    /**
     * ----------------------------------------------------------
     * EXPORT EXCEL
     * ----------------------------------------------------------
     */
    public function exportExcel(Request $request)
    {
        try {
            return Excel::download(
                new SalesReportExport($this->getReportData($request)),
                'sales_report.xlsx'
            );

        } catch (\Exception $e) {
            Log::error('Excel Export Error: '.$e->getMessage());
            return back()->with('error', 'Excel export failed!');
        }
    }

    /**
     * ----------------------------------------------------------
     * EXPORT PDF
     * ----------------------------------------------------------
     */
    public function exportPdf(Request $request)
    {
        try {
            $reports = $this->getReportData($request);

            $pdf = Pdf::loadView('admin.report.sales_report_pdf', [
                'reports' => $reports,
                'totalAmount' => $reports->sum('remaining_amount'),
            ]);

            return $pdf->download('sales_report.pdf');

        } catch (\Exception $e) {
            Log::error('PDF Export Error: '.$e->getMessage());
            return back()->with('error', 'PDF export failed!');
        }
    }

    /**
     * ----------------------------------------------------------
     * CASH REPORT
     * ----------------------------------------------------------
     * Shows payment mode-wise totals (cash, card, upi, bank)
     */
    public function caashReport(Request $request)
    {
        try {
            // Default date = today
            $date = $request->date ?? Carbon::today()->toDateString();

            // Get report data using reusable function
            $reports = $this->getCashReportData($date);

            return view('admin.report.cash', compact('reports', 'date'));

        } catch (\Exception $e) {
            Log::error('Cash Report Error: '.$e->getMessage());
            return back()->with('error', 'Something went wrong!');
        }
    }

    /**
     * ----------------------------------------------------------
     * COMMON CASH REPORT QUERY (REUSABLE)
     * ----------------------------------------------------------
     * Used for:
     *  - View
     *  - Excel Export
     *  - PDF Export
     */
    private function getCashReportData($date)
    {
        $query = DB::table('receipts')
            ->join('invoices', 'receipts.invoice_id', '=', 'invoices.id')
            ->join('customers', 'invoices.firm_id', '=', 'customers.id')
            ->join('salespersons', 'invoices.salesperson_id', '=', 'salespersons.id')
            ->leftJoin('users', 'receipts.user_id', '=', 'users.id')
            ->select(
                'receipts.receipt_no',
                'customers.firm_name',
                'salespersons.name as salesman_name',
                'users.full_name as company_name',

                // Mode-wise totals
                DB::raw("SUM(CASE WHEN receipts.mode='cash' THEN receipts.given_amount ELSE 0 END) as cash_total"),
                DB::raw("SUM(CASE WHEN receipts.mode='card' THEN receipts.given_amount ELSE 0 END) as cheque_total"),
                DB::raw("SUM(CASE WHEN receipts.mode='upi' THEN receipts.given_amount ELSE 0 END) as upi_total"),
                DB::raw("SUM(CASE WHEN receipts.mode='bank' THEN receipts.given_amount ELSE 0 END) as rtgs_total")
            )
            ->whereDate('receipts.date', $date);

        if (!Helper::isSuperAdmin()) {
            $query->where('receipts.user_id', Auth::id());
        }

        return $query->groupBy(
                'receipts.receipt_no',
                'customers.firm_name',
                'salespersons.name',
                'users.full_name'
            )
            ->get();
    }

    /**
     * ----------------------------------------------------------
     * EXPORT CASH REPORT TO EXCEL
     * ----------------------------------------------------------
     */
    public function cashReportExportExcel(Request $request)
    {
        try {
            // Get selected date or default today
            $date = $request->date ?? Carbon::today()->toDateString();

            // Fetch report data
            $reports = $this->getCashReportData($date);

            // Download Excel file
            return Excel::download(
                new CashReportExport($reports),
                'cash_report.xlsx'
            );

        } catch (\Exception $e) {
            Log::error('Cash Excel Export Error: '.$e->getMessage());
            return back()->with('error', 'Excel export failed!');
        }
    }

    /**
     * ----------------------------------------------------------
     * EXPORT CASH REPORT TO PDF
     * ----------------------------------------------------------
     */
    public function cashReportExportPdf(Request $request)
    {
        try {
            // Get selected date or default today
            $date = $request->date ?? Carbon::today()->toDateString();

            // Fetch report data
            $reports = $this->getCashReportData($date);

            // Load PDF view
            $pdf = Pdf::loadView('admin.report.cash_report_pdf', [
                'reports' => $reports,
                'date' => $date,
            ]);

            // Download PDF
            return $pdf->download('cash_report.pdf');

        } catch (\Exception $e) {
            Log::error('Cash PDF Export Error: '.$e->getMessage());
            return back()->with('error', 'PDF export failed!');
        }
    }

    /**
     * ----------------------------------------------------------
     * FIRM LEDGER DETAIL REPORT
     * ----------------------------------------------------------
     * Shows detailed entries (Invoices + Receipts)
     */
    public function firmLedgerDetailsReport(Request $request)
    {
        try {
            $firmId = $request->firm_id;

            // Get all firms
            $firmsQuery = Customer::where('status', 'active');
            Helper::applyUserScope($firmsQuery, 'customers');
            $firms = $firmsQuery->orderBy('firm_name')->get(['id', 'firm_name']);

            // Default values
            $data = [
                'selectedFirm' => null,
                'ledgerEntries' => collect(),
                'totalPendingAmount' => 0,
                'totalBillAmount' => 0,
                'totalReceiptAmount' => 0,
                'totalDiscountAmount' => 0,
            ];

            // If firm selected → fetch data
            if ($firmId) {
                $data = $this->getFirmLedgerData($firmId);
            }

            return view('admin.report.firm-ledger-details', array_merge(
                compact('firms', 'firmId'),
                $data
            ));

        } catch (\Exception $e) {
            Log::error('Firm Ledger Error: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong!');
        }
    }


    /**
     * ----------------------------------------------------------
     * COMMON DATA FUNCTION (USED EVERYWHERE)
     * ----------------------------------------------------------
     */
    private function getFirmLedgerData($firmId)
    {
        // Firm info
        $selectedFirm = Customer::find($firmId, ['id', 'firm_name', 'phone']);

        // Invoice (Debit)
        $invoiceEntriesQuery = Invoice::select(
                'id',
                'date',
                'invoice_no as reference_no',
                DB::raw("'invoice' as entry_type"),
                DB::raw('COALESCE(payable_amount, amount) as debit'),
                DB::raw('0 as credit'),
                DB::raw('COALESCE(discount_amount, 0) as discount'),
                DB::raw('NULL as remark')
            )
            ->where('firm_id', $firmId);
        Helper::applyUserScope($invoiceEntriesQuery, 'invoices');
        $invoiceEntries = $invoiceEntriesQuery->get();

        // Receipt (Credit)
        $receiptEntriesQuery = Receipt::select(
                'id',
                'date',
                'receipt_no as reference_no',
                DB::raw("'receipt' as entry_type"),
                DB::raw('0 as debit'),
                'given_amount as credit',
                DB::raw('COALESCE(discount, 0) as discount'),
                'remark'
            )
            ->where('firm_id', $firmId)
            ->where('status', 'accpet');
        Helper::applyUserScope($receiptEntriesQuery, 'receipts');
        $receiptEntries = $receiptEntriesQuery->get();

        // Merge + Sort
        $ledgerEntries = $invoiceEntries
            ->concat($receiptEntries)
            ->sortBy([['date','asc'],['id','asc']])
            ->values();

        // Running Balance
        $runningBalance = 0;
        $ledgerEntries = $ledgerEntries->reverse()->map(function ($entry) use (&$runningBalance) {
            $runningBalance += (float)$entry->debit - (float)$entry->credit;
            $entry->running_balance = $runningBalance;
            return $entry;
        })->reverse()->values();

        // Totals
        $totalBillAmount = $invoiceEntries->sum('debit');
        $totalReceiptAmount = $receiptEntries->sum('credit');
        $totalDiscountAmount = $invoiceEntries->sum('discount') + $receiptEntries->sum('discount');
        $totalPendingAmount = $totalBillAmount - $totalReceiptAmount;

        return compact(
            'selectedFirm',
            'ledgerEntries',
            'totalBillAmount',
            'totalReceiptAmount',
            'totalDiscountAmount',
            'totalPendingAmount'
        );
    }


    /**
     * ----------------------------------------------------------
     * EXPORT EXCEL
     * ----------------------------------------------------------
     */
    public function firmLedgerExcel(Request $request)
    {
        try {
            $firmId = $request->firm_id;

            if (!$firmId) {
                return back()->with('error', 'Please select firm');
            }

            $data = $this->getFirmLedgerData($firmId);

            return Excel::download(
                new FirmLedgerExport($data),
                'firm-ledger.xlsx'
            );

        } catch (\Exception $e) {
            Log::error('Excel Export Error: ' . $e->getMessage());
            return back()->with('error', 'Excel export failed!');
        }
    }


    /**
     * ----------------------------------------------------------
     * EXPORT PDF
     * ----------------------------------------------------------
     */
    public function firmLedgerPdf(Request $request)
    {
        try {
            $firmId = $request->firm_id;

            if (!$firmId) {
                return back()->with('error', 'Please select firm');
            }

            $data = $this->getFirmLedgerData($firmId);

            $pdf = PDF::loadView('admin.report.firm-ledger-pdf', $data);

            return $pdf->download('firm-ledger.pdf');

        } catch (\Exception $e) {
            dd($e);
            Log::error('PDF Export Error: ' . $e->getMessage());
            return back()->with('error', 'PDF export failed!');
        }
    }
}